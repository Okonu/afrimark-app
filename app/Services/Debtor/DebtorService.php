<?php

namespace App\Services\Debtor;

use App\Models\Business;
use App\Models\Debtor;
use App\Jobs\ProcessDebtorNotification;
use App\Jobs\ProcessDebtorDocuments;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DebtorService
{
    /**
     * Create a new debtor with optimized performance
     *
     * @param \App\Models\Business $business
     * @param array $data
     * @return \App\Models\Debtor
     */
    public function createDebtor(Business $business, array $data)
    {
        if (($business->registration_number === $data['kra_pin']) ||
            (strtolower($business->email) === strtolower($data['email']))) {
            throw new \Exception("A business cannot list itself as a debtor.");
        }

        $existingBusiness = Business::where('registration_number', $data['kra_pin'])
            ->orWhere('email', $data['email'])
            ->first();

        $listingGoesLiveAt = Carbon::now()->addDays(7);

        // Create debtor record first
        $debtor = Debtor::create([
            'business_id' => $business->id,
            'name' => $data['name'],
            'kra_pin' => $data['kra_pin'],
            'email' => $data['email'],
            'amount_owed' => $data['amount_owed'] ?? 0,
            'invoice_number' => $data['invoice_number'] ?? null,
            'status' => 'pending',
            'listing_goes_live_at' => $listingGoesLiveAt,
            'verification_token' => Str::random(64),
        ]);

        // Queue document processing and notification - don't process inline
        if (isset($data['documents']) && is_array($data['documents'])) {
            $this->queueDocumentProcessing($debtor, $data['documents']);
        }

        $this->queueDebtorNotification($debtor);

        return $debtor;
    }

    /**
     * Queue notification for debtor
     *
     * @param \App\Models\Debtor $debtor
     * @return void
     */
    public function queueDebtorNotification(Debtor $debtor)
    {
        try {
            ProcessDebtorNotification::dispatch($debtor)
                ->onQueue('notifications');

            Log::info("Debtor notification queued for: {$debtor->email}");
        } catch (\Exception $e) {
            Log::error("Failed to queue debtor notification: " . $e->getMessage());
        }
    }

    /**
     * Queue document processing without any inline processing
     *
     * @param \App\Models\Debtor $debtor
     * @param array $documentGroups
     * @return void
     */
    public function queueDocumentProcessing(Debtor $debtor, array $documentGroups)
    {
        try {
            ProcessDebtorDocuments::dispatch($debtor, $documentGroups, Auth::id() ?? 1)
                ->onQueue('document-processing');

            Log::info("Document processing queued for debtor ID: {$debtor->id}");
        } catch (\Exception $e) {
            Log::error("Failed to queue document processing: " . $e->getMessage());
        }
    }

    /**
     * Process debtor documents - this is for backward compatibility
     * It simply queues the job now without any inline processing
     *
     * @param \App\Models\Debtor $debtor
     * @param array $documentGroups
     * @return void
     */
    public function processDebtorDocuments(Debtor $debtor, array $documentGroups)
    {
        $this->queueDocumentProcessing($debtor, $documentGroups);
    }

    /**
     * Send notification to debtor - this is for backward compatibility
     * It simply queues the job now without any inline processing
     *
     * @param \App\Models\Debtor $debtor
     * @return void
     */
    public function sendDebtorNotification(Debtor $debtor)
    {
        $this->queueDebtorNotification($debtor);
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
     * Process bulk debtor import with improved performance
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
            'errors' => [],
        ];

        // Process in smaller batches to prevent timeout
        $batches = array_chunk($debtors, 5);

        foreach ($batches as $batchIndex => $batch) {
            foreach ($batch as $index => $debtorData) {
                try {
                    // Create minimal debtor first
                    $debtor = Debtor::create([
                        'name' => $debtorData['name'],
                        'kra_pin' => $debtorData['kra_pin'],
                        'email' => $debtorData['email'],
                        'status' => 'pending',
                        'listing_goes_live_at' => Carbon::now()->addDays(7),
                        'verification_token' => Str::random(64),
                    ]);

                    // Attach business relation
                    $debtor->businesses()->attach($business->id, [
                        'amount_owed' => $debtorData['amount_owed'] ?? 0,
                        'average_payment_terms' => 0,
                        'median_payment_terms' => 0,
                        'average_days_overdue' => 0,
                        'median_days_overdue' => 0,
                        'average_dbt_ratio' => 0,
                        'median_dbt_ratio' => 0,
                    ]);

                    // Queue document processing if present
                    if (isset($debtorData['documents']) && is_array($debtorData['documents'])) {
                        $this->queueDocumentProcessing($debtor, $debtorData['documents']);
                    }

                    // Queue notification
                    $this->queueDebtorNotification($debtor);

                    $results['success']++;
                    $results['debtors'][] = $debtor->id;
                } catch (\Exception $e) {
                    $results['failed']++;
                    $results['errors'][$batchIndex * 5 + $index] = $e->getMessage();
                }
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
