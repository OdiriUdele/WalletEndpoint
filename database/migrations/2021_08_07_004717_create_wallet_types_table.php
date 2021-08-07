<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\WalletType;

class CreateWalletTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wallet_types', function (Blueprint $table) {
            $table->id();
            $table->string('wallet_type');
            $table->decimal("minimum_balance")->default(0);
            $table->double("monthly_interest_rate")->default(0);
            $table->timestamps();
        });

        if(WalletType::count() == 0){

            App\WalletType::insert([
                [
                    'wallet_type' =>   "Premium",
                    'minimum_balance' =>   0,
                    'monthly_interest_rate' =>   3,
                ],
                [
                    'wallet_type' =>   "Standard",
                    'minimum_balance' =>   1000,
                    'monthly_interest_rate' =>   5,
                ],
                [
                    'wallet_type' =>   "Compact",
                    'minimum_balance' =>   2000,
                    'monthly_interest_rate' =>   7,
                ]
            ]);
        }

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wallet_types');
    }
}
