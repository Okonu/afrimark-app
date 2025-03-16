<?php

namespace App\Traits;

use App\Models\Invoice;
use App\Models\BusinessDebtor;
use Illuminate\Support\Facades\DB;

trait BusinessDebtorSyncing
{
    public static function bootBusinessDebtorSyncing()
    {
        static::created(function ($invoice) {
            static::syncBusinessDebtorRelationship($invoice->business_id, $invoice->debtor_id);
        });

        static::updated(function ($invoice) {
            if ($invoice->isDirty(['due_amount', 'business_id', 'debtor_id', 'payment_terms', 'days_overdue', 'dbt_ratio'])) {
                if ($invoice->isDirty(['business_id', 'debtor_id'])) {
                    $originalBusinessId = $invoice->getOriginal('business_id');
                    $originalDebtorId = $invoice->getOriginal('debtor_id');

                    if ($originalBusinessId && $originalDebtorId) {
                        static::syncBusinessDebtorRelationship($originalBusinessId, $originalDebtorId);
                    }
                }

                static::syncBusinessDebtorRelationship($invoice->business_id, $invoice->debtor_id);
            }
        });

        static::deleted(function ($invoice) {
            static::syncBusinessDebtorRelationship($invoice->business_id, $invoice->debtor_id);
        });

        static::restored(function ($invoice) {
            static::syncBusinessDebtorRelationship($invoice->business_id, $invoice->debtor_id);
        });
    }

    public static function syncBusinessDebtorAmount($businessId, $debtorId)
    {
        $totalDueAmount = Invoice::where('business_id', $businessId)
            ->where('debtor_id', $debtorId)
            ->sum('due_amount');

        DB::table('business_debtor')
            ->updateOrInsert(
                [
                    'business_id' => $businessId,
                    'debtor_id' => $debtorId,
                ],
                [
                    'amount_owed' => $totalDueAmount,
                    'updated_at' => now(),
                ]
            );
    }

    public static function syncBusinessDebtorRelationship(int $businessId, int $debtorId): void
    {
        $totalDueAmount = Invoice::where('business_id', $businessId)
            ->where('debtor_id', $debtorId)
            ->sum('due_amount');

        $invoices = Invoice::where('business_id', $businessId)
            ->where('debtor_id', $debtorId)
            ->get();

        $avgPaymentTerms = 0;
        $medianPaymentTerms = 0;
        $avgDaysOverdue = 0;
        $medianDaysOverdue = 0;
        $avgDbtRatio = 0;
        $medianDbtRatio = 0;

        if (!$invoices->isEmpty()) {
            $paymentTerms = $invoices->pluck('payment_terms')->toArray();
            $avgPaymentTerms = $invoices->avg('payment_terms');
            $medianPaymentTerms = static::calculateMedian($paymentTerms);

            $overdueInvoices = $invoices->filter(fn($invoice) => $invoice->days_overdue > 0);
            $daysOverdue = $overdueInvoices->pluck('days_overdue')->toArray();
            $avgDaysOverdue = $overdueInvoices->isEmpty() ? 0 : $overdueInvoices->avg('days_overdue');
            $medianDaysOverdue = static::calculateMedian($daysOverdue);

            $dbtRatios = $invoices->pluck('dbt_ratio')->toArray();
            $avgDbtRatio = $invoices->avg('dbt_ratio');
            $medianDbtRatio = static::calculateMedian($dbtRatios);
        }

        DB::table('business_debtor')
            ->updateOrInsert(
                [
                    'business_id' => $businessId,
                    'debtor_id' => $debtorId,
                ],
                [
                    'amount_owed' => $totalDueAmount,
                    'average_payment_terms' => $avgPaymentTerms,
                    'median_payment_terms' => $medianPaymentTerms,
                    'average_days_overdue' => $avgDaysOverdue,
                    'median_days_overdue' => $medianDaysOverdue,
                    'average_dbt_ratio' => $avgDbtRatio,
                    'median_dbt_ratio' => $medianDbtRatio,
                    'updated_at' => now(),
                ]
            );
    }

    protected static function calculateMedian(array $array)
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

    public static function resyncAllBusinessDebtorAmounts()
    {
        $stats = [
            'updated' => 0,
            'created' => 0
        ];

        $relationships = Invoice::select('business_id', 'debtor_id')
            ->groupBy('business_id', 'debtor_id')
            ->get();

        foreach ($relationships as $rel) {
            $existing = DB::table('business_debtor')
                ->where('business_id', $rel->business_id)
                ->where('debtor_id', $rel->debtor_id)
                ->first();

            static::syncBusinessDebtorRelationship($rel->business_id, $rel->debtor_id);

            $stats[$existing ? 'updated' : 'created']++;
        }

        $orphanedRelationships = DB::table('business_debtor')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('invoices')
                    ->whereRaw('invoices.business_id = business_debtor.business_id')
                    ->whereRaw('invoices.debtor_id = business_debtor.debtor_id');
            })
            ->get();

        foreach ($orphanedRelationships as $rel) {
            DB::table('business_debtor')
                ->where('id', $rel->id)
                ->update([
                    'amount_owed' => 0,
                    'average_payment_terms' => 0,
                    'median_payment_terms' => 0,
                    'average_days_overdue' => 0,
                    'median_days_overdue' => 0,
                    'average_dbt_ratio' => 0,
                    'median_dbt_ratio' => 0,
                    'updated_at' => now(),
                ]);

            $stats['updated']++;
        }

        return $stats;
    }
}
