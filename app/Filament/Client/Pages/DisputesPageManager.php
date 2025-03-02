<?php

namespace App\Filament\Client\Pages;

use App\Models\Debtor;
use App\Models\Dispute;
use App\Models\Business;
use Filament\Pages\Page;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords\Tab as ListRecordsTab;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class DisputesPageManager extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationLabel = 'Disputes Management';
    protected static ?int $navigationSort = 4;
    protected static ?string $title = 'Disputes Management';

    protected static string $view = 'filament.client.pages.disputes-page-manager';

    public $activeTab = 'disputable-listings';

    public function mount(): void
    {
        $this->activeTab = request()->query('tab', 'disputable-listings');
    }

    /**
     * Required method for HasTable interface
     * This base query is not directly used but must be implemented
     */
    protected function getTableQuery(): Builder
    {
        // This is a placeholder query since we're using custom tables
        // Each tab has its own query defined in its respective method
        $business = Auth::user()->businesses()->first();

        if (!$business) {
            return Dispute::query()->where('id', 0); // Empty query
        }

        return Dispute::query()
            ->where('id', 0); // Empty query, real queries are in specific table methods
    }

    /**
     * Required method for HasTable interface
     */
    protected function getTableColumns(): array
    {
        return []; // Not used directly, each table has its own columns
    }

    /**
     * Get disputable listings table
     * Shows listings where the current business is the debtor and still in dispute window
     */
    public function getDisputableListingsTable()
    {
        $business = Auth::user()->businesses()->first();

        if (!$business) {
            return null;
        }

        return Tables\Table::make($this)
            ->query(
                Debtor::query()
                    ->where(function($query) use ($business) {
                        $query->where('kra_pin', $business->registration_number)
                            ->orWhere('email', $business->email);
                    })
                    ->where('status', 'pending')
                    ->where('listing_goes_live_at', '>', now())
                    ->whereDoesntHave('disputes') // No disputes created yet
            )
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->label('Listed By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount_owed')
                    ->label('Amount Claimed')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Listed Date')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('listing_goes_live_at')
                    ->label('Dispute Window Closes')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('create_dispute')
                    ->label('Create Dispute')
                    ->color('danger')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->url(fn (Debtor $record): string => route('filament.client.resources.disputes.create', ['debtor' => $record->id])),
            ])
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('No Disputable Listings')
            ->emptyStateDescription('There are no pending listings against your business within the dispute window.')
            ->paginated([10, 25, 50]);
    }

    /**
     * Get my disputes table
     * Shows disputes created by the current business
     */
    public function getMyDisputesTable()
    {
        $business = Auth::user()->businesses()->first();

        if (!$business) {
            return null;
        }

        return Tables\Table::make($this)
            ->query(
                Dispute::query()
                    ->whereHas('debtor', function ($query) use ($business) {
                        $query->where(function($q) use ($business) {
                            $q->where('kra_pin', $business->registration_number)
                                ->orWhere('email', $business->email);
                        });
                    })
            )
            ->columns([
                Tables\Columns\TextColumn::make('debtor.business.name')
                    ->label('Listed By')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('debtor.amount_owed')
                    ->label('Amount Claimed')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\TextColumn::make('dispute_type')
                    ->label('Dispute Reason')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'wrong_amount' => 'Wrong Amount',
                        'no_debt' => 'No Debt Exists',
                        'already_paid' => 'Already Paid',
                        'wrong_business' => 'Wrong Business Listed',
                        'other' => 'Other',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'primary' => 'under_review',
                        'success' => 'resolved_approved',
                        'danger' => 'resolved_rejected',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dispute Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (Dispute $record): string => route('filament.client.resources.disputes.view', ['record' => $record->id])),
            ])
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('No Disputes Created')
            ->emptyStateDescription('You have not created any disputes yet.')
            ->paginated([10, 25, 50]);
    }

    /**
     * Get disputes to respond table
     * Shows disputes where the current business is the lister and needs to respond
     */
    public function getDisputesToRespondTable()
    {
        $business = Auth::user()->businesses()->first();

        if (!$business) {
            return null;
        }

        return Tables\Table::make($this)
            ->query(
                Dispute::query()
                    ->whereHas('debtor', function ($query) use ($business) {
                        $query->where('business_id', $business->id);
                    })
                    ->where('status', 'pending')
            )
            ->columns([
                Tables\Columns\TextColumn::make('debtor.name')
                    ->label('Debtor Business')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('debtor.amount_owed')
                    ->label('Amount Claimed')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\TextColumn::make('dispute_type')
                    ->label('Dispute Reason')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'wrong_amount' => 'Wrong Amount',
                        'no_debt' => 'No Debt Exists',
                        'already_paid' => 'Already Paid',
                        'wrong_business' => 'Wrong Business Listed',
                        'other' => 'Other',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dispute Date')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('respond')
                    ->label('Respond')
                    ->icon('heroicon-o-chat-bubble-left')
                    ->color('primary')
                    ->url(fn (Dispute $record): string => route('filament.client.resources.disputes.respond', ['record' => $record->id])),

                Tables\Actions\ViewAction::make()
                    ->url(fn (Dispute $record): string => route('filament.client.resources.disputes.view', ['record' => $record->id])),
            ])
            ->emptyStateIcon('heroicon-o-document-text')
            ->emptyStateHeading('No Disputes To Respond')
            ->emptyStateDescription('There are no pending disputes that require your response.')
            ->paginated([10, 25, 50]);
    }
}
