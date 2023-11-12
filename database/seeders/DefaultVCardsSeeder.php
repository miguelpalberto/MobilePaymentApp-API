<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

use function Symfony\Component\String\b;

class DefaultVCardsSeeder extends Seeder
{
    public static $defaultEmptyPhoneNumber = '999999999';
    public static $defaultPhoneNumberWithFinancialData = '999999998';
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //default empty vCard
        DB::table('vcards')->insert([
            'phone_number' => $this::$defaultEmptyPhoneNumber,
            'name' => 'Default Empty User',
            'email' => 'defaultEmpty@mail.pt',
            'photo_url' => null,
            'password' => bcrypt('123'),
            'confirmation_code' => bcrypt('123'),
            'blocked' => 0,
            'balance' => 0,
            'max_debit' => 0,
            'piggy_bank_balance' => 0,
            'custom_options' => null,
            'custom_data' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null,
        ]);

        //default vCard with existing financial data
        //transaction
        DB::transaction(function () {
            $vcard = DB::table('vcards')->insert([
                'phone_number' => $this::$defaultPhoneNumberWithFinancialData,
                'name' => 'Default User',
                'email' => 'defaultUser@mail.pt',
                'photo_url' => null,
                'password' => bcrypt('123'),
                'confirmation_code' => bcrypt('123'),
                'blocked' => 0,
                'balance' => 200,
                'max_debit' => 200,
                'piggy_bank_balance' => 50,
                'custom_options' => null,
                'custom_data' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]);

            $sampleDebitTransaction = $this->createSampleDebitTransaction($this::$defaultPhoneNumberWithFinancialData);
            DB::table('transactions')->insert($sampleDebitTransaction);
        });
    }

    private function createSampleDebitTransaction($phone_number)
    {
        return [
            'vcard' => $phone_number,
            'date' =>  now()->format('Y-m-d'),
            'datetime' =>  now(),
            'type' => 'D',
            'value' => 100,
            'old_balance' => 300,
            'new_balance' => 200,
            'payment_type' => "IBAN",
            'payment_reference' => "PT88268746714423379737733",
            'pair_transaction' => null,
            'pair_vcard' => null,
            'category_id' => null,
            'description' => "sample debit transaction",
            'custom_options' => null,
            'custom_data' => null,
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => null
        ];
    }
}
