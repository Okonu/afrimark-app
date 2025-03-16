<?php

namespace App\Imports;

use App\Models\Debtor;
use App\Models\Business;
use App\Models\Payment;
use App\Enums\DebtorStatus;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;

class PaymentsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows, WithValidation
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
        $business = Business::findOrFail($this->businessId);

        foreach ($collection as $row) {
            // Skip row if it's empty or if required fields are missing
            if (empty($row['kra_pin']) || empty($row['amount'])) {
                continue;
            }

            // Find the debtor by KRA PIN
            $debtor = Debtor::where('kra_pin', $row['kra_pin'])->first();

            if (!$debtor) {
                continue; // Skip if debtor doesn't exist
            }

            // Check if this business actually has this debtor
            $pivot = $business->debtors()->where('debtor_id', $debtor->id)->first()?->pivot;

            if (!$pivot) {
                continue; // Skip if no relationship exists
            }

            // Create payment record
            $payment = new Payment([
                'business_id' => $this->businessId,
                'debtor_id' => $debtor->id,
                'amount' => $row['amount'],
                'payment_date' => $row['payment_date'] ?? now(),
                'reference_number' => $row['reference_number'] ?? null,
                'notes' => $row['notes'] ?? null,
                'recorded_by' => $this->userId,
            ]);

            $payment->save();

            // Update the amount owed in the pivot table
            $newAmountOwed = max(0, $pivot->amount_owed - $row['amount']);

            $business->debtors()->updateExistingPivot($debtor->id, [
                'amount_owed' => $newAmountOwed,
            ]);

            // If amount is fully paid, update debtor status
            if ($newAmountOwed <= 0) {
                $debtor->update([
                    'status' => DebtorStatus::PAID->value,
                    'status_notes' => 'Paid via bulk import',
                    'status_updated_by' => $this->userId,
                    'status_updated_at' => now(),
                ]);
            } elseif ($row['amount'] > 0) {
                // Partial payment
                $debtor->update([
                    'status' => DebtorStatus::PARTIAL->value,
                    'status_notes' => 'Partial payment via bulk import',
                    'status_updated_by' => $this->userId,
                    'status_updated_at' => now(),
                ]);
            }

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
            '*.kra_pin' => ['required', 'string', 'max:255'],
            '*.amount' => ['required', 'numeric', 'min:0'],
            '*.payment_date' => ['nullable', 'date'],
            '*.reference_number' => ['nullable', 'string', 'max:255'],
            '*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            '*.kra_pin.required' => 'KRA PIN is required',
            '*.amount.required' => 'Payment amount is required',
            '*.amount.numeric' => 'Payment amount must be a number',
            '*.amount.min' => 'Payment amount must be greater than or equal to 0',
        ];
    }
}
