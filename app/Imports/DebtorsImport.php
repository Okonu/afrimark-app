<?php

namespace App\Imports;

use App\Models\Debtor;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class DebtorsImport implements ToCollection, WithHeadingRow, WithValidation, SkipsEmptyRows, WithChunkReading
{
    protected $businessId;
    protected $userId;
    protected $rowCount = 0;
    public $hasHeaders = true;

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
        foreach ($collection as $row) {
            // Create a new debtor record
            $debtor = Debtor::create([
                'business_id' => $this->businessId,
                'name' => $row['business_name'],
                'kra_pin' => $row['kra_pin'],
                'email' => $row['email'],
                'amount_owed' => $row['amount_owed'],
                'invoice_number' => $row['invoice_number'] ?? null,
                'status' => 'pending',
                'listing_goes_live_at' => Carbon::now()->addDays(7),
            ]);

            $this->rowCount++;
        }
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'business_name' => 'required|string|max:255',
            'kra_pin' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'amount_owed' => 'required|numeric|min:0',
            'invoice_number' => 'nullable|string|max:255',
        ];
    }

    /**
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'business_name.required' => 'Business name is required',
            'kra_pin.required' => 'KRA PIN is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'amount_owed.required' => 'Amount owed is required',
            'amount_owed.numeric' => 'Amount owed must be a number',
            'amount_owed.min' => 'Amount owed must be greater than or equal to zero',
        ];
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 100;
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
