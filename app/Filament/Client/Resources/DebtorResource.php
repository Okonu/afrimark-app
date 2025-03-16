<?php

namespace App\Filament\Client\Resources;

use App\Enums\DebtorStatus;
use App\Filament\Client\Resources\DebtorResource\Pages;
use App\Models\Debtor;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class DebtorResource extends Resource
{
    protected static ?string $model = Debtor::class;

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Debtors';
    protected static ?int $navigationSort = 3;

    public static function getEloquentQuery(): Builder
    {
        $businessId = Auth::user()->businesses()->first()?->id;

        return parent::getEloquentQuery()
            ->whereHas('businesses', function ($query) use ($businessId) {
                $query->where('businesses.id', $businessId);
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Debtor Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Business Name')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('kra_pin')
                            ->label('KRA PIN')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Business Email')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('amount_owed')
                            ->label('Amount Owed')
                            ->numeric()
                            ->required()
                            ->prefix('KES'),

                        Forms\Components\TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Supporting Documents')
                    ->schema([
                        Forms\Components\FileUpload::make('documents')
                            ->label('Supporting Documents')
                            ->helperText('Upload invoices, contracts, or any other relevant documents')
                            ->multiple()
                            ->directory('debtor-documents')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240)
                            ->required(),
                    ]),

                Forms\Components\Section::make('Terms & Conditions')
                    ->schema([
                        Forms\Components\Checkbox::make('liability_confirmation')
                            ->label('I confirm that all the information provided is accurate and I bear full liability for its correctness')
                            ->required(),

                        Forms\Components\Checkbox::make('terms_accepted')
                            ->label('I have read and accepted the Terms & Conditions')
                            ->required(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Business Name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kra_pin')
                    ->label('KRA PIN')
                    ->searchable(),

                Tables\Columns\TextColumn::make('amount_owed')
                    ->label('Amount Owed')
                    ->money('KES')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'disputed',
                        'warning' => 'pending',
                        'success' => 'active',
                        'primary' => 'paid',
                    ]),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        DebtorStatus::PENDING->value => DebtorStatus::PENDING->label(),
                        DebtorStatus::PARTIAL->value => DebtorStatus::PARTIAL->label(),
                        DebtorStatus::PAID->value => DebtorStatus::PAID->label(),
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('update_payment_status')
                    ->label('Update Payment Status')
                    ->color('success')
                    ->visible(fn (Debtor $record) => $record->status !== DebtorStatus::PAID)
                    ->icon('heroicon-o-currency-dollar')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Payment Status')
                            ->options([
                                DebtorStatus::PENDING->value => DebtorStatus::PENDING->label(),
                                DebtorStatus::PARTIAL->value => DebtorStatus::PARTIAL->label(),
                                DebtorStatus::PAID->value => DebtorStatus::PAID->label(),
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Additional Notes')
                            ->placeholder('Enter any additional information')
                            ->maxLength(500),
                    ])
                    ->action(function (Debtor $record, array $data): void {
                        // Update the debtor status with additional tracking information
                        $record->update([
                            'status' => $data['status'],
                            'status_notes' => $data['notes'] ?? null,
                            'status_updated_by' => auth()->id(),
                            'status_updated_at' => now(),
                        ]);

//                        // Optionally, create a dispute record if notes are provided
//                        if (!empty($data['notes'])) {
//                            $record->disputes()->create([
//                                'notes' => $data['notes'],
//                                'status' => $data['status'],
//                                'user_id' => auth()->id(),
//                            ]);
//                        }

                        Notification::make()
                            ->title('Payment Status Updated')
                            ->body("The payment status for {$record->name} has been updated to " . DebtorStatus::from($data['status'])->label() . ".")
                            ->success()
                            ->send();
                    })
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('export')
                    ->label('Export Selected')
                    ->icon('heroicon-o-document-arrow-down')
                    ->action(function (Collection $records) {
                        // Export logic
                    }),
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
            'index' => Pages\ListDebtors::route('/'),
            'create' => Pages\CreateDebtor::route('/create'),
            'import' => Pages\ImportDebtors::route('/import'),
            'import-payments' => Pages\ImportPayments::route('/import-payments'),
            'edit' => Pages\EditDebtor::route('/{record}/edit'),
            'view' => Pages\ViewDebtor::route('/{record}'),
            'payment' => Pages\UpdatePayment::route('/{record}/payment'),
        ];
    }
}
