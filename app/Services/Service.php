<?php
namespace App\Services;

use App\Enums\LoanStatus;
use App\Models\Loan\LoanLevel;
use App\Setting;
use App\Models\Loan\LoanApplication;
use App\Estimation;
use App\User;
use App\Notifications\ProvisionalOfferAccepted;
use App\Notifications\AdminProvisionalOfferMail;
use Illuminate\Support\Facades\Notification;
use Mail;
use Log;

use Illuminate\Support\Str;
use Carbon\Carbon;

class Service{


    public function createLoan($user, $refcode, $logged = false){
        try{
         //create loan using refcode user id
         $estimation = Estimation::where('reference_code', $refcode)->first();
         $estimation->selected = 1;
         $estimation->status = LoanStatus::ACTIVE;

         request()->how_you_found_us = $estimation->how_you_found_us;

         $est = $estimation->toArray();

        $est['status'] = 1;
    

        $loan = $user->loans()->create([
            "desired_amount"=> $est['desired_amount'],
            "desired_tenor"=> $est['desired_tenor'],
            "desired_repayment_plan"=> $est['desired_repayment_plan'],
            "offer_amount"=> $est['offer_amount'],
            "offer_tenor"=> $est['offer_tenor'],
            "offer_repayment_plan"=> $est['offer_repayment_plan'],
            "status"=> $est['status'],
            "reject_reason"=> $est['reject_reason'],
            "estimation_id"=> $est['id']
        ]);

        $values = $loan->fees()->create([
            "management_fee"=>  $est['management_fee'],
            "monthly_interest"=>  $est['monthly_interest'],
            "car_tracker"=>  $est['car_tracker'],
            "total_insurance"=>  $est['total_insurance']
        ]);

        $vehicle = $loan->vehicles()->create([
            "year"=> $est['year'],
            "make"=> $est['make'],
            "model"=> $est['model'],
            "trim"=> $est['trim'],
            "plate_number"=> $est['plate_number'],
            "mileage"=> $est['mileage'],
            "insurance"=> $est['insurance'],
            "registered_owner"=> $est['registered_owner'],
            "state"=> $est['registered_owner'],
            "average_value"=> $est['average_value'],
            "below_value"=> $est['below_value'],
            "above_value"=> $est['above_value']
        ]);

        activity('Loan-Creation-Operations')->causedBy($user)->performedOn($vehicle)->log('User added loan Vehicle');

        $now = Carbon::now();

        //update loan stage
        
        $stage = $logged ? $loan->stage()->create(['loan_id'=>$loan->id,'guest_date'=>$now]) : $loan->stage()->create(['loan_id'=>$loan->id,'accepted_date'=>$now]);

        $levels = $this->getLevels($loan->id);

         //update loan with breakdown

         $breakdown_values = $this->loanBreakDown($loan, $loan->vehicles[0]->below_value, $loan->estimate->type);

         $update_loan_breakdown = $loan->update($breakdown_values);

         $updated_loan = $loan->refresh();

         $updated_loan["level"] = $levels;

         $this->sendProvisionalOfferAcceptedMail($estimation,$user);

         $estimation->save();
         
         return $updated_loan;

        }catch(\Error $e){

             \Log::info($e->getMessage());
            return response(["message" => "Something went wrong.", "status" => false], 500);

        } catch(\Exception $e){

             \Log::info($e->getMessage());
            return response(["message" => "Something went wrong.", "status" => false], 500);
            
        }
    }

    public function loanBreakDown($loan, $below_value = 0, $type = "standard"){
        try{
            $breakdown = [];
            $breakdown["total_loan_amount"] = $this->getTotalLoanAmount($loan);
            $breakdown["monthly_repayment"] = $this->getMonthlyRepayment($loan, $below_value, $type);
            $breakdown["total_interest_due"] = $this->getTotalInterest($loan);
            $breakdown["total_payable_amount"] = $this->getTotalAmountPayable($loan);
            $breakdown["comprehensive_insurance"] = $this->getCompInsurance($loan);

            return $breakdown;

        }catch(\Error $e){

             \Log::info($e->getMessage());
            return response(["message" => "Something went wrong.", "status" => false], 500);

        } catch(\Exception $e){

             \Log::info($e->getMessage());
            return response(["message" => "Something went wrong.", "status" => false], 500);
            
        }  
    }

    public function setMaturityDateForLoan($loanapplication){
        // + {x} months from today
        $date = date('Y-m-d');

        // One month from a specific date
        $maturity_date = date('Y-m-d', strtotime('+'.$loanapplication->offer_tenor.' month', strtotime($date)));

        return $maturity_date;
    }
    public function setNextPaymentDate(){
        // + {x} months from today
        $date = date('Y-m-d');

        // One month from a specific date
        $next = date('Y-m-d', strtotime('+1 month', strtotime($date)));

        return $next;
    }

    public function buildPhoneNumber($phone_no){
        
        return "+234" . substr($phone_no, 1);
    }

    public function calculateProvisionalOffer($response, $value){
        $full = $response->prices->below * 360;

        $provisional_offer_amount = $full * ($value / 100);
            
        $provisional_offer_amount = $this->setProvisionalOffer($provisional_offer_amount);

        $provisional_offer_amount = 50 * floor($provisional_offer_amount / 50);

        $provisional_offer_amount = $this->roundOffer($provisional_offer_amount);

        $provisional_offer_amount = $this->offerLessThan200k($provisional_offer_amount);

        return $provisional_offer_amount;
    }

    public function calculateProvisionalOfferMax($response, $value){
        $full = $response->prices->below * 360;

        $provisional_offer_amount = $full * ($value / 100);

        $provisional_offer_amount = 50 * floor($provisional_offer_amount / 50);
        
        $provisional_offer_amount = $this->roundOffer($provisional_offer_amount);

        return $provisional_offer_amount;
    }

    public function roundOffer($provisional_offer_amount){
        $mod = $provisional_offer_amount%50000;

        if($mod != 0){
            return $provisional_offer_amount+($mod<(50000/2) ? -$mod:50000-$mod);
        }
        return $provisional_offer_amount;
       
    }

    public function calculateOffer($response, $value){
        
        $full = $response->prices->below * 360;

        $provisional_offer_amount = $full * ($value / 100);

        $provisional_offer_amount = 50 * floor($provisional_offer_amount / 50);

        $provisional_offer_amount = $this->roundOffer($provisional_offer_amount);

        $provisional_offer_amount = $this->offerLessThan200k($provisional_offer_amount);

        return $provisional_offer_amount;
    }

    public function acceptedStates(){
       return in_array(strtolower(request()->state), ["lagos", "abuja"]) ? true : false;
    }

    public function strongCheck($response){
        return !isset($response->error) && $this->acceptedStates() && $this->registeredOwner() ? true : false;
    }

    public function registeredOwner(){
        return request()->registered_owner == "1" ? true : false;
    }


    public function setProvisionalOffer($provisional_offer_amount){
        return $provisional_offer_amount > request()->desired_amount ? request()->desired_amount : $provisional_offer_amount;
    }

    public function getCarEstimatedWorth($estimated_value){
        $initial_value = floor(($estimated_value) * 360);
        return $initial_value;
    }


    public function offerLessThan200k($provisional_offer_amount){
        return $provisional_offer_amount < 200000 ? null : $provisional_offer_amount;
    }


    public function offerLessThan500k($provisional_offer_amount){
        return $provisional_offer_amount < 500000 ? true : false;
    }

    public function checkProvisionalOfferAmount($provisional_offer_amount){
        if(is_null($provisional_offer_amount)){
            if(!$this->acceptedStates()){
                return "We currently do not offer services in the State of your car registration";
            } elseif(!$this->registeredOwner()){
                return "We are only able to offer loans to the registered owner of the car";
            } elseif($this->offerLessThan500k($provisional_offer_amount)){
                return "Your car value does not meet the minimum requirement for a loan offer";
            }
            else{
                return "We are unable to evaluate your car at the moment, however, a member of our support team will be reaching out to you shortly.";
            }
        }
    }
    
    public function getAllCarEstimatedWorths($loan){
        $val = 0;
        foreach($loan->vehicles as $loan_vehicle){
            $val += $loan_vehicle->below_value * 0.8;
        }
        return $val;
    }

    public function getCompInsurance($loan){
        $estval = $this->getAllCarEstimatedWorths($loan);
        $comp_insurance_value = $loan->offer_tenor < 2 ? $this->belowTwoMonths($estval, $loan->offer_tenor) : $this->moreOrEqualToTwoMonths($estval, $loan->offer_tenor);
        
        return $comp_insurance_value;

    }

    public function getCharges($loan){
        $estimated_value = $this->getAllCarEstimatedWorths($loan);
        $charges = $loan->fees->management_fee + $loan->fees->car_tracker + $this->comprehensiveInsurance($estimated_value, $loan->offer_tenor);
        return $charges;
    }
    
    
    public function getTotalLoanAmount($loan){
        $total = $this->getCharges($loan) + $loan->offer_amount; 
        return $total;
    }

    public function getMonthlyRepayment($loan, $below_value, $type){
        $monthly_interest = $this->getMonthlyInterest($below_value, $loan->offer_amount, $loan->offer_tenor, $type);
        $total_interest = $monthly_interest * $loan->offer_tenor;
        
        $total_amount_payable = $this->getTotalAmountPayable($loan);
        $monthly_repayment = $total_amount_payable / $loan->offer_tenor;

        return $monthly_repayment;
    }

    public function getTotalInterest($loan){
        $total_loan_amount = $this->getTotalLoanAmount($loan);
        $type = $loan->estimate->type;

        switch ($type) {
            case 'premium':
                $interest = Setting::first()->premium_monthly_percentage ;
                break;
            case 'best':
                $interest = Setting::first()->best_monthly_percentage ;
                break;
            default:
                $interest = Setting::first()->monthly_percentage;
                break;
        }

        $interest_percent = ($total_loan_amount / 100) * $interest;
        $total_interest = $interest_percent * $loan->offer_tenor;

        return $total_interest;
    }
    
    public function getTotalAmountPayable($loan){
        $totalinterest = $this->getTotalInterest($loan);
        $totalloanamount = $this->getTotalLoanAmount($loan);
        $total_amount_payable = $totalinterest + $totalloanamount;
        return $total_amount_payable;
    }
    
    public function getManangementFee($offer_amount){
        return (Setting::first()->management_percentage / 100) * $offer_amount;
    }

    public function getCarTrackerFee(){
        return Setting::first()->car_tracker_fee;
    }

    
    public function getPrincipalDue($estimated_value, $offer_amount, $offer_tenor){
        return $offer_amount + $this->additionalCharge($estimated_value, $offer_amount, $offer_tenor);
    }
    
    public function getMonthlyInterest($estimated_value, $offer_amount, $offer_tenor, $type="standard"){
        switch ($type) {
            case 'premium':
                return (Setting::first()->premium_monthly_percentage / 100) * $this->getPrincipalDue($estimated_value, $offer_amount, $offer_tenor);
                break;
            case 'best':
                return (Setting::first()->best_monthly_percentage / 100) * $this->getPrincipalDue($estimated_value, $offer_amount, $offer_tenor);
                break;
            default:
                return (Setting::first()->monthly_percentage / 100) * $this->getPrincipalDue($estimated_value, $offer_amount, $offer_tenor);
                break;
        }
    }


    public function additionalCharge($estimated_value, $offer_amount, $offer_tenor){
        $additional_charge = 0;

        return $this->comprehensiveInsurance($estimated_value, $offer_tenor) + $this->getManangementFee($offer_amount) + $this->getCarTrackerFee() + $additional_charge;
    }

    public function comprehensiveInsurance($estimated_value, $offer_tenor){
        
        $vehicle_insurance_value = $offer_tenor < 2 ? $this->belowTwoMonths($estimated_value, $offer_tenor) : $this->moreOrEqualToTwoMonths($estimated_value, $offer_tenor);

        return $vehicle_insurance_value;
    }

    public function belowTwoMonths($estimated_value, $offer_tenor){
        return  ((3 / 100) * $estimated_value * (($offer_tenor + 2) / 12));
    }

    public function moreOrEqualToTwoMonths($estimated_value, $offer_tenor){
        return  ((3 / 100) * $estimated_value * (($offer_tenor + 1) / 12));
    }

    public function breakNames(string $name){
        return explode(" ", $name);
    }
 
    public function RequestedVehicleInfo(){
        // $vehicleinfo = [
        //     "year" => request()->year,
        //     "make" => request()->make,
        //     "model" => request()->model,
        //     "mileage" => request()->mileage,
        //     "trim" => request()->trim
        // ];

        $vehicleinfo = [
            "year" => request()->year,
            "make" => request()->make,
            "model" => request()->model,
            "mileage" => request()->mileage
        ];
        return $vehicleinfo;
    }


    public function compareNames(string $customernames, string $servicenames){
        (int) $match = 0;

        $customernames = $this->breakNames($customernames);

        $servicenames = $this->breakNames($servicenames);
        
        $number_of_vehicle_names = count($servicenames);
        

        // return $number_of_vehicle_names;
        // return response(["customer" => $customernames, "service" => $servicenames]);
        switch ($number_of_vehicle_names) {
            case 2:
                for($i = 0; $i < 2; $i++){
                    if(in_array($customernames[$i],$servicenames)){
                        $match = $match + 1;
                    }
                }
                // return $match;
                return ($match == 2) ? true : false;

                break;

            case 3:
                for($i = 0; $i < 3; $i++){
                    if(in_array($customernames[0],$servicenames)){
                        $match = $match + 1;
                    }
                    if(in_array($customernames[1],$servicenames)){
                        $match = $match + 1;
                    }
                    break 1;

                }

                // return $match;
                return ($match > 1) ? true : false;

                break;
            
            default:
                return false;
                break;
        }
        
    }

    public function setMilage(){
        $current_year = Carbon::today()->year;
        $mileage_multiplier = $current_year - (int)(request()->year);
        $mileage = 10000 * $mileage_multiplier;
        return $mileage;
    }

    public function getLevels($loan_id){
        // provisionall set to true for now
        $levels = LoanLevel::where('loan_application_id', $loan_id)->firstOrCreate([
            'loan_application_id' => $loan_id,
            'passed_bvn' => false,
            'passed_document_upload' => false,
            'passed_set_inspection_date' => false,
            'passed_picture_upload' => false,
            'passed_repayment_setup' => false
        ]);

        // $levels = LoanLevel::where('loan_application_id', $loan_id)->firstOrCreate([
        //     'loan_application_id' => $loan_id,
        //     'passed_bvn' => false,
        //     'passed_document_upload' => false,
        //     'passed_set_inspection_date' => false,
        //     'passed_picture_upload' => false,
        //     'passed_repayment_setup' => false
        // ]);

        return $levels;
    }

    public function bvnSample(){
        $payload = [
            "status"=> true,
            "message"=> "BVN resolved",
            "data" => [
              "first_name"=> auth()->user()->first_name,
              "last_name"=> auth()->user()->last_name,
              "dob"=> "27-May-93",
              "formatted_dob"=> "1993-05-27",
              "mobile"=> "07080481215",
              "bvn"=> "12345678920"
            ],
            "meta"=> [
              "calls_this_month"=> 2,
              "free_calls_left"=> 8
            ]
        ];
        return $payload;
    }




    public function prepareData($data){
        $arr = [
            "authorization_code" => $data->data->authorization->authorization_code, 
            "card_type" => $data->data->authorization->card_type, 
            "last4" => $data->data->authorization->last4,
            "exp_month" => $data->data->authorization->exp_month,
            "exp_year" => $data->data->authorization->exp_year,
            "bin" => $data->data->authorization->bin,
            "bank" => $data->data->authorization->bank,
            "signature" => $data->data->authorization->signature,
            "reusable" => $data->data->authorization->reusable,
            "country_code" => $data->data->authorization->country_code,
            "customer_code" => $data->data->customer->customer_code,
            "first_name" => $data->data->customer->first_name ?? auth()->user()->first_name,
            "last_name" => $data->data->customer->last_name ?? auth()->user()->last_name,
            "email" => $data->data->customer->email,
            "amount" => $data->data->amount,
            "reference" => $data->data->reference,
            "txn_date" => $data->data->paid_at,
            "status" => true
           ];
        return $arr;
    }




    public function preparePaystackRequest($bill){
        $data = [
            "authorization_code" => $bill->auth_code,
            "email" => $bill->email,
            "amount" => $this->inKobo($bill->payable_amount)
        ];
        return $data;
    }
    


    public function inKobo(int $value):int{
        return $value * 100;
    }

    public function inNaira($value){
        return $value / 100;
    }

    public function userOwnsLoan($loan){
        return auth()->user()->id === LoanApplication::find($loan->id)->user_id;
    }

    public function userOwnsEstimation($estimation){
        if(auth()->user()){
            return auth()->user()->email === $estimation->email;
        }
    }

    public function jsonp_decode($jsonp, $assoc = false) { // PHP 5.3 adds depth as third parameter to json_decode
        if($jsonp[0] !== '[' && $jsonp[0] !== '{') { // we have JSONP
           $jsonp = substr($jsonp, strpos($jsonp, '('));
        }
        return json_decode(trim($jsonp,'();'), $assoc);
    }

    public function sendProvisionalOfferAcceptedMail($estimation, $user){

        try {
            Notification::route('mail', $user->email)->notify(new ProvisionalOfferAccepted($estimation,$user));
        } catch (\Exception $e) {
            Log::info('Unable to send Provional Offer Accepted mail to user '.$e->getMessage());
        } 

        //notify admin
       
        // $admin_email_template = $this->htmlAcceptOfferEmail($estimation,$user,'A user just accepted a provisional offer');
        
        $admin_emails = $this->fetchAdminEmails();
        
        foreach($admin_emails as $email){
            try {
                Notification::route('mail', $email)->notify(new AdminProvisionalOfferMail($estimation, $user));
            } catch (\Exception $e) {
                Log::info('Unable to send Provional Offer Accepted mail copy to admin '.$email.' ' .$e->getMessage());
            } 
        }
    }

    public function fetchAdminEmails(){
        $admin_emails = User::whereHas(
            'roles', function($role){
                $role->where('name', 'superadmin');
            }
        )->pluck('email');
        
        $exists = false;
        foreach($admin_emails as $email){
            if($email == "cashdrive75@gmail.com"){
                $exists = true;
                break;
            }
        }
        if(!$exists){
            $admin_emails[] = "cashdrive75@gmail.com";
        }
        
        return  $admin_emails;
    }

    public function htmlAcceptOfferEmail($estimation,$user,$title)
    {

        $html = '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="border-spacing:0;border-left:1px solid #e8e7e5;border-right:1px solid #e8e7e5;border-bottom:1px solid #e8e7e5;border-top:1px solid #e8e7e5; font-family: "Work Sans";" bgcolor="#FFFFFF">
        <tbody><tr>
          <td align="left" style="padding:50px 50px 50px 50px"><p style="color:#262626;font-size:24px;text-align:left;font-family:Verdana,Geneva,sans-serif"><strong>'."$title".'</strong></p>
            <p style="color:#000000;font-size:16px;text-align:left;font-family:Verdana,Geneva,sans-serif;line-height:22px">
                 
            </p>
            <ul style="font-size: 1.2rem;">
              <li style="margin-bottom: 1rem;">Full Name : <b>'."{$user->full_name}".'</b></li>
              <li style="margin-bottom: 1rem;">Loan Amount : <b>'."$estimation->offer_amount".'</b></li>
              <li style="margin-bottom: 1rem;">Offer Type : <b>'."{$estimation->type}".'</b></li>
              <li style="margin-bottom: 1rem;">Email : <b>'."{$estimation->email}".'</b></li>
              <li style="margin-bottom: 1rem;">Phone : <b>'."{$estimation->phone}".'</b></li>
              <li style="margin-bottom: 1rem;">Location : <b>'."{$estimation->state}".'</b></li>
          </ul>
            <table border="0" align="left" cellpadding="0" cellspacing="0" style="Margin:0 auto">
              <tbody>
                <tr>
                  
                </tr>
              </tbody>
            </table>
           </td>
        </tr>
      </tbody></table>';
        return $html;
    }

    public function recordActivity($subject,$type = "",$operation){
        if($type != ""){
            activity($type)->performedOn($subject)->log($operation);
        }else{
             activity()->performedOn($subject)->log($operation);
        }
     }
}