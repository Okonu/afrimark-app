<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaymentTemplateExport implements FromArray, WithHeadings, WithStyles
{
    /**
     * @return array
     */
    public function array(): array
    {
        return [
            [
                'A123456789Z',
                10000.00,
                '2025-03-15',
                'TRX12345678',
                'Payment for invoice INV-2023-001'
            ]
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'kra_pin',
            'amount',
            'payment_date',
            'reference_number',
            'notes'
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],

            'A1' => ['comment' => $this->buildComment('KRA PIN of the debtor (required)')],
            'B1' => ['comment' => $this->buildComment('Payment amount in KES (required)')],
            'C1' => ['comment' => $this->buildComment('Payment date (YYYY-MM-DD format, optional)')],
            'D1' => ['comment' => $this->buildComment('Reference number or transaction ID (optional)')],
            'E1' => ['comment' => $this->buildComment('Additional notes about the payment (optional)')],
        ];
    }

    /**
     * Build a comment object
     *
     * @param string $text
     * @return \PhpOffice\PhpSpreadsheet\Comment
     */
    private function buildComment(string $text)
    {
        return new \PhpOffice\PhpSpreadsheet\Comment(
            $text,
            'System',
            'Instructions'
        );
    }
}
