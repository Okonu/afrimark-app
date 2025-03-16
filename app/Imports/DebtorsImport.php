<?php

namespace App\Imports;

use App\Models\Debtor;
use App\Enums\DebtorStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;

class DebtorsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithValidation
{
    use Importable;

    protected int $businessId;
    protected int $userId;
    protected int $rowCount = 0;
    public bool $hasHeaders = true;

    /**
     * @param int $businessId
     * @param int $userId
     */
    public function __construct(int $businessId, int $userId)
    {
        $this->businessId = $businessId;
        $this->userId = $userId;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        $business = \App\Models\Business::findOrFail($this->businessId);

        foreach ($collection as $row) {
            if (empty($row['name']) || empty($row['kra_pin']) || empty($row['email']) || empty($row['amount_owed'])) {
                continue;
            }

            $debtor = Debtor::where('kra_pin', $row['kra_pin'])->first();

            if (!$debtor) {
                $debtor = Debtor::create([
                    'name' => $row['name'],
                    'kra_pin' => $row['kra_pin'],
                    'email' => $row['email'],
                    'status' => 'active',
                    'listing_goes_live_at' => now()->addDays(7),
                    'verification_token' => Str::random(64),
                ]);
            }

            $existingRelation = $business->debtors()->where('debtor_id', $debtor->id)->exists();

            if (!$existingRelation) {
                $business->debtors()->attach($debtor->id, [
                    'amount_owed' => $row['amount_owed'],
                ]);
            } else {
                $business->debtors()->updateExistingPivot($debtor->id, [
                    'amount_owed' => $row['amount_owed'],
                ]);
            }

            // Store invoice number if provided
//            if (!empty($row['invoice_number'])) {
//                // Only create invoice if the model exists
//                if (class_exists('\App\Models\Invoice')) {
//                    $debtor->invoices()->updateOrCreate(
//                        ['invoice_number' => $row['invoice_number']],
//                        [
//                            'business_id' => $this->businessId,
//                            'amount' => $row['amount_owed'],
//                            'date' => now(),
//                        ]
//                    );
//                }
//            }

            $this->rowCount++;
        }
    }

    /**
     * Get row count
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '*.name' => ['required', 'string', 'max:255'],
            '*.kra_pin' => ['required', 'string', 'max:255'],
            '*.email' => ['required', 'email', 'max:255'],
            '*.amount_owed' => ['required', 'numeric', 'min:0'],
//            '*.invoice_number' => ['nullable', 'string', 'max:255'],
        ];
    }
}
