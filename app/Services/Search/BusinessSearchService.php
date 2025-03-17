<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\Debtor;
use App\Services\CreditScoreService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BusinessSearchService
{
    /**
     * @var CreditScoreService
     */
    protected $creditScoreService;

    /**
     * Constructor
     *
     * @param CreditScoreService $creditScoreService
     */
    public function __construct(CreditScoreService $creditScoreService)
    {
        $this->creditScoreService = $creditScoreService;
    }

    /**
     * Search for businesses by name or registration number
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

        // Sanitize the search term to prevent SQL injection
        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';

        try {
            // Find registered businesses
            $registeredBusinesses = Business::where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('registration_number', 'like', $searchTerm);
            })
                ->limit(20)
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

            // Find unregistered businesses that are listed as debtors
            $unregisteredBusinesses = Debtor::whereNotIn('name', $registeredBusinesses->pluck('name')->toArray())
                ->where(function ($query) use ($searchTerm) {
                    $query->where('name', 'like', $searchTerm)
                        ->orWhere('kra_pin', 'like', $searchTerm);
                })
                ->where('status', 'active')
                ->distinct('name')
                ->limit(20)
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

            // If no results found, add the search term to the no_report section
            if (empty($results['registered']) && empty($results['unregistered_listed'])) {
                $results['no_report'][] = [
                    'search_term' => $term,
                    'report_available' => false,
                ];
            }
        } catch (\Exception $e) {
            Log::error('Error in business search: ' . $e->getMessage());

            // Return error in results
            $results['no_report'][] = [
                'search_term' => $term,
                'report_available' => false,
                'error' => 'An error occurred while searching. Please try again.'
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
        // Don't allow users to view their own business credit report in detail
        $currentUserBusinessId = Auth::check() ? Auth::user()->businesses()->first()?->id : null;

        if ($business && $currentUserBusinessId && $business->id === $currentUserBusinessId) {
            return [
                'error' => 'You cannot view your own business credit report from this section. Please visit your business profile.'
            ];
        }

        try {
            if ($business) {
                return $this->getRegisteredBusinessReport($business);
            }

            if ($businessName || $kraPin) {
                return $this->getUnregisteredBusinessReport($businessName, $kraPin);
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Error generating business report: ' . $e->getMessage());
            return [
                'error' => 'An error occurred while generating the report. Please try again.'
            ];
        }
    }

    /**
     * Get report for a registered business
     *
     * @param \App\Models\Business $business
     * @return array
     */
    protected function getRegisteredBusinessReport(Business $business)
    {
        // Get total amount owed across all active listings
        $totalOwed = Debtor::where('kra_pin', $business->registration_number)
            ->where('status', 'active')
            ->sum('amount_owed');

        // Count active listings
        $activeListings = Debtor::where('kra_pin', $business->registration_number)
            ->where('status', 'active')
            ->count();

        // Count resolved listings
        $resolvedListings = Debtor::where('kra_pin', $business->registration_number)
            ->where('status', 'paid')
            ->count();

        // Get credit score details directly from the business model which uses the trait
        $creditScore = null;
        $riskDescription = null;
        $riskClass = null;
        $apiScoreDetails = null;
        $hasApiScore = false;

        if ($business->hasCreditScore()) {
            $creditScore = $business->getCreditScore();
            $riskDescription = $business->getRiskDescription();
            $riskClass = $business->getRiskClass();
            $apiScoreDetails = $business->getCreditScoreDetails();
            $hasApiScore = true;
        }

        return [
            'name' => $business->name,
            'registration_number' => $business->registration_number,
            'is_registered' => true,
            'credit_score' => $creditScore,
            'total_owed' => $totalOwed,
            'active_listings' => $activeListings,
            'resolved_listings' => $resolvedListings,
            'has_api_score' => $hasApiScore,
            'risk_description' => $riskDescription,
            'risk_class' => $riskClass,
            'api_score_details' => $apiScoreDetails,
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

        // Get total amount owed across all active listings
        $totalOwed = $query->clone()->where('status', 'active')->sum('amount_owed');

        // Count active listings
        $activeListings = $query->clone()->where('status', 'active')->count();

        // Count resolved listings
        $resolvedListings = $query->clone()->where('status', 'paid')->count();

        // Get first record for name and KRA PIN
        $firstRecord = $query->first();
        $name = $firstRecord ? $firstRecord->name : $businessName;
        $pin = $firstRecord ? $firstRecord->kra_pin : $kraPin;

        // Get credit score from API if KRA PIN is available
        $creditScore = null;
        $riskDescription = null;
        $riskClass = null;
        $apiScoreDetails = null;
        $hasApiScore = false;

        if ($pin) {
            // Use the CreditScoreService to get the credit score by KRA PIN
            $apiScoreDetails = $this->creditScoreService->getCreditScoreByKraPin($pin);

            if ($apiScoreDetails && isset($apiScoreDetails['Composite Score'])) {
                $creditScore = $apiScoreDetails['Composite Score'];
                $riskDescription = $apiScoreDetails['Risk Description'] ?? null;
                $riskClass = $apiScoreDetails['Risk Class'] ?? null;
                $hasApiScore = true;
            }
        }

        // If the debtor object has the DebtorCreditScore trait, use that
        if (!$hasApiScore && $firstRecord && method_exists($firstRecord, 'hasCreditScore') && $firstRecord->hasCreditScore()) {
            $creditScore = $firstRecord->getCreditScore();
            $riskDescription = $firstRecord->getRiskDescription();
            $riskClass = $firstRecord->getRiskClass();
            $apiScoreDetails = $firstRecord->getCreditScoreDetails();
            $hasApiScore = true;
        }

        return [
            'name' => $name,
            'kra_pin' => $pin,
            'is_registered' => false,
            'credit_score' => $creditScore,
            'total_owed' => $totalOwed,
            'active_listings' => $activeListings,
            'resolved_listings' => $resolvedListings,
            'has_api_score' => $hasApiScore,
            'risk_description' => $riskDescription,
            'risk_class' => $riskClass,
            'api_score_details' => $apiScoreDetails,
        ];
    }
}
