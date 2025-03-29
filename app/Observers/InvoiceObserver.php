<?php

namespace App\Observers;

use App\Models\BusinessDebtor;
use App\Models\Invoice;
use App\Services\Calculations\InvoiceCalculationService;

class InvoiceObserver
{
    protected $calculationService;

    public function __construct(InvoiceCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    /**
     * Handle the Invoice "created" event.
     * Only used to sync the business-debtor relationship - not for recalculating invoice fields
     */
    public function created(Invoice $invoice): void
    {
        $this->calculationService->updateBusinessDebtorMetrics(
            $invoice->business_id,
            $invoice->debtor_id
        );
    }

    /**
     * Handle the Invoice "updated" event.
     * Only used for business-debtor sync when specific fields change
     */
    public function updated(Invoice $invoice): void
    {
        // Only update business-debtor if the due amount or metrics changed
        if ($invoice->isDirty(['due_amount', 'payment_terms', 'days_overdue', 'dbt_ratio'])) {
            $this->calculationService->updateBusinessDebtorMetrics(
                $invoice->business_id,
                $invoice->debtor_id
            );
        }
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        $this->calculationService->updateBusinessDebtorMetrics(
            $invoice->business_id,
            $invoice->debtor_id
        );
    }

    /**
     * Handle the Invoice "restored" event.
     */
    public function restored(Invoice $invoice): void
    {
        $this->calculationService->updateBusinessDebtorMetrics(
            $invoice->business_id,
            $invoice->debtor_id
        );
    }
}
