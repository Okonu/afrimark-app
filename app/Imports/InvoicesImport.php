<?php

namespace App\Imports;

use App\Models\Invoice;
use App\Models\Debtor;
use App\Models\Business;
use App\Traits\InvoiceCalculations;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;

class InvoicesImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithValidation, WithChunkReading
{
    use Importable, InvoiceCalculations;

    protected int $businessId;
    protected int $userId;
    protected int $rowCount = 0;
    public bool $hasHeaders = true;
    protected array $updatedRelationships = [];

    public function __construct(int $businessId, int $userId)
    {
        $this->businessId = $businessId;
        $this->userId = $userId;
    }

    public function collection(Collection $collection)
    {
        $business = Business::findOrFail($this->businessId);

        foreach ($collection as $row) {
            if (empty($row['debtor_kra_pin']) ||
                empty($row['invoice_number']) ||
                empty($row['invoice_date']) ||
                (empty($row['due_date']) && empty($row['payment_terms'])) ||
                empty($row['invoice_amount'])) {
                continue;
            }

            $debtor = Debtor::where('kra_pin', $row['debtor_kra_pin'])->first();

            if (!$debtor) {
                if (!empty($row['debtor_name']) && !empty($row['debtor_email'])) {
                    $debtor = Debtor::create([
                        'name' => $row['debtor_name'],
                        'kra_pin' => $row['debtor_kra_pin'],
                        'email' => $row['debtor_email'],
                        'status' => 'pending',
                        'listing_goes_live_at' => now()->addDays(7),
                        'verification_token' => Str::random(64),
                    ]);

                    $business->debtors()->attach($debtor->id, [
                        'amount_owed' => $row['invoice_amount'],
                    ]);
                } else {
                    continue;
                }
            }

            $invoiceAmount = $row['invoice_amount'];
            $dueAmount = $row['due_amount'] ?? $invoiceAmount;

            $metrics = $this->calculateInvoiceMetrics([
                'invoice_date' => $row['invoice_date'],
                'due_date' => $row['due_date'] ?? null,
                'payment_terms' => $row['payment_terms'] ?? null,
            ]);

            Invoice::updateOrCreate(
                [
                    'business_id' => $this->businessId,
                    'debtor_id' => $debtor->id,
                    'invoice_number' => $row['invoice_number'],
                ],
                [
                    'invoice_date' => $metrics['invoice_date'],
                    'due_date' => $metrics['due_date'],
                    'invoice_amount' => $invoiceAmount,
                    'due_amount' => $dueAmount,
                    'payment_terms' => $metrics['payment_terms'],
                    'days_overdue' => $metrics['days_overdue'],
                    'dbt_ratio' => $metrics['dbt_ratio'],
                ]
            );

            $key = "{$this->businessId}_{$debtor->id}";
            $this->updatedRelationships[$key] = [
                'business_id' => $this->businessId,
                'debtor_id' => $debtor->id
            ];

            $this->rowCount++;
        }

        foreach ($this->updatedRelationships as $relationship) {
            Invoice::syncBusinessDebtorRelationship(
                $relationship['business_id'],
                $relationship['debtor_id']
            );
        }
    }

    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    public function rules(): array
    {
        return [
            '*.debtor_kra_pin' => ['required', 'string', 'max:255'],
            '*.invoice_number' => ['required', 'string', 'max:255'],
            '*.invoice_date' => ['required'],
            '*.due_date' => ['required_without:*.payment_terms'],
            '*.payment_terms' => ['required_without:*.due_date', 'nullable', 'integer', 'min:0'],
            '*.invoice_amount' => ['required', 'numeric', 'min:0'],
            '*.due_amount' => ['nullable', 'numeric', 'min:0'],
            '*.debtor_name' => ['nullable', 'string', 'max:255'],
            '*.debtor_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            '*.debtor_kra_pin.required' => 'Debtor KRA PIN is required',
            '*.invoice_number.required' => 'Invoice number is required',
            '*.invoice_date.required' => 'Invoice date is required',
            '*.due_date.required_without' => 'Either due date or payment terms must be provided',
            '*.payment_terms.required_without' => 'Either due date or payment terms must be provided',
            '*.invoice_amount.required' => 'Invoice amount is required',
            '*.invoice_amount.numeric' => 'Invoice amount must be a number',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
