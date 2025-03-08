<?php

namespace App\Observers;

use App\Models\BusinessDebtor;
use App\Models\Invoice;
use App\Services\Calculations\InvoiceMetricsCalculator;
use App\Services\Calculations\BusinessDebtorMetricsCalculator;

class InvoiceObserver
{
    protected $invoiceMetricsCalculator;
    protected $businessDebtorMetricsCalculator;

    public function __construct(
        InvoiceMetricsCalculator $invoiceMetricsCalculator,
        BusinessDebtorMetricsCalculator $businessDebtorMetricsCalculator
    ) {
        $this->invoiceMetricsCalculator = $invoiceMetricsCalculator;
        $this->businessDebtorMetricsCalculator = $businessDebtorMetricsCalculator;
    }

    /**
     * Handle the Invoice "created" event.
     */
    public function created(Invoice $invoice): void
    {
        $this->invoiceMetricsCalculator->updateInvoiceMetrics($invoice);

        $businessDebtor = BusinessDebtor::where('business_id', $invoice->business_id)
            ->where('debtor_id', $invoice->debtor_id)
            ->first();

        if ($businessDebtor) {
            $this->businessDebtorMetricsCalculator->updateBusinessDebtorMetrics($businessDebtor);
        }
    }

    /**
     * Handle the Invoice "updated" event.
     */
    public function updated(Invoice $invoice): void
    {
        // Same actions as created
        $this->created($invoice);
    }

    /**
     * Handle the Invoice "deleted" event.
     */
    public function deleted(Invoice $invoice): void
    {
        $businessDebtor = BusinessDebtor::where('business_id', $invoice->business_id)
            ->where('debtor_id', $invoice->debtor_id)
            ->first();

        if ($businessDebtor) {
            $this->businessDebtorMetricsCalculator->updateBusinessDebtorMetrics($businessDebtor);
        }
    }
}
