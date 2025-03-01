<?php

namespace App\Services\Dispute;

use App\Models\Debtor;
use App\Models\Dispute;
use App\Models\DisputeDocument;
use App\Notifications\DisputeCreatedNotification;
use Illuminate\Support\Facades\Auth;

class DisputeService
{
    /**
     * Create a new dispute
     *
     * @param \App\Models\Debtor $debtor
     * @param array $data
     * @return \App\Models\Dispute
     */
    public function createDispute(Debtor $debtor, array $data)
    {
        $dispute = Dispute::create([
            'debtor_id' => $debtor->id,
            'business_id' => $debtor->business_id,
            'dispute_type' => $data['dispute_type'],
            'description' => $data['description'],
            'status' => 'pending',
        ]);

        if (isset($data['documents']) && is_array($data['documents'])) {
            foreach ($data['documents'] as $document) {
                DisputeDocument::create([
                    'dispute_id' => $dispute->id,
                    'file_path' => $document,
                    'original_filename' => $document,
                    'uploaded_by' => Auth::id(),
                ]);
            }
        }

        $debtor->status = 'disputed';
        $debtor->save();

        $this->notifyLister($dispute);

        return $dispute;
    }

    /**
     * Update dispute status
     *
     * @param \App\Models\Dispute $dispute
     * @param string $status
     * @param string|null $notes
     * @return \App\Models\Dispute
     */
    public function updateDisputeStatus(Dispute $dispute, string $status, ?string $notes = null)
    {
        $dispute->status = $status;

        if ($notes) {
            $dispute->notes = $notes;
        }

        if (in_array($status, ['resolved_approved', 'resolved_rejected'])) {
            $dispute->resolved_at = now();
            $dispute->resolved_by = Auth::id();
        }

        $dispute->save();

        $this->updateDebtorBasedOnResolution($dispute);

        return $dispute;
    }

    /**
     * Get disputes for a business
     *
     * @param \App\Models\Business $business
     * @param string|null $status
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getBusinessDisputes($business, $status = null)
    {
        $query = Dispute::where('business_id', $business->id);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->with(['debtor', 'documents'])->get();
    }

    /**
     * Notify the original lister about the dispute
     *
     * @param \App\Models\Dispute $dispute
     * @return void
     */
    public function notifyLister(Dispute $dispute)
    {
        $business = $dispute->business;

        foreach ($business->users as $businessUser) {
            $businessUser->user->notify(new DisputeCreatedNotification($dispute));
        }
    }

    /**
     * Update debtor status based on dispute resolution
     *
     * @param \App\Models\Dispute $dispute
     * @return void
     */
    protected function updateDebtorBasedOnResolution(Dispute $dispute)
    {
        $debtor = $dispute->debtor;

        if ($dispute->status === 'resolved_approved') {
            $debtor->status = 'pending';
            $debtor->listed_at = null;
        } elseif ($dispute->status === 'resolved_rejected') {
            $debtor->status = 'active';
            $debtor->listed_at = now();
        }

        $debtor->save();
    }
}
