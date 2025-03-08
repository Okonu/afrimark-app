<?php

namespace Database\Seeders;

use App\Models\BusinessDebtor;
use App\Services\Calculations\BusinessDebtorMetricsCalculator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessDebtorMetricsSeeder extends Seeder
{
    protected $businessDebtorMetricsCalculator;

    public function __construct(BusinessDebtorMetricsCalculator $businessDebtorMetricsCalculator)
    {
        $this->businessDebtorMetricsCalculator = $businessDebtorMetricsCalculator;
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $businessDebtorRelationships = DB::table('business_debtor')->get();

        foreach ($businessDebtorRelationships as $relationship) {
            $businessDebtor = BusinessDebtor::find($relationship->id);

            if ($businessDebtor) {
                $metrics = $this->businessDebtorMetricsCalculator->calculateMetrics($businessDebtor);

                DB::table('business_debtor')
                    ->where('id', $relationship->id)
                    ->update([
                        'average_payment_terms' => $metrics['average_payment_terms'],
                        'median_payment_terms' => $metrics['median_payment_terms'],
                        'average_days_overdue' => $metrics['average_days_overdue'],
                        'median_days_overdue' => $metrics['median_days_overdue'],
                        'average_dbt_ratio' => $metrics['average_dbt_ratio'],
                        'median_dbt_ratio' => $metrics['median_dbt_ratio'],
                        'updated_at' => now(),
                    ]);
            }
        }
    }
}
