<?php

use Illuminate\Database\Seeder;

class WalletTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
