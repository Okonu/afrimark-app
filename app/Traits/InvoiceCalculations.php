<?php

namespace App\Traits;

use Carbon\Carbon;

trait InvoiceCalculations
{

    public function calculateDaysOverdue($dueDate): int
    {
        if (!$dueDate instanceof \DateTimeInterface) {
            $dueDate = Carbon::parse($dueDate);
        }

        return now()->gt($dueDate) ? now()->diffInDays($dueDate) * -1 : 0;
    }

    public function calculateDbtRatio(int $daysOverdue, int $paymentTerms): float
    {
        return $paymentTerms > 0 ? ($daysOverdue / $paymentTerms) : 0;
    }

    public function calculatePaymentTerms($invoiceDate, $dueDate): int
    {
        if (!$invoiceDate instanceof \DateTimeInterface) {
            $invoiceDate = Carbon::parse($invoiceDate);
        }

        if (!$dueDate instanceof \DateTimeInterface) {
            $dueDate = Carbon::parse($dueDate);
        }

        return $invoiceDate->diffInDays($dueDate);
    }

    public function calculateDueDate($invoiceDate, int $paymentTerms): Carbon
    {
        if (!$invoiceDate instanceof \DateTimeInterface) {
            $invoiceDate = Carbon::parse($invoiceDate);
        }

        return (clone $invoiceDate)->addDays($paymentTerms);
    }

    public function parseDate($date): Carbon
    {
        if (empty($date)) {
            return now();
        }

        if (is_numeric($date) && class_exists('PhpOffice\PhpSpreadsheet\Shared\Date')) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date));
        }

        if ($date instanceof Carbon) {
            return $date;
        }

        try {
            return Carbon::parse($date);
        } catch (\Exception $e) {
            return now();
        }
    }

    public function calculateInvoiceMetrics(array $data): array
    {
        $invoiceDate = $this->parseDate($data['invoice_date'] ?? now());
        $paymentTerms = null;
        $dueDate = null;

        if (!empty($data['payment_terms'])) {
            $paymentTerms = (int) $data['payment_terms'];
            $dueDate = $this->calculateDueDate($invoiceDate, $paymentTerms);
        } elseif (!empty($data['due_date'])) {
            $dueDate = $this->parseDate($data['due_date']);
            $paymentTerms = $this->calculatePaymentTerms($invoiceDate, $dueDate);
        } else {
            $paymentTerms = 30;
            $dueDate = $this->calculateDueDate($invoiceDate, $paymentTerms);
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
}
