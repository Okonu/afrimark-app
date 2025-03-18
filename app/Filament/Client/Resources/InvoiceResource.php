<?php

namespace App\Filament\Client\Resources;

use App\Enums\DocumentType;
use App\Filament\Client\Resources\InvoiceResource\Pages;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Invoices';
    protected static ?string $navigationGroup = 'Records';
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

                Forms\Components\Section::make('Invoice Documents')
                    ->schema([
                        Forms\Components\Repeater::make('documents')
                            ->schema([
                                Forms\Components\Select::make('document_type')
                                    ->label('Document Type')
                                    ->options(function () {
                                        $options = [];
                                        $invoiceDocTypes = [
                                            DocumentType::INVOICE,
                                            DocumentType::PAYMENT_PROOF,
                                            DocumentType::EVIDENCE,
                                            DocumentType::DELIVERY_NOTE,
                                            DocumentType::RECEIPT,
                                            DocumentType::PURCHASE_ORDER,
                                            DocumentType::CONTRACT,
                                        ];
                                        foreach ($invoiceDocTypes as $type) {
                                            $options[$type->value] = $type->label();
                                        }
                                        return $options;
                                    })
                                    ->required(),

                                Forms\Components\FileUpload::make('file')
                                    ->label('Upload Document')
                                    ->directory('debtor-documents')
                                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                    ->maxSize(10240)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Add Another Document')
                            ->itemLabel(function (array $state): ?string {
                                if (isset($state['document_type'])) {
                                    $docType = DocumentType::tryFrom($state['document_type']);
                                    if ($docType) {
                                        return $docType->label();
                                    }
                                }
                                return 'Document';
                            }),
                    ]),

                Forms\Components\Section::make('Terms & Conditions')
                    ->schema([
                        Forms\Components\Checkbox::make('liability_confirmation')
                            ->label('I confirm that all the information provided is accurate and I bear full liability for its correctness')
                            ->required(),

                        Forms\Components\Checkbox::make('terms_accepted')
                            ->label('I have read and accepted the Terms & Conditions')
                            ->required(),

                        Forms\Components\Placeholder::make('disclaimer')
                            ->label('Disclaimer')
                            ->content('By submitting this invoice, you certify that these are legitimate business debts that are due and payable. Fraudulent submissions may result in legal action. Afrimark does not guarantee payment but provides a platform to record and track business debts.')
                            ->columnSpanFull(),
                    ]),
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Tables\Actions\Action::make('add_document')
                        ->label('Add Document')
                        ->icon('heroicon-o-document-plus')
                        ->form([
                            Forms\Components\Select::make('document_type')
                                ->label('Document Type')
                                ->options(function () {
                                    $options = [];
                                    $invoiceDocTypes = [
                                        DocumentType::INVOICE,
                                        DocumentType::PAYMENT_PROOF,
                                        DocumentType::EVIDENCE,
                                        DocumentType::DELIVERY_NOTE,
                                        DocumentType::RECEIPT,
                                        DocumentType::PURCHASE_ORDER,
                                        DocumentType::CONTRACT,
                                    ];
                                    foreach ($invoiceDocTypes as $type) {
                                        $options[$type->value] = $type->label();
                                    }
                                    return $options;
                                })
                                ->required(),

                            Forms\Components\FileUpload::make('file')
                                ->label('Upload Document')
                                ->directory('debtor-documents')
                                ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                ->maxSize(10240)
                                ->required(),
                        ])
                        ->action(function (Invoice $record, array $data) {
                            // Create document in the debtor_documents table
                            $record->debtor->documents()->create([
                                'type' => $data['document_type'],
                                'file_path' => $data['file'],
                                'original_filename' => basename($data['file']),
                                'uploaded_by' => Auth::id(),
                                'processing_status' => 'pending',
                                'related_invoice_id' => $record->id,
                            ]);

                            \Filament\Notifications\Notification::make()
                                ->title('Document Added')
                                ->body("Document has been added to Invoice #{$record->invoice_number}")
                                ->success()
                                ->send();
                        }),
                ]),
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
