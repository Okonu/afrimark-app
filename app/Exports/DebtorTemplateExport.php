<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DebtorTemplateExport implements FromArray, WithHeadings, WithStyles
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Example data row
        return [
            [
                'ABC Company Ltd', // business_name
                'A123456789X',     // kra_pin
                'info@abccompany.co.ke', // email
                25000,             // amount_owed
                'INV-2023-001',    // invoice_number
            ],
            [
                'XYZ Enterprises', // business_name
                'P987654321Y',     // kra_pin
                'accounts@xyzenterprise.com', // email
                15000,             // amount_owed
                'INV-2023-045',    // invoice_number
            ],
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'business_name',
            'kra_pin',
            'email',
            'amount_owed',
            'invoice_number',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // header row
            1 => ['font' => ['bold' => true, 'size' => 12]],

            'A1:D1' => ['fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFDA'] // Light green for required fields
            ]],
            'E1' => ['fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FCE4D6'] // Light orange for optional fields
            ]],
        ];
    }
}
