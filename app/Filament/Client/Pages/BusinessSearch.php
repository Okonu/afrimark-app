<?php

namespace App\Filament\Client\Pages;

use App\Models\Business;
use App\Services\Search\BusinessSearchService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;

class BusinessSearch extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?string $navigationLabel = 'Business Search';
    protected static ?string $title = 'Search for Businesses';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.client.pages.business-search';

    public ?array $data = [];
    public $searchResults = null;
    public $selectedBusiness = null;
    public $businessReport = null;
    public $searchPerformed = false;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('search_term')
                    ->label('Search by Business Name or KRA PIN')
                    ->placeholder('Enter at least 3 characters to search')
                    ->helperText('Search for any registered or unregistered business in our network')
                    ->required()
                    ->minLength(3)
                    ->autofocus(),
            ])
            ->statePath('data');
    }

    public function search(BusinessSearchService $searchService): void
    {
        $this->validate([
            'data.search_term' => 'required|min:3',
        ]);

        $term = $this->data['search_term'];

        $this->searchResults = $searchService->searchBusinesses($term);
        $this->selectedBusiness = null;
        $this->businessReport = null;
        $this->searchPerformed = true;
    }

    public function viewBusinessReport($type, $identifier, BusinessSearchService $searchService): void
    {
        try {
            if ($type === 'registered') {
                $business = Business::find($identifier);

                if (!$business) {
                    $this->notify('error', 'Business not found');
                    return;
                }

                $this->businessReport = $searchService->getBusinessReport($business);
                $this->selectedBusiness = [
                    'id' => $business->id,
                    'name' => $business->name,
                    'registration_number' => $business->registration_number,
                    'is_registered' => true,
                ];
            } else {
                $businessName = is_array($identifier) && isset($identifier['name']) ? $identifier['name'] : null;
                $kraPin = is_array($identifier) && isset($identifier['kra_pin']) ? $identifier['kra_pin'] : null;

                if (!$businessName && !$kraPin) {
                    $this->notify('error', 'Business information is missing');
                    return;
                }

                $this->businessReport = $searchService->getBusinessReport(
                    null,
                    $businessName,
                    $kraPin
                );

                $this->selectedBusiness = [
                    'name' => $businessName ?? 'Unknown Business',
                    'kra_pin' => $kraPin ?? 'Unknown',
                    'is_registered' => false,
                ];
            }

            // Ensure all required fields exist in the business report
            if ($this->businessReport && !isset($this->businessReport['error'])) {
                // Make sure key fields are set with defaults if missing
                $this->businessReport = array_merge([
                    'name' => $this->selectedBusiness['name'] ?? 'Unknown Business',
                    'is_registered' => $type === 'registered',
                    'registration_number' => $this->selectedBusiness['registration_number'] ?? null,
                    'kra_pin' => $this->selectedBusiness['kra_pin'] ?? null,
                    'credit_score' => null,
                    'total_owed' => 0,
                    'active_listings' => 0,
                    'resolved_listings' => 0,
                    'has_api_score' => false,
                    'risk_description' => null,
                    'risk_class' => null,
                    'risk_color' => 'gray',
                    'api_score_details' => [],
                ], $this->businessReport);

                // Log the report data for debugging
                Log::debug('Business Report Data:', [
                    'has_credit_score' => isset($this->businessReport['credit_score']),
                    'credit_score' => $this->businessReport['credit_score'] ?? null,
                    'has_risk_class' => isset($this->businessReport['risk_class']),
                    'risk_class' => $this->businessReport['risk_class'] ?? null,
                    'has_api_details' => isset($this->businessReport['api_score_details']),
                    'api_details_count' => isset($this->businessReport['api_score_details']) ?
                        (is_array($this->businessReport['api_score_details']) ? count($this->businessReport['api_score_details']) : 'not array') : 0,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch business report: ' . $e->getMessage(), ['exception' => $e]);
            $this->notify('error', 'Failed to fetch business report: ' . $e->getMessage());
            $this->businessReport = null;
        }
    }

    /**
     * Get CSS color class for credit score badge
     */
    public function getCreditScoreBadgeClass($scoreData): string
    {
        // If we have risk class
        if (isset($scoreData['risk_class']) && $scoreData['risk_class']) {
            return match((int)$scoreData['risk_class']) {
                1 => 'bg-green-100 text-green-800',
                2 => 'bg-green-100 text-green-800',
                3 => 'bg-yellow-100 text-yellow-800',
                4 => 'bg-yellow-100 text-yellow-800',
                5 => 'bg-red-100 text-red-800',
                default => 'bg-gray-100 text-gray-800',
            };
        }

        // Otherwise, calculate based on score
        $score = isset($scoreData['credit_score']) ? $scoreData['credit_score'] : null;
        if ($score === null) {
            return 'bg-gray-100 text-gray-800';
        }

        return match(true) {
            $score >= 80 => 'bg-green-100 text-green-800',
            $score >= 70 => 'bg-green-100 text-green-800',
            $score >= 60 => 'bg-yellow-100 text-yellow-800',
            $score >= 40 => 'bg-yellow-100 text-yellow-800',
            default => 'bg-red-100 text-red-800',
        };
    }

    /**
     * Get a description for the credit score
     */
    public function getCreditScoreDescription($scoreData): string
    {
        // Use the risk description if available
        if (isset($scoreData['risk_description']) && $scoreData['risk_description']) {
            return $scoreData['risk_description'] . ' Risk';
        }

        // Otherwise, calculate based on score
        $score = isset($scoreData['credit_score']) ? $scoreData['credit_score'] : null;
        if ($score === null) {
            return 'No Score Available';
        }

        return match(true) {
            $score >= 80 => 'Excellent',
            $score >= 70 => 'Good',
            $score >= 60 => 'Fair',
            $score >= 40 => 'Poor',
            default => 'Very Poor',
        };
    }

    /**
     * Convert risk color to CSS class
     */
    public function getRiskColorClass($riskColor): string
    {
        return match ($riskColor) {
            'success' => 'green',
            'info' => 'blue',
            'warning' => 'yellow',
            'amber' => 'orange',
            'danger' => 'red',
            default => 'gray',
        };
    }

    /**
     * Get CSS color class for credit score circle
     */
    public function getCreditScoreColorClass($scoreData): string
    {
        // Check if we have a risk class
        if (isset($scoreData['risk_class'])) {
            return match($scoreData['risk_class']) {
                1 => 'bg-green-500',  // Low risk
                2 => 'bg-green-400',  // Low to Medium risk
                3 => 'bg-yellow-500', // Medium risk
                4 => 'bg-yellow-600', // Medium to High risk
                5 => 'bg-red-500',    // High risk
                default => 'bg-gray-500',
            };
        }

        // Otherwise, calculate based on score
        $score = $scoreData['credit_score'] ?? null;
        if ($score === null) {
            return 'bg-gray-200';
        }

        return match(true) {
            $score >= 80 => 'bg-green-500',
            $score >= 70 => 'bg-green-400',
            $score >= 60 => 'bg-yellow-400',
            $score >= 40 => 'bg-yellow-600',
            default => 'bg-red-500',
        };
    }

    public function clearSearch(): void
    {
        $this->searchResults = null;
        $this->selectedBusiness = null;
        $this->businessReport = null;
        $this->searchPerformed = false;
        $this->data = [];
        $this->form->fill();
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('search')
                ->label('Search')
                ->submit('search')
                ->color('primary')
                ->icon('heroicon-o-magnifying-glass'),

            Forms\Components\Actions\Action::make('clear')
                ->label('Clear')
                ->action('clearSearch')
                ->color('secondary')
                ->icon('heroicon-o-x-mark')
                ->visible(fn() => $this->searchPerformed),
        ];
    }
}
