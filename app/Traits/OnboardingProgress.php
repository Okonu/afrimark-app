<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use App\Services\Business\OnboardingService;

trait OnboardingProgress
{
    public function getOnboardingProgress(): array
    {
        $user = Auth::user();
        $business = $user ? $user->businesses()->first() : null;

        if (!$business) {
            return [
                'progress' => null,
                'nextStepUrl' => route('filament.client.auth.business-information'),
            ];
        }

        $onboardingService = app(OnboardingService::class);
        $progress = $onboardingService->getBusinessProgress($business);

        return [
            'progress' => $progress,
            'nextStepUrl' => $progress['next_step']
                ? $onboardingService->getNextStepUrl($progress['next_step'])
                : null,
        ];
    }
}
