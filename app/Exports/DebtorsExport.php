<?php

namespace App\Exports;

use App\Models\Debtor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DebtorsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $debtors;

    /**
     * @param \Illuminate\Database\Eloquent\Collection|array $debtors
     */
    public function __construct($debtors)
    {
        $this->debtors = $debtors;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->debtors);
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Business Name',
            'KRA PIN',
            'Email',
            'Amount Owed',
            'Invoice Number',
            'Status',
            'Listed Date',
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->name,
            $row->kra_pin,
            $row->email,
            $row->amount_owed,
            $row->invoice_number ?? 'N/A',
            ucfirst($row->status),
            $row->listed_at ? $row->listed_at->format('Y-m-d H:i:s') : 'Not Listed',
        ];
    }

    /**
     * @param Worksheet $sheet
     *
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
