<?php

namespace App\Services\Calculations;

use App\Models\Invoice;
use Carbon\Carbon;

class InvoiceMetricsCalculator
{
    /**
     * Calculate metrics for an invoice
     */
    public function calculateMetrics(Invoice $invoice): array
    {
        return [
            'payment_terms' => $this->calculatePaymentTerms($invoice),
            'days_overdue' => $this->calculateDaysOverdue($invoice),
            'dbt_ratio' => $this->calculateDbtRatio($invoice),
        ];
    }

    /**
     * Calculate payment terms (days between invoice date and due date)
     */
    public function calculatePaymentTerms(Invoice $invoice): int
    {
        return $invoice->invoice_date->diffInDays($invoice->due_date);
    }

    /**
     * Calculate days overdue (days between due date and today, if overdue)
     */
    public function calculateDaysOverdue(Invoice $invoice): int
    {
        if (Carbon::now()->lessThanOrEqualTo($invoice->due_date)) {
            return 0;
        }

        return Carbon::now()->diffInDays($invoice->due_date);
    }

    /**
     * Calculate DBT Ratio (Days Beyond Terms ratio)
     */
    public function calculateDbtRatio(Invoice $invoice): float
    {
        $paymentTerms = $this->calculatePaymentTerms($invoice);

        if ($paymentTerms === 0) {
            return 0;
        }

        return $this->calculateDaysOverdue($invoice) / $paymentTerms;
    }

    /**
     * Update metrics for an invoice and save to database
     */
    public function updateInvoiceMetrics(Invoice $invoice): void
    {
        $metrics = $this->calculateMetrics($invoice);

        $invoice->payment_terms = $metrics['payment_terms'];
        $invoice->days_overdue = $metrics['days_overdue'];
        $invoice->dbt_ratio = $metrics['dbt_ratio'];

        $invoice->save();
    }
}
