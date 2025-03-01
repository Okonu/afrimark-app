<?php

namespace App\Filament\Client\Pages;

use App\Models\Business;
use App\Models\Debtor;
use Filament\Pages\Page;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class BusinessProfile extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Profile';
    protected static ?string $title = 'Business Profile';
    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.client.pages.business-profile';

    protected ?Business $business = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->business = $user ? $user->businesses()->first() : null;

        if (!$this->business) {
            $this->redirect(route('filament.client.auth.business-information'));
            return;
        }

        $this->form->fill([
            'name' => $this->business->name ?? '',
            'email' => $this->business->email ?? '',
            'phone' => $this->business->phone ?? '',
            'address' => $this->business->address ?? '',
            'registration_number' => $this->business->registration_number ?? '',
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tab::make('Business Information')
                            ->schema([
                                // Business information form fields
                            ]),

                        Tab::make('Listed By Others')
                            ->schema([
                                $this->getListedByOthersTable(),
                            ]),

                        Tab::make('Your Debtors')
                            ->schema([
                                $this->getYourDebtorsTable(),
                            ]),
                    ]),
            ]);
    }

    protected function getListedByOthersTable()
    {
        if (!$this->business) {
            return null;
        }

        return Tables\Table::make([
            'kra_pin' => $this->business->registration_number,
        ])
            ->query(
                Debtor::query()
                    ->where('kra_pin', $this->business->registration_number)
                    ->where('status', 'active')
            )
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Listed By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_owed')
                    ->label('Amount Owed')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable(),

                Tables\Columns\TextColumn::make('listed_at')
                    ->label('Listing Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('dispute')
                    ->label('Dispute')
                    ->color('danger')
                    ->url(fn (Debtor $record): string => route('filament.client.resources.disputes.create', ['debtor' => $record->id])),
            ])
            ->paginated([10, 25, 50]);
    }

    protected function getYourDebtorsTable()
    {
        if (!$this->business) {
            return null;
        }

        return Tables\Table::make([
            'business_id' => $this->business->id,
        ])
            ->query(
                Debtor::query()
                    ->where('business_id', $this->business->id)
                    ->where('status', 'active')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Debtor Business')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_owed')
                    ->label('Amount Owed')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable(),

                Tables\Columns\TextColumn::make('listed_at')
                    ->label('Listing Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'disputed',
                        'warning' => 'pending',
                        'success' => 'active',
                        'primary' => 'paid',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('update_payment')
                    ->label('Update Payment')
                    ->color('success')
                    ->url(fn (Debtor $record): string => route('filament.client.resources.debtors.payment', ['record' => $record->id])),

                Tables\Actions\ViewAction::make()
                    ->url(fn (Debtor $record): string => route('filament.client.resources.debtors.view', ['record' => $record->id])),
            ])
            ->paginated([10, 25, 50]);
    }
}
