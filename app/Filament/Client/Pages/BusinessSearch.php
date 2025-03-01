<?php

namespace App\Filament\Client\Pages;

use App\Services\Search\BusinessSearchService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BusinessSearch extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';
    protected static ?string $navigationLabel = 'Search';
    protected static ?string $title = 'Business Search';
    protected static ?int $navigationSort = 5;

    protected static string $view = 'filament.client.pages.business-search';

    public ?array $data = [];
    public $searchResults = null;
    public $selectedBusiness = null;
    public $businessReport = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Search Businesses')
                    ->schema([
                        Forms\Components\TextInput::make('search_term')
                            ->label('Business Name or KRA PIN')
                            ->required()
                            ->minLength(3),
                    ]),
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
    }

    public function viewBusinessReport($type, $identifier, BusinessSearchService $searchService): void
    {
        if ($type === 'registered') {
            $business = \App\Models\Business::find($identifier);
            $this->businessReport = $searchService->getBusinessReport($business);
            $this->selectedBusiness = [
                'name' => $business->name,
                'is_registered' => true,
            ];
        } else {
            $this->businessReport = $searchService->getBusinessReport(
                null,
                $identifier['name'] ?? null,
                $identifier['kra_pin'] ?? null
            );
            $this->selectedBusiness = [
                'name' => $identifier['name'] ?? 'Unknown Business',
                'is_registered' => false,
            ];
        }
    }

    public function render(): View
    {
        return view('filament.client.pages.business-search', [
            'searchResults' => $this->searchResults,
            'selectedBusiness' => $this->selectedBusiness,
            'businessReport' => $this->businessReport,
        ]);
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('search')
                ->label('Search')
                ->submit('search')
                ->icon('heroicon-o-magnifying-glass'),
        ];
    }
}
