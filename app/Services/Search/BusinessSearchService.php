<?php

namespace App\Services\Search;

use App\Models\Business;
use App\Models\Debtor;
use App\Models\Invoice;
use App\Traits\BusinessListingsCalculator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class BusinessSearchService
{
    use BusinessListingsCalculator;

    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * @var string
     */
    protected $sessionPrefix = 'business_search_';

    /**
     * API data cache time in minutes
     */
    protected $cacheTime = 60;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->apiUrl = config('afrimark.model_api_url', 'https://afri-model.afrimark.io');
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

        $searchTerm = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';

        try {
            // Find registered businesses
            $registeredBusinesses = Business::where(function ($query) use ($searchTerm) {
                $query->where('name', 'like', $searchTerm)
                    ->orWhere('registration_number', 'like', $searchTerm);
            })
                ->limit(20)
                ->get();

            // Get API data
            $apiData = $this->getApiData();

            foreach ($registeredBusinesses as $business) {
                // Check if we have API data for this business
                $hasApiData = false;
                foreach ($apiData as $item) {
                    if (strtoupper($item['kra_pin']) === strtoupper($business->registration_number) ||
                        stripos($item['name'], $business->name) !== false ||
                        stripos($business->name, $item['name']) !== false) {
                        $hasApiData = true;
                        break;
                    }
                }

                $results['registered'][] = [
                    'id' => $business->id,
                    'name' => $business->name,
                    'registration_number' => $business->registration_number,
                    'report_available' => true,
                    'is_registered' => true,
                    'has_api_data' => $hasApiData,
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

                // Check if we have API data for this business
                $hasApiData = false;
                foreach ($apiData as $item) {
                    if ((!empty($unregisteredBusiness->kra_pin) && strtoupper($item['kra_pin']) === strtoupper($unregisteredBusiness->kra_pin)) ||
                        stripos($item['name'], $unregisteredBusiness->name) !== false ||
                        stripos($unregisteredBusiness->name, $item['name']) !== false) {
                        $hasApiData = true;
                        break;
                    }
                }

                $results['unregistered_listed'][] = [
                    'name' => $unregisteredBusiness->name,
                    'kra_pin' => $unregisteredBusiness->kra_pin,
                    'report_available' => true,
                    'is_registered' => false,
                    'has_api_data' => $hasApiData,
                ];
            }

            // Do an additional search in the API data for exact term matches
            if (empty($results['registered']) && empty($results['unregistered_listed'])) {
                foreach ($apiData as $item) {
                    if (stripos($item['name'], $term) !== false ||
                        (!empty($item['kra_pin']) && stripos($item['kra_pin'], $term) !== false)) {

                        // Check if this business already exists in our database
                        $existingBusiness = Business::where('registration_number', $item['kra_pin'])->first();

                        if ($existingBusiness) {
                            $results['registered'][] = [
                                'id' => $existingBusiness->id,
                                'name' => $existingBusiness->name,
                                'registration_number' => $existingBusiness->registration_number,
                                'report_available' => true,
                                'is_registered' => true,
                                'has_api_data' => true,
                            ];
                        } else {
                            $results['unregistered_listed'][] = [
                                'name' => $item['name'],
                                'kra_pin' => $item['kra_pin'],
                                'report_available' => true,
                                'is_registered' => false,
                                'has_api_data' => true,
                            ];
                        }
                    }
                }
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
     * Fetch and cache API data
     *
     * @return array
     */
    protected function getApiData(): array
    {
        $cacheKey = "{$this->sessionPrefix}api_data";

        // Check if we have cached data that's still valid
        if (Session::has($cacheKey)) {
            $cachedData = Session::get($cacheKey);
            if (isset($cachedData['expires_at']) && $cachedData['expires_at'] > now()->timestamp) {
                return $cachedData['data'] ?? [];
            }
        }

        // Fetch fresh data
        try {
            Log::info("Fetching credit scores from API: {$this->apiUrl}/fetch-data/");

            $response = Http::timeout(30)
                ->get("{$this->apiUrl}/fetch-data/");

            if ($response->successful()) {
                $data = $response->json() ?? [];

                // Cache the data
                Session::put($cacheKey, [
                    'data' => $data,
                    'expires_at' => now()->addMinutes($this->cacheTime)->timestamp,
                    'fetched_at' => now()->timestamp
                ]);

                Log::info("Successfully fetched " . count($data) . " items from API");
                return $data;
            }

            Log::error("Failed to fetch data from API: " . $response->status());
            return [];
        } catch (\Exception $e) {
            Log::error("Exception when fetching API data: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Find item in API data by KRA PIN
     *
     * @param string $kraPin
     * @return array|null
     */
    protected function findByKraPin(string $kraPin): ?array
    {
        $apiData = $this->getApiData();

        foreach ($apiData as $item) {
            if (isset($item['kra_pin']) && strtoupper($item['kra_pin']) === strtoupper($kraPin)) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Find item in API data by name
     *
     * @param string $name
     * @return array|null
     */
    protected function findByName(string $name): ?array
    {
        $apiData = $this->getApiData();

        foreach ($apiData as $item) {
            if (isset($item['name']) &&
                (stripos($item['name'], $name) !== false ||
                    stripos($name, $item['name']) !== false)) {
                return $item;
            }
        }

        return null;
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
            Log::error('Error generating business report: ' . $e->getMessage(), ['exception' => $e]);
            return [
                'error' => 'An error occurred while generating the report. Please try again.'
            ];
        }
    }

    /**
     * Get total amount owed for a business by KRA PIN
     *
     * @param string $kraPin
     * @return float
     */
    protected function getTotalAmountOwed(string $kraPin): float
    {
        try {
            // Get the total amount from unpaid invoices
            return Invoice::whereHas('debtor', function ($query) use ($kraPin) {
                $query->where('kra_pin', $kraPin)
                    ->where('status', 'active');
            })->sum('due_amount');
        } catch (\Exception $e) {
            Log::error("Error getting total amount owed: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count active listings for a business
     *
     * @param string $kraPin
     * @return int
     */
    protected function countActiveListings(string $kraPin): int
    {
        try {
            return Debtor::where('kra_pin', $kraPin)
                ->where('status', 'active')
                ->count();
        } catch (\Exception $e) {
            Log::error("Error counting active listings: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Count resolved listings for a business
     *
     * @param string $kraPin
     * @return int
     */
    protected function countResolvedListings(string $kraPin): int
    {
        try {
            return Debtor::where('kra_pin', $kraPin)
                ->where('status', 'paid')
                ->count();
        } catch (\Exception $e) {
            Log::error("Error counting resolved listings: " . $e->getMessage());
            return 0;
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
        Log::info("Generating report for registered business: {$business->name} (ID: {$business->id}) with KRA PIN: {$business->registration_number}");

        $totalOwed = 0;
        $activeListings = 0;
        $resolvedListings = 0;

        // Get the listings metrics
        $listingsCounts = [
            'negative' => 0,
            'positive' => 0,
            'total' => 0
        ];

        $invoiceCounts = [
            'negative' => 0,
            'positive' => 0,
            'total' => 0
        ];

        $invoiceAmounts = [
            'negative' => 0,
            'positive' => 0,
            'total' => 0
        ];

        if ($business->registration_number) {
            // Use API data for financial metrics if available
            $apiData = $this->findByKraPin($business->registration_number);
            if ($apiData && isset($apiData['Total Amount Owed'])) {
                $totalOwed = $apiData['Total Amount Owed'];
            } else {
                // Fall back to database calculation
                $totalOwed = $this->getTotalAmountOwed($business->registration_number);
            }

            // Get listing counts from database
            $activeListings = $this->countActiveListings($business->registration_number);
            $resolvedListings = $this->countResolvedListings($business->registration_number);

            // Get the listings metrics using the BusinessListingsCalculator trait
            $listingsCounts = $this->getBusinessListingsCounts($business->registration_number);
            $invoiceCounts = $this->getBusinessInvoiceCounts($business->registration_number);
            $invoiceAmounts = $this->getBusinessInvoiceAmounts($business->registration_number);
        }

        // Get API data for this business
        $apiData = null;
        $hasApiData = false;

        // Try to find by KRA PIN first
        if ($business->registration_number) {
            $apiData = $this->findByKraPin($business->registration_number);
            if ($apiData) {
                Log::info("Found API data for KRA PIN: {$business->registration_number}");
            }
        }

        // If not found by KRA PIN, try by name
        if (!$apiData) {
            $apiData = $this->findByName($business->name);
            if ($apiData) {
                Log::info("Found API data by name match: {$business->name}");
            } else {
                Log::info("No API data found for business: {$business->name}");
            }
        }

        $creditScore = null;
        $riskDescription = null;
        $riskClass = null;

        if ($apiData) {
            $hasApiData = true;
            $creditScore = isset($apiData['Composite Score']) ? $apiData['Composite Score'] : null;
            $riskDescription = isset($apiData['Risk Description']) ? $apiData['Risk Description'] : null;
            $riskClass = isset($apiData['Risk Class']) ? $apiData['Risk Class'] : null;

            // If we have API data for Total Amount Owed, use it
            if (isset($apiData['Total Amount Owed'])) {
                $totalOwed = $apiData['Total Amount Owed'];
            }
        }

        // Get color for risk level
        $riskColor = $this->getRiskColorFromClass($riskClass);

        return [
            'name' => $business->name,
            'registration_number' => $business->registration_number,
            'is_registered' => true,
            'credit_score' => $creditScore,
            'total_owed' => $totalOwed,
            'active_listings' => $activeListings,
            'resolved_listings' => $resolvedListings,
            'has_api_score' => $hasApiData,
            'risk_description' => $riskDescription,
            'risk_class' => $riskClass,
            'risk_color' => $riskColor,
            'api_score_details' => $apiData,
            // Listings metrics
            'negative_listings' => $listingsCounts['negative'],
            'positive_listings' => $listingsCounts['positive'],
            'total_listings' => $listingsCounts['total'],
            'negative_invoices' => $invoiceCounts['negative'],
            'positive_invoices' => $invoiceCounts['positive'],
            'total_invoices' => $invoiceCounts['total'],
            'negative_amount' => $invoiceAmounts['negative'],
            'positive_amount' => $invoiceAmounts['positive'],
            'total_amount' => $invoiceAmounts['total'],
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
        Log::info("Generating report for unregistered business: Name: {$businessName}, KRA PIN: {$kraPin}");

        // Find the debtor records
        $query = Debtor::query();

        if ($businessName) {
            $query->where('name', $businessName);
        }

        if ($kraPin) {
            $query->orWhere('kra_pin', $kraPin);
        }

        // Get first record for name and KRA PIN
        $firstRecord = $query->first();
        $name = $firstRecord ? $firstRecord->name : $businessName;
        $pin = $firstRecord ? $firstRecord->kra_pin : $kraPin;

        $totalOwed = 0;
        $activeListings = 0;
        $resolvedListings = 0;

        // Get the listings metrics
        $listingsCounts = [
            'negative' => 0,
            'positive' => 0,
            'total' => 0
        ];

        $invoiceCounts = [
            'negative' => 0,
            'positive' => 0,
            'total' => 0
        ];

        $invoiceAmounts = [
            'negative' => 0,
            'positive' => 0,
            'total' => 0
        ];

        if ($pin) {
            // Try to get data from API first
            $apiData = $this->findByKraPin($pin);
            if ($apiData && isset($apiData['Total Amount Owed'])) {
                $totalOwed = $apiData['Total Amount Owed'];
            } else {
                // Fall back to database calculation
                $totalOwed = $this->getTotalAmountOwed($pin);
            }

            // Get listing counts
            $activeListings = $this->countActiveListings($pin);
            $resolvedListings = $this->countResolvedListings($pin);

            // Get the listings metrics using the BusinessListingsCalculator trait
            $listingsCounts = $this->getBusinessListingsCounts($pin);
            $invoiceCounts = $this->getBusinessInvoiceCounts($pin);
            $invoiceAmounts = $this->getBusinessInvoiceAmounts($pin);
        }

        // Get API data
        $apiData = null;
        $hasApiData = false;

        // Try to find by KRA PIN first
        if ($pin) {
            $apiData = $this->findByKraPin($pin);
            if ($apiData) {
                Log::info("Found API data for KRA PIN: {$pin}");
            }
        }

        // If not found by KRA PIN, try by name
        if (!$apiData && $name) {
            $apiData = $this->findByName($name);
            if ($apiData) {
                Log::info("Found API data by name match: {$name}");
            } else {
                Log::info("No API data found for business: {$name}");
            }
        }

        $creditScore = null;
        $riskDescription = null;
        $riskClass = null;

        if ($apiData) {
            $hasApiData = true;
            $creditScore = isset($apiData['Composite Score']) ? $apiData['Composite Score'] : null;
            $riskDescription = isset($apiData['Risk Description']) ? $apiData['Risk Description'] : null;
            $riskClass = isset($apiData['Risk Class']) ? $apiData['Risk Class'] : null;

            // If we have API data for Total Amount Owed, use it
            if (isset($apiData['Total Amount Owed'])) {
                $totalOwed = $apiData['Total Amount Owed'];
            }
        }

        // Get color for risk level
        $riskColor = $this->getRiskColorFromClass($riskClass);

        return [
            'name' => $name ?? ($apiData['name'] ?? 'Unknown Business'),
            'kra_pin' => $pin ?? ($apiData['kra_pin'] ?? null),
            'is_registered' => false,
            'credit_score' => $creditScore,
            'total_owed' => $totalOwed,
            'active_listings' => $activeListings,
            'resolved_listings' => $resolvedListings,
            'has_api_score' => $hasApiData,
            'risk_description' => $riskDescription,
            'risk_class' => $riskClass,
            'risk_color' => $riskColor,
            'api_score_details' => $apiData,
            // Listings metrics
            'negative_listings' => $listingsCounts['negative'],
            'positive_listings' => $listingsCounts['positive'],
            'total_listings' => $listingsCounts['total'],
            'negative_invoices' => $invoiceCounts['negative'],
            'positive_invoices' => $invoiceCounts['positive'],
            'total_invoices' => $invoiceCounts['total'],
            'negative_amount' => $invoiceAmounts['negative'],
            'positive_amount' => $invoiceAmounts['positive'],
            'total_amount' => $invoiceAmounts['total'],
        ];
    }

    /**
     * Get risk color name from risk class
     *
     * @param int|null $riskClass
     * @return string
     */
    protected function getRiskColorFromClass(?int $riskClass): string
    {
        return match ($riskClass) {
            1 => 'success',    // Low risk
            2 => 'info',       // Low to Medium risk
            3 => 'warning',    // Medium risk
            4 => 'amber',      // Medium to High risk
            5 => 'danger',     // High risk
            default => 'gray', // Unknown or no data
        };
    }

    /**
     * Clear the cache
     */
    public function clearCache(): void
    {
        foreach (Session::all() as $key => $value) {
            if (strpos($key, $this->sessionPrefix) === 0) {
                Session::forget($key);
            }
        }
    }
}
