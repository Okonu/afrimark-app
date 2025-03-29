<?php

namespace App\Services\Calculations;

use App\Models\Invoice;
use App\Models\BusinessDebtor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceCalculationService
{
    /**
     * Single source of truth for all invoice metric calculations
     *
     * @param Carbon|string $invoiceDate
     * @param Carbon|string $dueDate
     * @param int|null $paymentTerms
     * @return array
     */
    public function calculateInvoiceMetrics($invoiceDate, $dueDate = null, ?int $paymentTerms = null): array
    {
        $invoiceDate = $this->parseDate($invoiceDate);

        // Calculate either due date or payment terms, depending on what was provided
        if ($dueDate && !$paymentTerms) {
            $dueDate = $this->parseDate($dueDate);
            $paymentTerms = $invoiceDate->diffInDays($dueDate);
        } elseif ($paymentTerms && !$dueDate) {
            $dueDate = (clone $invoiceDate)->addDays($paymentTerms);
        } elseif (!$dueDate && !$paymentTerms) {
            // Default to 30 days if neither is provided
            $paymentTerms = 30;
            $dueDate = (clone $invoiceDate)->addDays($paymentTerms);
        } else {
            // Both were provided, recalculate payment terms to ensure consistency
            $dueDate = $this->parseDate($dueDate);
            $paymentTerms = $invoiceDate->diffInDays($dueDate);
        }

        $daysOverdue = $this->calculateDaysOverdue($dueDate);
        $dbtRatio = $this->calculateDbtRatio($daysOverdue, $paymentTerms);

        return [
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'payment_terms' => $paymentTerms,
            'days_overdue' => $daysOverdue,
            'dbt_ratio' => $dbtRatio,
        ];
    }

    /**
     * Calculate days overdue
     *
     * @param Carbon|string $dueDate
     * @return int
     */
    public function calculateDaysOverdue($dueDate): int
    {
        $dueDate = $this->parseDate($dueDate);
        return now()->gt($dueDate) ? now()->diffInDays($dueDate) : 0;
    }

    /**
     * Calculate DBT ratio
     *
     * @param int $daysOverdue
     * @param int $paymentTerms
     * @return float
     */
    public function calculateDbtRatio(int $daysOverdue, int $paymentTerms): float
    {
        return $paymentTerms > 0 ? ($daysOverdue / $paymentTerms) : 0;
    }

    /**
     * Parse date from various formats
     *
     * @param mixed $date
     * @return Carbon
     */
    public function parseDate($date): Carbon
    {
        if (empty($date)) {
            return now();
        }

        if ($date instanceof Carbon) {
            return $date;
        }

        if (is_numeric($date) && class_exists('PhpOffice\PhpSpreadsheet\Shared\Date')) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return now();
        }
    }

    /**
     * Batch update metrics for multiple invoices
     *
     * @param Collection|array $invoices
     * @return void
     */
    public function batchUpdateInvoiceMetrics($invoices): void
    {
        if (!is_array($invoices) && !$invoices instanceof Collection) {
            $invoices = [$invoices];
        }

        $updates = [];
        $businessDebtorPairs = [];

        // Prepare bulk updates without firing events
        foreach ($invoices as $invoice) {
            $metrics = $this->calculateInvoiceMetrics(
                $invoice->invoice_date,
                $invoice->due_date
            );

            $updates[] = [
                'id' => $invoice->id,
                'payment_terms' => $metrics['payment_terms'],
                'days_overdue' => $metrics['days_overdue'],
                'dbt_ratio' => $metrics['dbt_ratio'],
                'updated_at' => now()
            ];

            // Track unique business-debtor pairs that need updating
            $key = "{$invoice->business_id}-{$invoice->debtor_id}";
            $businessDebtorPairs[$key] = [
                'business_id' => $invoice->business_id,
                'debtor_id' => $invoice->debtor_id
            ];
        }

        // Execute batch update if there are records to update
        if (!empty($updates)) {
            DB::transaction(function() use ($updates, $businessDebtorPairs) {
                // Batch update invoices
                Invoice::withoutEvents(function() use ($updates) {
                    foreach ($updates as $update) {
                        Invoice::where('id', $update['id'])->update([
                            'payment_terms' => $update['payment_terms'],
                            'days_overdue' => $update['days_overdue'],
                            'dbt_ratio' => $update['dbt_ratio'],
                            'updated_at' => $update['updated_at']
                        ]);
                    }
                });

                // Update business-debtor relationships
                foreach ($businessDebtorPairs as $pair) {
                    $this->updateBusinessDebtorMetrics(
                        $pair['business_id'],
                        $pair['debtor_id']
                    );
                }
            });
        }
    }

    /**
     * Efficiently update business-debtor metrics with a single query
     *
     * @param int $businessId
     * @param int $debtorId
     * @return void
     */
    public function updateBusinessDebtorMetrics(int $businessId, int $debtorId): void
    {
        // Get all metrics in a single query to avoid multiple queries
        $metrics = DB::table('invoices')
            ->where('business_id', $businessId)
            ->where('debtor_id', $debtorId)
            ->whereNull('deleted_at')
            ->selectRaw('
                SUM(due_amount) as total_amount_owed,
                AVG(payment_terms) as avg_payment_terms,
                AVG(CASE WHEN days_overdue > 0 THEN days_overdue ELSE NULL END) as avg_days_overdue,
                AVG(dbt_ratio) as avg_dbt_ratio
            ')
            ->first();

        // Get arrays for median calculations
        $arrays = DB::table('invoices')
            ->where('business_id', $businessId)
            ->where('debtor_id', $debtorId)
            ->whereNull('deleted_at')
            ->select('payment_terms', 'days_overdue', 'dbt_ratio')
            ->get();

        $paymentTerms = $arrays->pluck('payment_terms')->filter()->toArray();
        $daysOverdue = $arrays->pluck('days_overdue')->filter(function($value) {
            return $value > 0;
        })->toArray();
        $dbtRatios = $arrays->pluck('dbt_ratio')->filter()->toArray();

        // Calculate medians
        $medianPaymentTerms = $this->calculateMedian($paymentTerms);
        $medianDaysOverdue = $this->calculateMedian($daysOverdue);
        $medianDbtRatio = $this->calculateMedian($dbtRatios);

        // Check if the business-debtor relationship exists first
        $exists = DB::table('business_debtor')
            ->where('business_id', $businessId)
            ->where('debtor_id', $debtorId)
            ->exists();

        if ($exists) {
            // Just update the existing record
            DB::table('business_debtor')
                ->where('business_id', $businessId)
                ->where('debtor_id', $debtorId)
                ->update([
                    'amount_owed' => $metrics->total_amount_owed ?? 0,
                    'average_payment_terms' => max($metrics->avg_payment_terms ?? 1, 1), // Avoid division by zero
                    'median_payment_terms' => max($medianPaymentTerms, 1), // Avoid division by zero
                    'average_days_overdue' => $metrics->avg_days_overdue ?? 0,
                    'median_days_overdue' => $medianDaysOverdue,
                    'average_dbt_ratio' => $metrics->avg_dbt_ratio ?? 0,
                    'median_dbt_ratio' => $medianDbtRatio,
                    'updated_at' => now(),
                ]);
        } else {
            // Insert a new record
            DB::table('business_debtor')->insert([
                'business_id' => $businessId,
                'debtor_id' => $debtorId,
                'amount_owed' => $metrics->total_amount_owed ?? 0,
                'average_payment_terms' => max($metrics->avg_payment_terms ?? 1, 1),
                'median_payment_terms' => max($medianPaymentTerms, 1),
                'average_days_overdue' => $metrics->avg_days_overdue ?? 0,
                'median_days_overdue' => $medianDaysOverdue,
                'average_dbt_ratio' => $metrics->avg_dbt_ratio ?? 0,
                'median_dbt_ratio' => $medianDbtRatio,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Calculate median value
     *
     * @param array $array
     * @return float
     */
    protected function calculateMedian(array $array): float
    {
        if (empty($array)) {
            return 0;
        }

        sort($array);
        $count = count($array);
        $middle = floor($count / 2);

        if ($count % 2 === 0) {
            return ($array[$middle - 1] + $array[$middle]) / 2;
        }

        return $array[$middle];
    }

    /**
     * Bulk create invoices and calculate metrics efficiently
     *
     * @param int $debtorId
     * @param int $businessId
     * @param array $invoicesData
     * @return array
     */
    public function bulkCreateInvoices(int $debtorId, int $businessId, array $invoicesData): array
    {
        $invoiceRecords = [];
        $totalAmount = 0;
        $now = now();

        foreach ($invoicesData as $data) {
            // Calculate metrics
            $metrics = $this->calculateInvoiceMetrics(
                $data['invoice_date'],
                $data['due_date']
            );

            $invoiceRecords[] = [
                'debtor_id' => $debtorId,
                'business_id' => $businessId,
                'invoice_number' => $data['invoice_number'],
                'invoice_date' => $metrics['invoice_date'],
                'due_date' => $metrics['due_date'],
                'invoice_amount' => $data['invoice_amount'],
                'due_amount' => $data['due_amount'],
                'payment_terms' => $metrics['payment_terms'],
                'days_overdue' => $metrics['days_overdue'],
                'dbt_ratio' => $metrics['dbt_ratio'],
                'created_at' => $now,
                'updated_at' => $now
            ];

            $totalAmount += (float)$data['due_amount'];
        }

        // Insert all invoices in a single transaction
        DB::transaction(function() use ($invoiceRecords, $debtorId, $businessId, $totalAmount) {
            // Bulk insert invoices without firing events
            Invoice::withoutEvents(function() use ($invoiceRecords) {
                DB::table('invoices')->insert($invoiceRecords);
            });

            // Update business-debtor relationship directly
            $this->updateBusinessDebtorMetrics($businessId, $debtorId);
        });

        return [
            'count' => count($invoiceRecords),
            'total_amount' => $totalAmount
        ];
    }
}
