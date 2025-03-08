<?php

namespace App\Services\Calculations;

use App\Models\BusinessDebtor;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class BusinessDebtorMetricsCalculator
{
    /**
     * Calculate all metrics for a business-debtor relationship
     */
    public function calculateMetrics(BusinessDebtor $businessDebtor): array
    {
        $averagePaymentTerms = $this->calculateAveragePaymentTerms($businessDebtor);
        $medianPaymentTerms = $this->calculateMedianPaymentTerms($businessDebtor);
        $averageDaysOverdue = $this->calculateAverageDaysOverdue($businessDebtor);
        $medianDaysOverdue = $this->calculateMedianDaysOverdue($businessDebtor);

        return [
            'average_payment_terms' => $averagePaymentTerms,
            'median_payment_terms' => $medianPaymentTerms,
            'average_days_overdue' => $averageDaysOverdue,
            'median_days_overdue' => $medianDaysOverdue,
            'average_dbt_ratio' => $this->calculateAverageDbtRatio($averagePaymentTerms, $averageDaysOverdue),
            'median_dbt_ratio' => $this->calculateMedianDbtRatio($medianPaymentTerms, $medianDaysOverdue),
        ];
    }

    /**
     * Calculate average payment terms
     */
    public function calculateAveragePaymentTerms(BusinessDebtor $businessDebtor): float
    {
        $avg = Invoice::where('business_id', $businessDebtor->business_id)
            ->where('debtor_id', $businessDebtor->debtor_id)
            ->avg('payment_terms');

        // Ensure we always return at least 1 for terms to avoid division by zero
        return max(($avg ?? 0), 1);
    }

    /**
     * Calculate median payment terms
     */
    public function calculateMedianPaymentTerms(BusinessDebtor $businessDebtor): float
    {
        $values = Invoice::where('business_id', $businessDebtor->business_id)
            ->where('debtor_id', $businessDebtor->debtor_id)
            ->pluck('payment_terms')
            ->filter()
            ->sort()
            ->values();

        $count = $values->count();

        if ($count === 0) {
            return 1; // Return 1 instead of 0 to avoid division by zero
        }

        if ($count % 2 === 0) {
            return max(($values[($count / 2) - 1] + $values[$count / 2]) / 2, 1);
        }

        return max($values->get(intval($count / 2)), 1);
    }

    /**
     * Calculate average days overdue
     */
    public function calculateAverageDaysOverdue(BusinessDebtor $businessDebtor): float
    {
        $avg = Invoice::where('business_id', $businessDebtor->business_id)
            ->where('debtor_id', $businessDebtor->debtor_id)
            ->avg('days_overdue');

        return $avg ?? 0;
    }

    /**
     * Calculate median days overdue
     */
    public function calculateMedianDaysOverdue(BusinessDebtor $businessDebtor): float
    {
        $values = Invoice::where('business_id', $businessDebtor->business_id)
            ->where('debtor_id', $businessDebtor->debtor_id)
            ->pluck('days_overdue')
            ->filter()
            ->sort()
            ->values();

        $count = $values->count();

        if ($count === 0) {
            return 0;
        }

        if ($count % 2 === 0) {
            return ($values[($count / 2) - 1] + $values[$count / 2]) / 2;
        }

        return $values->get(intval($count / 2));
    }

    /**
     * Calculate average DBT ratio
     */
    public function calculateAverageDbtRatio(float $avgPaymentTerms, float $avgDaysOverdue): float
    {
        // Ensure we never divide by zero
        if ($avgPaymentTerms <= 0) {
            return 0;
        }

        return $avgDaysOverdue / $avgPaymentTerms;
    }

    /**
     * Calculate median DBT ratio
     */
    public function calculateMedianDbtRatio(float $medianPaymentTerms, float $medianDaysOverdue): float
    {
        // Ensure we never divide by zero
        if ($medianPaymentTerms <= 0) {
            return 0;
        }

        return $medianDaysOverdue / $medianPaymentTerms;
    }

    /**
     * Update metrics for a business-debtor relationship and save to database
     */
    public function updateBusinessDebtorMetrics(BusinessDebtor $businessDebtor): void
    {
        $metrics = $this->calculateMetrics($businessDebtor);

        $businessDebtor->average_payment_terms = $metrics['average_payment_terms'];
        $businessDebtor->median_payment_terms = $metrics['median_payment_terms'];
        $businessDebtor->average_days_overdue = $metrics['average_days_overdue'];
        $businessDebtor->median_days_overdue = $metrics['median_days_overdue'];
        $businessDebtor->average_dbt_ratio = $metrics['average_dbt_ratio'];
        $businessDebtor->median_dbt_ratio = $metrics['median_dbt_ratio'];

        $businessDebtor->save();
    }
}
