<?php

namespace App\Filament\Client\Resources;

use App\Filament\Client\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Invoices';
    protected static ?int $navigationSort = 4;

    public static function getEloquentQuery(): Builder
    {
        $businessId = Auth::user()->businesses()->first()?->id;

        return parent::getEloquentQuery()
            ->where('business_id', $businessId);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Invoice Information')
                    ->schema([
                        Forms\Components\Select::make('debtor_id')
                            ->relationship('debtor', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('invoice_number')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\DatePicker::make('invoice_date')
                            ->required(),

                        Forms\Components\DatePicker::make('due_date')
                            ->required(),

                        Forms\Components\TextInput::make('invoice_amount')
                            ->label('Invoice Amount')
                            ->numeric()
                            ->required()
                            ->prefix('KES'),

                        Forms\Components\TextInput::make('due_amount')
                            ->label('Amount Due')
                            ->numeric()
                            ->required()
                            ->prefix('KES')
                            ->helperText('Amount remaining to be paid'),

                        Forms\Components\TextInput::make('payment_terms')
                            ->label('Payment Terms (Days)')
                            ->numeric()
                            ->integer()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('debtor.name')
                    ->label('Debtor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_amount')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_amount')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_terms')
                    ->label('Terms (Days)')
                    ->numeric(),

                Tables\Columns\TextColumn::make('days_overdue')
                    ->label('Days Overdue')
                    ->badge()
                    ->color(fn (int $state): string => match(true) {
                        $state <= 0 => 'success',
                        $state <= 30 => 'warning',
                        default => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'overdue' => 'Overdue',
                        'due_soon' => 'Due Soon (7 days)',
                        'paid' => 'Paid',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return match($data['value']) {
                            'overdue' => $query->where('due_date', '<', now())
                                ->where('due_amount', '>', 0),
                            'due_soon' => $query->whereBetween('due_date', [now(), now()->addDays(7)])
                                ->where('due_amount', '>', 0),
                            'paid' => $query->where('due_amount', 0),
                            default => $query,
                        };
                    }),

                Tables\Filters\Filter::make('date_range')
                    ->form([
                        Forms\Components\DatePicker::make('from'),
                        Forms\Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('invoice_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'create' => Pages\CreateInvoice::route('/create'),
            'import' => Pages\ImportInvoices::route('/import'),
            'view' => Pages\ViewInvoice::route('/{record}'),
            'edit' => Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
