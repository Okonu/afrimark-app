<?php

namespace App\Filament\Client\Widgets;

use App\Services\Business\OnboardingService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class OnboardingProgressWidget extends Widget
{
    protected static string $view = 'filament.client.widgets.onboarding-progress';
    protected int | string | array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $user = Auth::user();
        $business = $user->businesses()->first();

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
