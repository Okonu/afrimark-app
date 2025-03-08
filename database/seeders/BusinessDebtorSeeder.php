<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Debtor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessDebtorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Get our businesses
        $acme = Business::where('registration_number', 'A123456789X')->first();
        $xyz = Business::where('registration_number', 'B987654321Y')->first();
        $savanna = Business::where('registration_number', 'C654321987Z')->first();

        // Get all debtors
        $debtors = Debtor::all();

        // First 10 debtors primarily for Acme
        $acmeDebtors = $debtors->take(10);
        foreach ($acmeDebtors as $debtor) {
            DB::table('business_debtor')->insert([
                'business_id' => $acme->id,
                'debtor_id' => $debtor->id,
                'amount_owed' => rand(5000, 50000) / 100, // Random amount between 50.00 and 500.00
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Next 10 debtors primarily for XYZ
        $xyzDebtors = $debtors->slice(10, 10);
        foreach ($xyzDebtors as $debtor) {
            DB::table('business_debtor')->insert([
                'business_id' => $xyz->id,
                'debtor_id' => $debtor->id,
                'amount_owed' => rand(5000, 50000) / 100,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Last 10 debtors primarily for Savanna
        $savannaDebtors = $debtors->slice(20, 10);
        foreach ($savannaDebtors as $debtor) {
            DB::table('business_debtor')->insert([
                'business_id' => $savanna->id,
                'debtor_id' => $debtor->id,
                'amount_owed' => rand(5000, 50000) / 100,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Create cross-business relationships for some debtors

        // Some Acme debtors also owe to XYZ
        $crossDebtors1 = $acmeDebtors->random(3);
        foreach ($crossDebtors1 as $debtor) {
            DB::table('business_debtor')->insert([
                'business_id' => $xyz->id,
                'debtor_id' => $debtor->id,
                'amount_owed' => rand(5000, 50000) / 100,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Some XYZ debtors also owe to Savanna
        $crossDebtors2 = $xyzDebtors->random(3);
        foreach ($crossDebtors2 as $debtor) {
            DB::table('business_debtor')->insert([
                'business_id' => $savanna->id,
                'debtor_id' => $debtor->id,
                'amount_owed' => rand(5000, 50000) / 100,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Some Savanna debtors also owe to Acme
        $crossDebtors3 = $savannaDebtors->random(3);
        foreach ($crossDebtors3 as $debtor) {
            DB::table('business_debtor')->insert([
                'business_id' => $acme->id,
                'debtor_id' => $debtor->id,
                'amount_owed' => rand(5000, 50000) / 100,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Handle the special case where businesses are also debtors to each other

        // Acme is a debtor to XYZ
        $acmeAsDebtor = Debtor::where('kra_pin', 'A123456789X')->first();
        if ($acmeAsDebtor) {
            DB::table('business_debtor')->insert([
                'business_id' => $xyz->id,
                'debtor_id' => $acmeAsDebtor->id,
                'amount_owed' => 75000 / 100, // 750.00
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // XYZ is a debtor to Savanna
        $xyzAsDebtor = Debtor::where('kra_pin', 'B987654321Y')->first();
        if ($xyzAsDebtor) {
            DB::table('business_debtor')->insert([
                'business_id' => $savanna->id,
                'debtor_id' => $xyzAsDebtor->id,
                'amount_owed' => 85000 / 100, // 850.00
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Savanna is a debtor to Acme
        $savannaAsDebtor = Debtor::where('kra_pin', 'C654321987Z')->first();
        if ($savannaAsDebtor) {
            DB::table('business_debtor')->insert([
                'business_id' => $acme->id,
                'debtor_id' => $savannaAsDebtor->id,
                'amount_owed' => 65000 / 100, // 650.00
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
