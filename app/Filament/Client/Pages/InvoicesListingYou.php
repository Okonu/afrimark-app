<?php

namespace App\Filament\Client\Pages;

use App\Models\Invoice;
use App\Models\Debtor;
use App\Models\Business;
use App\Models\Dispute;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications;

class InvoicesListingYou extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Invoices Listing You';

    protected static ?string $title = 'Invoices Listing You';
    protected static ?string $navigationGroup = 'Listings';
    protected static ?int $navigationSort = 4;

    protected static string $view = 'filament.client.pages.invoices-listing-you';

    protected ?Business $business = null;
    public ?string $businessId = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->business = $user ? $user->businesses()->first() : null;

        if (!$this->business) {
            $this->redirect(route('filament.client.auth.business-information'));
            return;
        }

        // Set the business ID from the query string if present
        $this->businessId = request()->query('business_id');
    }

    public function table(Table $table): Table
    {
        // Safety check to ensure business exists
        if (!$this->business) {
            return $table->query(Invoice::query()->whereNull('id')); // Return empty query
        }

        $query = Invoice::query()
            ->whereHas('debtor', function ($query) {
                // Ensure the debtor has the current business's KRA PIN
                $query->where('kra_pin', $this->business->registration_number);
            });

        // Filter by business ID if specified
        if ($this->businessId) {
            $query->where('business_id', $this->businessId);
        }

        return $table
            ->query($query)
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->label('From Business')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice Number')
                    ->searchable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_amount')
                    ->label('Invoice Amount')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_amount')
                    ->label('Due Amount')
                    ->money('KES')
                    ->sortable(),

//                Tables\Columns\TextColumn::make('debtor.kra_pin')
//                    ->label('KRA PIN')
//                    ->searchable()
//                    ->sortable(),

                Tables\Columns\TextColumn::make('days_overdue')
                    ->label('OverdueDays')
                    ->numeric()
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => match(true) {
                        $state <= 0 => 'success',
                        $state > 0 && $state <= 30 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('debtor.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'disputed' => 'danger',
                        'pending' => 'warning',
                        'active' => 'success',
                        'paid' => 'primary',
                        default => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('business')
                    ->relationship('business', 'name')
                    ->label('Business'),

                Tables\Filters\SelectFilter::make('status')
                    ->relationship('debtor', 'status')
                    ->options([
                        'active' => 'Active',
                        'disputed' => 'Disputed',
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                    ])
                    ->label('Status'),

                Tables\Filters\Filter::make('overdue')
                    ->query(fn (Builder $query): Builder => $query->where('days_overdue', '>', 0))
                    ->label('Overdue Only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->iconButton()
                    ->url(fn (Invoice $record): string => route('filament.client.resources.invoices.view', ['record' => $record->id])),

                Tables\Actions\Action::make('dispute')
                    ->label('Dispute')
                    ->color('danger')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->modalHeading('Dispute Invoice')
                    ->modalDescription('Are you sure you want to dispute this invoice? Please provide details for your dispute.')
                    ->form([
                        \Filament\Forms\Components\Select::make('dispute_type')
                            ->label('Dispute Type')
                            ->options([
                                'incorrect_amount' => 'Incorrect Amount',
                                'service_not_rendered' => 'Service Not Rendered',
                                'incorrect_details' => 'Incorrect Details',
                                'duplicate_invoice' => 'Duplicate Invoice',
                                'other' => 'Other',
                            ])
                            ->required(),

                        \Filament\Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->maxLength(500),
                    ])
                    ->action(function (Invoice $record, array $data): void {
                        // Find or create the debtor record
                        $debtor = $record->debtor;

                        // Update debtor status
                        $debtor->status = 'disputed';
                        $debtor->status_notes = 'Invoice disputed: ' . $data['description'];
                        $debtor->status_updated_by = Auth::id();
                        $debtor->status_updated_at = now();
                        $debtor->save();

                        // Create a dispute record
                        Dispute::create([
                            'debtor_id' => $debtor->id,
                            'business_id' => $this->business->id,
                            'dispute_type' => $data['dispute_type'],
                            'description' => $data['description'],
                            'status' => 'pending',
                        ]);

                        Notifications\Notification::make()
                            ->title('Invoice Disputed')
                            ->success()
                            ->body('The invoice has been marked as disputed.')
                            ->send();
                    })
                    ->visible(fn (Invoice $record): bool => $record->debtor->status !== 'disputed' && $record->debtor->status !== 'paid'),
            ])
            ->defaultSort('invoice_date', 'desc')
            ->paginated([10, 25, 50, 100]);
    }
}
