<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Debtor;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class CreditScoreService
{
    protected string $apiUrl;
    protected string $sessionPrefix = 'credit_score_';

    public function __construct()
    {
        $this->apiUrl = config('afrimark.model_api_url', 'https://afri-model.afrimark.io');
    }

    /**
     * Get credit score for a specific business
     *
     * @param Business $business
     * @return array|null
     */
    public function getBusinessCreditScore(Business $business): ?array
    {
        $cacheKey = "{$this->sessionPrefix}business_{$business->registration_number}";

        if (Session::has($cacheKey)) {
            return Session::get($cacheKey);
        }

        // If all scores are already cached, use them instead of making another API call
        if (Session::has("{$this->sessionPrefix}all")) {
            $allScores = Session::get("{$this->sessionPrefix}all");

            foreach ($allScores as $score) {
                if ($score['kra_pin'] === $business->registration_number) {
                    Session::put($cacheKey, $score);
                    return $score;
                }
            }

            return null;
        }

        try {
            $creditScores = $this->fetchCreditScoresFromApi();

            if (empty($creditScores)) {
                return null;
            }

            // Cache all scores for future use
            Session::put("{$this->sessionPrefix}all", $creditScores);

            foreach ($creditScores as $score) {
                if ($score['kra_pin'] === $business->registration_number) {
                    Session::put($cacheKey, $score);
                    return $score;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Error fetching credit score for business {$business->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get credit score for a specific debtor
     *
     * @param Debtor $debtor
     * @return array|null
     */
    public function getDebtorCreditScore(Debtor $debtor): ?array
    {
        $cacheKey = "{$this->sessionPrefix}debtor_{$debtor->kra_pin}";

        if (Session::has($cacheKey)) {
            return Session::get($cacheKey);
        }

        // If all scores are already cached, use them instead of making another API call
        if (Session::has("{$this->sessionPrefix}all")) {
            $allScores = Session::get("{$this->sessionPrefix}all");

            foreach ($allScores as $score) {
                if ($score['kra_pin'] === $debtor->kra_pin) {
                    Session::put($cacheKey, $score);
                    return $score;
                }
            }

            return null;
        }

        try {
            $creditScores = $this->fetchCreditScoresFromApi();

            if (empty($creditScores)) {
                return null;
            }

            // Cache all scores for future use
            Session::put("{$this->sessionPrefix}all", $creditScores);

            foreach ($creditScores as $score) {
                if ($score['kra_pin'] === $debtor->kra_pin) {
                    Session::put($cacheKey, $score);
                    return $score;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Error fetching credit score for debtor {$debtor->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get all credit scores
     *
     * @return array
     */
    public function getAllCreditScores(): array
    {
        $cacheKey = "{$this->sessionPrefix}all";

        if (Session::has($cacheKey)) {
            return Session::get($cacheKey);
        }

        try {
            $scores = $this->fetchCreditScoresFromApi();

            Session::put($cacheKey, $scores);

            return $scores;
        } catch (\Exception $e) {
            Log::error("Error fetching all credit scores: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get credit score for a specific KRA PIN
     *
     * @param string $kraPin
     * @return array|null
     */
    public function getCreditScoreByKraPin(string $kraPin): ?array
    {
        $cacheKey = "{$this->sessionPrefix}kra_{$kraPin}";

        if (Session::has($cacheKey)) {
            return Session::get($cacheKey);
        }

        // If all scores are already cached, use them instead of making another API call
        if (Session::has("{$this->sessionPrefix}all")) {
            $allScores = Session::get("{$this->sessionPrefix}all");

            foreach ($allScores as $score) {
                if ($score['kra_pin'] === $kraPin) {
                    Session::put($cacheKey, $score);
                    return $score;
                }
            }

            return null;
        }

        try {
            $creditScores = $this->fetchCreditScoresFromApi();

            if (empty($creditScores)) {
                return null;
            }

            // Cache all scores for future use
            Session::put("{$this->sessionPrefix}all", $creditScores);

            foreach ($creditScores as $score) {
                if ($score['kra_pin'] === $kraPin) {
                    Session::put($cacheKey, $score);
                    return $score;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Error fetching credit score for KRA PIN {$kraPin}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch credit scores from the API
     *
     * @return array
     */
    protected function fetchCreditScoresFromApi(): array
    {
        $response = Http::timeout(10)
            ->get("{$this->apiUrl}/fetch-data/");

        if ($response->successful()) {
            return $response->json() ?? [];
        }

        Log::error("Failed to fetch credit scores from API: " . $response->status());
        return [];
    }

    /**
     * Fetch credit scores for a user's business (to be called after login)
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function fetchUserCreditScores($user): bool
    {
        $business = $user->businesses()->first();

        if (!$business) {
            return false;
        }

        try {
            // Clear previous session data
            $this->clearCreditScoreCache();

            $scores = $this->fetchCreditScoresFromApi();

            if (empty($scores)) {
                return false;
            }

            // Cache all scores
            Session::put("{$this->sessionPrefix}all", $scores);

            // Cache individual scores for quicker access
            foreach ($scores as $score) {
                if (isset($score['kra_pin'])) {
                    $kraPin = $score['kra_pin'];

                    // Store with multiple key patterns for backward compatibility
                    Session::put("{$this->sessionPrefix}kra_{$kraPin}", $score);
                    Session::put("{$this->sessionPrefix}business_{$kraPin}", $score);
                    Session::put("{$this->sessionPrefix}debtor_{$kraPin}", $score);

                    // Store current business score if matching
                    if ($kraPin === $business->registration_number) {
                        Session::put("{$this->sessionPrefix}current_business", $score);
                    }
                }
            }

            Session::put("{$this->sessionPrefix}fetched", true);

            Log::info("Credit scores fetched and cached for user {$user->id}");
            return true;
        } catch (\Exception $e) {
            Log::error("Error fetching credit scores for user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Clear all credit score cache data
     *
     * @return void
     */
    public function clearCreditScoreCache(): void
    {
        foreach (Session::all() as $key => $value) {
            if (strpos($key, $this->sessionPrefix) === 0) {
                Session::forget($key);
            }
        }
    }

    /**
     * Get formatted credit score value for a business
     *
     * @param Business $business
     * @return string|null
     */
    public function getFormattedCreditScore(Business $business): ?string
    {
        $score = $this->getBusinessCreditScore($business);

        if (!$score || !isset($score['Composite Score'])) {
            return null;
        }

        return number_format($score['Composite Score'], 1);
    }

    /**
     * Get risk description for a business
     *
     * @param Business $business
     * @return string|null
     */
    public function getRiskDescription(Business $business): ?string
    {
        $score = $this->getBusinessCreditScore($business);

        if (!$score || !isset($score['Risk Description'])) {
            return null;
        }

        return $score['Risk Description'];
    }

    /**
     * Get risk class (numeric value) for a business
     *
     * @param Business $business
     * @return int|null
     */
    public function getRiskClass(Business $business): ?int
    {
        $score = $this->getBusinessCreditScore($business);

        if (!$score || !isset($score['Risk Class'])) {
            return null;
        }

        return (int) $score['Risk Class'];
    }

    /**
     * Get risk color based on risk class
     *
     * @param Business $business
     * @return string
     */
    public function getRiskColor(Business $business): string
    {
        $riskClass = $this->getRiskClass($business);

        return match ($riskClass) {
            1 => 'success',    // Low risk
            2 => 'info',       // Low to Medium risk
            3 => 'warning',    // Medium risk
            4 => 'amber',      // Medium to High risk
            5 => 'danger',     // High risk
            default => 'gray', // Unknown or no data
        };
    }
}
