<?php

namespace App\Filament\Client\Pages;

use App\Models\Business;
use App\Models\Debtor;
use Filament\Pages\Page;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class BusinessProfile extends Page implements HasTable
{
    use InteractsWithTable;

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
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Debtor::query()
                    ->where('business_id', $this->business?->id)
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

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'disputed' => 'danger',
                        'pending' => 'warning',
                        'active' => 'success',
                        'paid' => 'primary',
                        default => 'gray',
                    }),
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

    public function getListedByOthersTable()
    {
        if (!$this->business) {
            return null;
        }

        return Tables\Table::make($this)
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
}
