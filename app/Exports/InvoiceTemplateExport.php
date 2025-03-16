<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;

class InvoiceTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnFormatting
{

    public function array(): array
    {
        return [
            [
                'A123456789Z', // debtor_kra_pin
                'ABC Company Ltd', // debtor_name
                'accounts@abccompany.com', // debtor_email
                'INV-2025-001', // invoice_number
                '2025-03-01', // invoice_date
                '2025-04-01', // due_date
                30, // payment_terms (days)
                150000, // invoice_amount
                150000, // due_amount (same as invoice_amount for a new invoice)
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'debtor_kra_pin',
            'debtor_name',
            'debtor_email',
            'invoice_number',
            'invoice_date',
            'due_date',
            'payment_terms',
            'invoice_amount',
            'due_amount'
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_DATE_YYYYMMDD,
            'F' => NumberFormat::FORMAT_DATE_YYYYMMDD,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],

            'A1' => ['comment' => $this->buildComment('KRA PIN of the debtor (required)')],
            'B1' => ['comment' => $this->buildComment('Business name of the debtor (only needed for new debtors)')],
            'C1' => ['comment' => $this->buildComment('Email of the debtor (only needed for new debtors)')],
            'D1' => ['comment' => $this->buildComment('Invoice number (required)')],
            'E1' => ['comment' => $this->buildComment('Invoice date in YYYY-MM-DD format (required)')],
            'F1' => ['comment' => $this->buildComment('Due date in YYYY-MM-DD format (required)')],
            'G1' => ['comment' => $this->buildComment('Payment terms in days - will be calculated from dates if not provided')],
            'H1' => ['comment' => $this->buildComment('Total invoice amount in KES (required)')],
            'I1' => ['comment' => $this->buildComment('Amount still due in KES (defaults to invoice amount if not provided)')],
        ];
    }

    private function buildComment(string $text)
    {
        return new \PhpOffice\PhpSpreadsheet\Comment(
            $text,
            'System',
            'Instructions'
        );
    }
}
