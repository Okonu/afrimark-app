<?php

namespace App\Services\Debtor;

use App\Models\Business;
use App\Models\Debtor;
use App\Models\DebtorDocument;
use App\Notifications\DebtorListingNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class DebtorService
{
    /**
     * Create a new debtor
     *
     * @param \App\Models\Business $business
     * @param array $data
     * @return \App\Models\Debtor
     */
    public function createDebtor(Business $business, array $data)
    {
        $listingGoesLiveAt = Carbon::now()->addDays(7);

        $debtor = Debtor::create([
            'business_id' => $business->id,
            'name' => $data['name'],
            'kra_pin' => $data['kra_pin'],
            'email' => $data['email'],
            'amount_owed' => $data['amount_owed'],
            'invoice_number' => $data['invoice_number'] ?? null,
            'status' => 'pending',
            'listing_goes_live_at' => $listingGoesLiveAt,
        ]);

        if (isset($data['documents']) && is_array($data['documents'])) {
            foreach ($data['documents'] as $document) {
                DebtorDocument::create([
                    'debtor_id' => $debtor->id,
                    'file_path' => $document,
                    'original_filename' => $document,
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        $this->sendDebtorNotification($debtor);

        return $debtor;
    }

    /**
     * Update debtor payment information
     *
     * @param \App\Models\Debtor $debtor
     * @param float $paymentAmount
     * @return \App\Models\Debtor
     */
    public function updatePayment(Debtor $debtor, float $paymentAmount)
    {
        $newAmountOwed = max(0, $debtor->amount_owed - $paymentAmount);

        $debtor->amount_owed = $newAmountOwed;

        if ($newAmountOwed <= 0) {
            $debtor->status = 'paid';
        }

        $debtor->save();

        return $debtor;
    }

    /**
     * Process bulk debtor import
     *
     * @param \App\Models\Business $business
     * @param array $debtors
     * @return array
     */
    public function bulkImport(Business $business, array $debtors)
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'debtors' => [],
        ];

        foreach ($debtors as $debtorData) {
            try {
                $debtor = $this->createDebtor($business, $debtorData);
                $results['success']++;
                $results['debtors'][] = $debtor->id;
            } catch (\Exception $e) {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Get debtors that have all required documents
     *
     * @param \App\Models\Business $business
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getCompletedDebtors(Business $business)
    {
        return $business->debtors()
            ->whereHas('documents')
            ->where(function ($query) {
                $query->where('status', 'active')
                    ->orWhere('status', 'paid');
            })
            ->get();
    }

    /**
     * Get pending debtors without required documents
     *
     * @param \App\Models\Business $business
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPendingDebtors(Business $business)
    {
        return $business->debtors()
            ->where(function ($query) {
                $query->whereDoesntHave('documents')
                    ->orWhere('status', 'pending');
            })
            ->get();
    }

    /**
     * Send notification to debtor
     *
     * @param \App\Models\Debtor $debtor
     * @return void
     */
    public function sendDebtorNotification(Debtor $debtor)
    {
        $notification = new DebtorListingNotification($debtor);

        Mail::to($debtor->email)->send($notification->toMail(null));
    }

    /**
     * Publish listings that have passed their countdown period
     *
     * @return int Number of listings published
     */
    public function publishDueListings()
    {
        $count = 0;

        $dueDebtors = Debtor::where('status', 'pending')
            ->where('listing_goes_live_at', '<=', now())
            ->whereDoesntHave('disputes', function ($query) {
                $query->whereIn('status', ['pending', 'under_review']);
            })
            ->whereHas('documents')
            ->get();

        foreach ($dueDebtors as $debtor) {
            $debtor->status = 'active';
            $debtor->listed_at = now();
            $debtor->save();
            $count++;
        }

        return $count;
    }
}
