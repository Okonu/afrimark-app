<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\Debtor;
use Illuminate\Support\Facades\Auth;

class BusinessSearchService
{
    /**
     * Search for businesses by name
     *
     * @param string $term
     * @return array
     */
    public function searchBusinesses(string $term)
    {
        $results = [
            'registered' => [],
            'unregistered_listed' => [],
            'no_report' => [],
        ];

        $registeredBusinesses = Business::where('name', 'like', "%{$term}%")
            ->orWhere('registration_number', 'like', "%{$term}%")
            ->get();

        foreach ($registeredBusinesses as $business) {
            $results['registered'][] = [
                'id' => $business->id,
                'name' => $business->name,
                'registration_number' => $business->registration_number,
                'report_available' => true,
                'is_registered' => true,
            ];
        }

        $unregisteredBusinesses = Debtor::whereNotIn('name', $registeredBusinesses->pluck('name')->toArray())
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('kra_pin', 'like', "%{$term}%");
            })
            ->where('status', 'active')
            ->get();

        $processedNames = [];

        foreach ($unregisteredBusinesses as $unregisteredBusiness) {
            if (in_array($unregisteredBusiness->name, $processedNames)) {
                continue;
            }

            $processedNames[] = $unregisteredBusiness->name;

            $results['unregistered_listed'][] = [
                'name' => $unregisteredBusiness->name,
                'kra_pin' => $unregisteredBusiness->kra_pin,
                'report_available' => true,
                'is_registered' => false,
            ];
        }

        if (empty($results['registered']) && empty($results['unregistered_listed'])) {
            $results['no_report'][] = [
                'search_term' => $term,
                'report_available' => false,
            ];
        }

        return $results;
    }

    /**
     * Get business report
     *
     * @param \App\Models\Business|null $business
     * @param string|null $businessName
     * @param string|null $kraPin
     * @return array|null
     */
    public function getBusinessReport($business = null, $businessName = null, $kraPin = null)
    {
        $currentUserBusinessId = Auth::check() ? Auth::user()->businesses()->first()?->id : null;

        if ($business && $business->id === $currentUserBusinessId) {
            return null;
        }

        if ($business) {
            return $this->getRegisteredBusinessReport($business);
        }

        if ($businessName || $kraPin) {
            return $this->getUnregisteredBusinessReport($businessName, $kraPin);
        }

        return null;
    }

    /**
     * Get report for a registered business
     *
     * @param \App\Models\Business $business
     * @return array
     */
    protected function getRegisteredBusinessReport(Business $business)
    {
        $totalOwed = Debtor::where('kra_pin', $business->registration_number)
            ->where('status', 'active')
            ->sum('amount_owed');

        $activeListings = Debtor::where('kra_pin', $business->registration_number)
            ->where('status', 'active')
            ->count();

        $resolvedListings = Debtor::where('kra_pin', $business->registration_number)
            ->where('status', 'paid')
            ->count();

        $creditScore = $this->calculateCreditScore($activeListings, $resolvedListings, $totalOwed);

        return [
            'name' => $business->name,
            'registration_number' => $business->registration_number,
            'is_registered' => true,
            'credit_score' => $creditScore,
            'total_owed' => $totalOwed,
            'active_listings' => $activeListings,
            'resolved_listings' => $resolvedListings,
        ];
    }

    /**
     * Get report for an unregistered but listed business
     *
     * @param string|null $businessName
     * @param string|null $kraPin
     * @return array
     */
    protected function getUnregisteredBusinessReport($businessName, $kraPin)
    {
        $query = Debtor::query();

        if ($businessName) {
            $query->where('name', $businessName);
        }

        if ($kraPin) {
            $query->orWhere('kra_pin', $kraPin);
        }

        $totalOwed = $query->clone()->where('status', 'active')->sum('amount_owed');

        $activeListings = $query->clone()->where('status', 'active')->count();

        $resolvedListings = $query->clone()->where('status', 'paid')->count();

        $firstRecord = $query->first();
        $name = $firstRecord ? $firstRecord->name : $businessName;
        $pin = $firstRecord ? $firstRecord->kra_pin : $kraPin;

        $creditScore = $this->calculateCreditScore($activeListings, $resolvedListings, $totalOwed);

        return [
            'name' => $name,
            'kra_pin' => $pin,
            'is_registered' => false,
            'credit_score' => $creditScore,
            'total_owed' => $totalOwed,
            'active_listings' => $activeListings,
            'resolved_listings' => $resolvedListings,
        ];
    }

    /**
     * Calculate a simple credit score
     *
     * @param int $activeListings
     * @param int $resolvedListings
     * @param float $totalOwed
     * @return int
     */
    protected function calculateCreditScore($activeListings, $resolvedListings, $totalOwed)
    {
        $score = 500;

        $score -= ($activeListings * 20);

        $score += ($resolvedListings * 15);

        $score -= min(200, $totalOwed / 1000);

        return max(0, min(850, $score));
    }
}
