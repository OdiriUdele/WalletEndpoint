<?php

namespace App\Imports;

use App\State;
use App\Lga;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class StateLgaImport implements ToCollection,WithHeadingRow,WithBatchInserts
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        try{

        foreach ($collection as $row) 
        {
            $state = State::where('state','LIKE','%'.$row['state'].'%')->first();
            if($state){//if state exists already import lga

                if(!Lga::where('lga','LIKE','%'.$row['lga'].'%')->first()){//check if LGA Exists in state if not store
                    Lga::create([
                        'state_id'=>$state->id,
                        'lga'=>$row['lga']
                    ]);
                }
            }else{
                State::create([
                    'state' => $row['state'],
                ]);

                $state = State::where('state', $row['state'])->first();

                if(!Lga::where('lga','LIKE','%'.$row['lga'].'%')->first()){//check if LGA Exists in state, if not, store
                    Lga::create([
                        'state_id'=>$state->id,
                        'lga'=>$row['lga']
                    ]);
                }
            }
        }
        }catch(\Exception $e){
            throw($e);
        }
    }

    public function batchSize(): int
    {
        return 1000;
    }
}
