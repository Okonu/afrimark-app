<?php

namespace App\Traits;

use App\Models\Business;
use App\Models\Debtor;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

trait BusinessListingsCalculator
{
    /**
     * Get negative and positive listings count for a business
     *
     * Negative listings: Number of businesses that have listed this business as a debtor with overdue invoices
     * Positive listings: Number of businesses that have listed this business as a debtor with no overdue invoices
     *
     * @param string $kraPin The KRA PIN of the business
     * @return array Returns ['negative' => count, 'positive' => count]
     */
    public function getBusinessListingsCounts(string $kraPin): array
    {
        // Find debtor by KRA PIN
        $debtor = Debtor::where('kra_pin', $kraPin)->first();

        if (!$debtor) {
            return [
                'negative' => 0,
                'positive' => 0,
                'total' => 0
            ];
        }

        $businessesWithOverdueInvoices = Invoice::where('debtor_id', $debtor->id)
            ->where('days_overdue', '>', 0)
            ->select('business_id')
            ->distinct()
            ->pluck('business_id')
            ->toArray();

        $businessesWithNonOverdueInvoices = Invoice::where('debtor_id', $debtor->id)
            ->where(function ($query) {
                $query->where('days_overdue', '<=', 0)
                    ->orWhereNull('days_overdue');
            })
            ->select('business_id')
            ->distinct()
            ->pluck('business_id')
            ->toArray();

        // We need to handle businesses that have both overdue and non-overdue invoices
        // Such businesses should only be counted in the negative listings or not
        // to inquire about this.
        $uniquePositiveBusinesses = array_diff($businessesWithNonOverdueInvoices, $businessesWithOverdueInvoices);

        return [
            'negative' => count($businessesWithOverdueInvoices),
            'positive' => count($uniquePositiveBusinesses),
            'total' => count(array_unique(array_merge($businessesWithOverdueInvoices, $businessesWithNonOverdueInvoices)))
        ];
    }

    /**
     * Get detailed information about businesses that have listed the given business
     *
     * @param string $kraPin The KRA PIN of the business
     * @return array Returns ['negative' => [...], 'positive' => [...]]
     */
    public function getBusinessListingsDetails(string $kraPin): array
    {
        $debtor = Debtor::where('kra_pin', $kraPin)->first();

        if (!$debtor) {
            return [
                'negative' => [],
                'positive' => []
            ];
        }

        $negativeBusinessIds = Invoice::where('debtor_id', $debtor->id)
            ->where('days_overdue', '>', 0)
            ->select('business_id')
            ->distinct()
            ->pluck('business_id')
            ->toArray();

        $positiveBusinessIds = Invoice::where('debtor_id', $debtor->id)
            ->where(function ($query) {
                $query->where('days_overdue', '<=', 0)
                    ->orWhereNull('days_overdue');
            })
            ->select('business_id')
            ->distinct()
            ->pluck('business_id')
            ->toArray();

        $uniquePositiveBusinessIds = array_diff($positiveBusinessIds, $negativeBusinessIds);

        $negativeBusinesses = Business::whereIn('id', $negativeBusinessIds)->get()->map(function ($business) use ($debtor) {
            return [
                'id' => $business->id,
                'name' => $business->name,
                'registration_number' => $business->registration_number,
                'total_invoices' => Invoice::where('business_id', $business->id)
                    ->where('debtor_id', $debtor->id)
                    ->count(),
                'overdue_invoices' => Invoice::where('business_id', $business->id)
                    ->where('debtor_id', $debtor->id)
                    ->where('days_overdue', '>', 0)
                    ->count(),
                'total_amount' => Invoice::where('business_id', $business->id)
                    ->where('debtor_id', $debtor->id)
                    ->sum('due_amount'),
                'overdue_amount' => Invoice::where('business_id', $business->id)
                    ->where('debtor_id', $debtor->id)
                    ->where('days_overdue', '>', 0)
                    ->sum('due_amount'),
            ];
        });

        $positiveBusinesses = Business::whereIn('id', $uniquePositiveBusinessIds)->get()->map(function ($business) use ($debtor) {
            return [
                'id' => $business->id,
                'name' => $business->name,
                'registration_number' => $business->registration_number,
                'total_invoices' => Invoice::where('business_id', $business->id)
                    ->where('debtor_id', $debtor->id)
                    ->count(),
                'total_amount' => Invoice::where('business_id', $business->id)
                    ->where('debtor_id', $debtor->id)
                    ->sum('due_amount'),
            ];
        });

        return [
            'negative' => $negativeBusinesses,
            'positive' => $positiveBusinesses
        ];
    }

    /**
     * Get negative and positive invoice counts for a business
     *
     * @param string $kraPin The KRA PIN of the business
     * @return array Returns ['negative' => count, 'positive' => count]
     */
    public function getBusinessInvoiceCounts(string $kraPin): array
    {
        $debtor = Debtor::where('kra_pin', $kraPin)->first();

        if (!$debtor) {
            return [
                'negative' => 0,
                'positive' => 0,
                'total' => 0
            ];
        }

        $negativeCount = Invoice::where('debtor_id', $debtor->id)
            ->where('days_overdue', '>', 0)
            ->count();

        $positiveCount = Invoice::where('debtor_id', $debtor->id)
            ->where(function ($query) {
                $query->where('days_overdue', '<=', 0)
                    ->orWhereNull('days_overdue');
            })
            ->count();

        return [
            'negative' => $negativeCount,
            'positive' => $positiveCount,
            'total' => $negativeCount + $positiveCount
        ];
    }

    /**
     * Get the total amount owed for negative and positive invoices
     *
     * @param string $kraPin The KRA PIN of the business
     * @return array Returns ['negative' => amount, 'positive' => amount]
     */
    public function getBusinessInvoiceAmounts(string $kraPin): array
    {
        $debtor = Debtor::where('kra_pin', $kraPin)->first();

        if (!$debtor) {
            return [
                'negative' => 0,
                'positive' => 0,
                'total' => 0
            ];
        }

        $negativeAmount = Invoice::where('debtor_id', $debtor->id)
            ->where('days_overdue', '>', 0)
            ->sum('due_amount');

        $positiveAmount = Invoice::where('debtor_id', $debtor->id)
            ->where(function ($query) {
                $query->where('days_overdue', '<=', 0)
                    ->orWhereNull('days_overdue');
            })
            ->sum('due_amount');

        return [
            'negative' => $negativeAmount,
            'positive' => $positiveAmount,
            'total' => $negativeAmount + $positiveAmount
        ];
    }
}
