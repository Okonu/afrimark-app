<?php

namespace App\Traits;

trait DebtorCreditScore
{
    use HasCreditScore;

    /**
     * Get all credit score details as an array
     *
     * @return array|null
     */
    public function getCreditScoreDetails(): ?array
    {
        return $this->creditScoreService()->getDebtorCreditScore($this);
    }
}
