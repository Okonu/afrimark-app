<?php

namespace App\Imports;

use App\Models\Debtor;
use App\Services\Debtor\DebtorService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class PaymentsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    protected $businessId;
    protected $userId;
    protected $rowCount = 0;
    protected $debtorService;
    public $hasHeaders = true;

    /**
     * @param int $businessId
     * @param int $userId
     * @param DebtorService $debtorService
     */
    public function __construct(int $businessId, int $userId, DebtorService $debtorService)
    {
        $this->businessId = $businessId;
        $this->userId = $userId;
        $this->debtorService = $debtorService;
    }

    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $debtor = Debtor::where('business_id', $this->businessId)
                ->where('kra_pin', $row['kra_pin'])
                ->first();

            if ($debtor) {
                $this->debtorService->updatePayment($debtor, $row['payment_amount']);
                $this->rowCount++;
            }
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'kra_pin' => 'required|string|max:255',
            'payment_amount' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_reference' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'kra_pin.required' => 'KRA PIN is required',
            'payment_amount.required' => 'Payment amount is required',
            'payment_amount.numeric' => 'Payment amount must be a number',
            'payment_amount.min' => 'Payment amount must be greater than or equal to zero',
            'payment_date.date' => 'Payment date must be a valid date',
        ];
    }

    /**
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * @return bool
     */
    public function withHeadingRow(): bool
    {
        return $this->hasHeaders;
    }
}
