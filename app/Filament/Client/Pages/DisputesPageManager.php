<?php

namespace App\Filament\Client\Pages;

use App\Models\Debtor;
use App\Models\Dispute;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DisputesPageManager extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Disputes Management';
    protected static ?int $navigationSort = 4;
    protected static ?string $title = 'Disputes Management';

    protected static string $view = 'filament.client.pages.disputes-page-manager';

    public $activeTab = 'disputable-listings';

    public $disputableListings = [];
    public $myDisputes = [];
    public $disputesToRespond = [];

    public function mount(): void
    {
        $this->activeTab = request()->query('tab', 'disputable-listings');
        $this->loadData();
    }

    /**
     * Load all required data for the current tab
     */
    public function loadData(): void
    {
        $business = Auth::user()->businesses()->first();

        if (!$business) {
            Log::warning("No business found for user", ['user_id' => Auth::id()]);
            return;
        }

        Log::info("Loading dispute data for business: " . $business->name);

        $this->disputableListings = Debtor::query()
            ->where(function($query) use ($business) {
                $query->where('kra_pin', $business->registration_number)
                    ->orWhereRaw('LOWER(email) = ?', [strtolower($business->email)]);
            })
            ->where('status', 'pending')
            ->where('listing_goes_live_at', '>', now())
            ->whereDoesntHave('disputes')
            ->with('business')
            ->get();

        Log::info("Loaded " . $this->disputableListings->count() . " disputable listings");

        if ($this->disputableListings->count() > 0) {
            Log::info("Found disputable listings: " . $this->disputableListings->pluck('id')->implode(', '));
        }

        $this->myDisputes = Dispute::query()
            ->whereHas('debtor', function ($query) use ($business) {
                $query->where(function($q) use ($business) {
                    $q->where('kra_pin', $business->registration_number)
                        ->orWhereRaw('LOWER(email) = ?', [strtolower($business->email)]);
                });
            })
            ->with(['debtor', 'debtor.business'])
            ->get();

        Log::info("Loaded " . $this->myDisputes->count() . " disputes created by this business");

        $this->disputesToRespond = Dispute::query()
            ->whereHas('debtor', function ($query) use ($business) {
                $query->where('business_id', $business->id);
            })
            ->where('status', 'pending')
            ->with(['debtor'])
            ->get();

        Log::info("Loaded " . $this->disputesToRespond->count() . " disputes requiring a response");
    }

    /**
     * Get URL for creating a dispute
     */
    public function getCreateDisputeUrl($debtorId)
    {
        return route('filament.client.resources.disputes.create', ['debtor' => $debtorId]);
    }

    /**
     * Get URL for viewing a dispute
     */
    public function getViewDisputeUrl($disputeId)
    {
        return route('filament.client.resources.disputes.view', ['record' => $disputeId]);
    }

    /**
     * Get URL for responding to a dispute
     */
    public function getRespondDisputeUrl($disputeId)
    {
        return route('filament.client.resources.disputes.respond', ['record' => $disputeId]);
    }

    /**
     * Format dispute type for display
     */
    public function formatDisputeType($type)
    {
        return match($type) {
            'wrong_amount' => 'Wrong Amount',
            'no_debt' => 'No Debt Exists',
            'already_paid' => 'Already Paid',
            'wrong_business' => 'Wrong Business Listed',
            'other' => 'Other',
            default => $type,
        };
    }

    /**
     * Get status color for badges
     */
    public function getStatusColor($status)
    {
        return match($status) {
            'pending' => 'warning',
            'under_review' => 'primary',
            'resolved_approved' => 'success',
            'resolved_rejected' => 'danger',
            default => 'secondary',
        };
    }
}
