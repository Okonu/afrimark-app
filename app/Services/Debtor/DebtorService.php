<?php

namespace App\Services\Debtor;

use App\Models\Business;
use App\Models\Debtor;
use App\Models\DebtorDocument;
use App\Notifications\DebtorListingNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
        if (($business->registration_number === $data['kra_pin']) ||
            (strtolower($business->email) === strtolower($data['email']))) {
            throw new \Exception("A business cannot list itself as a debtor.");
        }

        $existingBusiness = Business::where('registration_number', $data['kra_pin'])
            ->orWhere('email', $data['email'])
            ->first();

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
            'verification_token' => Str::random(64),
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
            'errors' => [],
        ];

        foreach ($debtors as $index => $debtorData) {
            try {
                $debtor = $this->createDebtor($business, $debtorData);
                $results['success']++;
                $results['debtors'][] = $debtor->id;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][$index] = $e->getMessage();
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
        try {
            if (!$debtor->relationLoaded('business')) {
                $debtor->load('business');
            }

            $businessName = $debtor->business ? $debtor->business->name : 'A business on our platform';

            if (!$debtor->verification_token) {
                $debtor->verification_token = Str::random(64);
                $debtor->save();
            }

            $registrationUrl = route('debtor.verify', [
                'debtor_id' => $debtor->id,
                'token' => $debtor->verification_token
            ]);

            $loginUrl = route('filament.client.auth.login', [
                'redirect' => route('filament.client.pages.disputes-page-manager', ['tab' => 'disputable-listings'])
            ]);

            $content = view('emails.debtor-listing', [
                'debtor' => $debtor,
                'businessName' => $businessName,
                'amountOwed' => number_format($debtor->amount_owed, 2),
                'invoiceNumber' => $debtor->invoice_number ?? 'N/A',
                'disputeUrl' => $loginUrl,
                'registrationUrl' => $registrationUrl,
                'appName' => config('app.name')
            ])->render();

            Mail::html($content, function ($message) use ($debtor) {
                $message->to($debtor->email)
                    ->subject('Important: Your Business Has Been Listed as a Debtor');
            });

            \Log::info("Debtor notification sent successfully to: {$debtor->email}");
        } catch (\Exception $e) {
            \Log::error("Failed to send debtor notification: " . $e->getMessage());

            // Fallback plain text email
            Mail::raw("Your business has been listed as a debtor for {$debtor->amount_owed} KES. This listing will become publicly visible in 7 days unless resolved.", function($message) use ($debtor) {
                $message->to($debtor->email)
                    ->subject('Important: Your Business Has Been Listed as a Debtor');
            });
        }
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
