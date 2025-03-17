<?php

namespace App\Traits;

use App\Services\CreditScoreService;

trait HasCreditScore
{
    /**
     * Get the credit score service instance
     *
     * @return CreditScoreService
     */
    protected function creditScoreService(): CreditScoreService
    {
        return app(CreditScoreService::class);
    }

    /**
     * Get the formatted credit score value
     *
     * @return string|null
     */
    public function getCreditScore(): ?string
    {
        $score = $this->getCreditScoreDetails();

        if (!$score || !isset($score['Composite Score'])) {
            return null;
        }

        return number_format($score['Composite Score'], 1);
    }

    /**
     * Get the risk description for the entity
     *
     * @return string|null
     */
    public function getRiskDescription(): ?string
    {
        $score = $this->getCreditScoreDetails();

        if (!$score || !isset($score['Risk Description'])) {
            return null;
        }

        return $score['Risk Description'];
    }

    /**
     * Get the risk class (numeric value)
     *
     * @return int|null
     */
    public function getRiskClass(): ?int
    {
        $score = $this->getCreditScoreDetails();

        if (!$score || !isset($score['Risk Class'])) {
            return null;
        }

        return (int) $score['Risk Class'];
    }

    /**
     * Get risk color based on risk class
     *
     * @return string
     */
    public function getRiskColor(): string
    {
        $riskClass = $this->getRiskClass();

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
     * Check if the entity has credit score data
     *
     * @return bool
     */
    public function hasCreditScore(): bool
    {
        $score = $this->getCreditScoreDetails();
        return $score !== null && isset($score['Composite Score']);
    }

    /**
     * Get additional details about the credit score
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getCreditScoreDetail(string $key, $default = null)
    {
        $score = $this->getCreditScoreDetails();

        if (!$score || !isset($score[$key])) {
            return $default;
        }

        return $score[$key];
    }
}
