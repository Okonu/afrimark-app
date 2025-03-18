<?php

namespace App\Filament\Client\Pages;

use App\Models\Business;
use App\Models\Debtor;
use App\Models\Invoice;
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
use Illuminate\Support\Facades\DB;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class BusinessProfile extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationLabel = 'Profile';

    protected static ?string $title = 'Business Profile';
    protected static ?string $navigationGroup = 'Overview';
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
                    ->whereHas('businesses', function ($query) {
                        $query->where('businesses.id', $this->business?->id);
                    })
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
            // Find distinct businesses that have invoiced the current business
                Business::query()
                    ->select('businesses.*')
                    ->join('invoices', 'businesses.id', '=', 'invoices.business_id')
                    ->join('debtors', 'invoices.debtor_id', '=', 'debtors.id')
                    ->where('debtors.kra_pin', $this->business->registration_number)
                    ->where('debtors.status', 'active')
                    ->distinct()
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Business Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount_claimed')
                    ->label('Total Amount Claimed')
                    ->money('KES')
                    ->getStateUsing(function (Business $record) {
                        // Get all the invoices from this business to the current business
                        return Invoice::query()
                            ->where('business_id', $record->id)
                            ->whereHas('debtor', function($query) {
                                $query->where('kra_pin', $this->business->registration_number)
                                    ->where('status', 'active');
                            })
                            ->sum('due_amount');
                    }),

                Tables\Columns\TextColumn::make('latest_invoice_date')
                    ->label('Latest Invoice Date')
                    ->getStateUsing(function (Business $record) {
                        // Get the most recent invoice date
                        $latestInvoice = Invoice::query()
                            ->where('business_id', $record->id)
                            ->whereHas('debtor', function($query) {
                                $query->where('kra_pin', $this->business->registration_number)
                                    ->where('status', 'active');
                            })
                            ->orderByDesc('invoice_date')
                            ->first();

                        return $latestInvoice ? $latestInvoice->invoice_date : null;
                    })
                    ->date()
                    ->sortable(),

            ])
            ->actions([
                Tables\Actions\Action::make('view_invoices')
                    ->label('View Invoices')
                    ->color('primary')
                    ->url(function (Business $record) {
                        // Link to our new InvoicesListingYou page with a business_id filter
                        return route('filament.client.pages.invoices-listing-you', [
                            'business_id' => $record->id,
                        ]);
                    }),

                Tables\Actions\Action::make('dispute')
                    ->label('Dispute Invoice')
                    ->color('danger')
                    ->url(function (Business $record) {
                        // Link to our InvoicesListingYou page to let the user select which invoice to dispute
                        return route('filament.client.pages.invoices-listing-you', [
                            'business_id' => $record->id,
                        ]);
                    }),
            ])
            ->paginated([10, 25, 50]);
    }
}
