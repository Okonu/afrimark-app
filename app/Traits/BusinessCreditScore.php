<?php

namespace App\Traits;

trait BusinessCreditScore
{
    use HasCreditScore;

    /**
     * Get all credit score details as an array
     *
     * @return array|null
     */
    public function getCreditScoreDetails(): ?array
    {
        return $this->creditScoreService()->getBusinessCreditScore($this);
    }
}
