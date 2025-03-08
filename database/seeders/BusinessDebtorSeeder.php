<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Debtor;
use App\Services\Calculations\BusinessDebtorMetricsCalculator;
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

        $businesses = Business::all();

        $debtors = Debtor::all();

        $businessesToMakeDebtors = $businesses->take(50);

        foreach ($businessesToMakeDebtors as $business) {

            $existingDebtor = Debtor::where('kra_pin', $business->registration_number)->first();

            if (!$existingDebtor) {
                Debtor::create([
                    'name' => $business->name,
                    'kra_pin' => $business->registration_number,
                    'email' => $business->email,
                    'status' => 'active',
                    'status_updated_at' => $now,
                    'listed_at' => $now->copy()->subDays(rand(1, 30)),
                ]);
            }
        }

        foreach ($businesses as $business) {
            $debtorCount = rand(5, 20);
            $randomDebtors = $debtors->random($debtorCount);

            foreach ($randomDebtors as $debtor) {
                if ($business->registration_number === $debtor->kra_pin) {
                    continue;
                }

                DB::table('business_debtor')->insert([
                    'business_id' => $business->id,
                    'debtor_id' => $debtor->id,
                    'amount_owed' => rand(10000, 1000000) / 100,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        for ($i = 0; $i < count($businessesToMakeDebtors) - 1; $i++) {
            $business1 = $businessesToMakeDebtors[$i];
            $business2 = $businessesToMakeDebtors[$i + 1];

            $debtor1 = Debtor::where('kra_pin', $business1->registration_number)->first();
            $debtor2 = Debtor::where('kra_pin', $business2->registration_number)->first();

            if ($debtor1 && $debtor2) {
                DB::table('business_debtor')->updateOrInsert(
                    [
                        'business_id' => $business2->id,
                        'debtor_id' => $debtor1->id,
                    ],
                    [
                        'amount_owed' => rand(10000, 1000000) / 100,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );

                DB::table('business_debtor')->updateOrInsert(
                    [
                        'business_id' => $business1->id,
                        'debtor_id' => $debtor2->id,
                    ],
                    [
                        'amount_owed' => rand(10000, 1000000) / 100,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }

        $firstBusiness = $businessesToMakeDebtors->first();
        $lastBusiness = $businessesToMakeDebtors->last();
        $firstDebtor = Debtor::where('kra_pin', $firstBusiness->registration_number)->first();
        $lastDebtor = Debtor::where('kra_pin', $lastBusiness->registration_number)->first();

        if ($firstDebtor && $lastDebtor) {
            DB::table('business_debtor')->updateOrInsert(
                [
                    'business_id' => $firstBusiness->id,
                    'debtor_id' => $lastDebtor->id,
                ],
                [
                    'amount_owed' => rand(10000, 1000000) / 100,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            DB::table('business_debtor')->updateOrInsert(
                [
                    'business_id' => $lastBusiness->id,
                    'debtor_id' => $firstDebtor->id,
                ],
                [
                    'amount_owed' => rand(10000, 1000000) / 100,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
