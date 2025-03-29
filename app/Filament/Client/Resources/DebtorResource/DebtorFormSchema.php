<?php

namespace App\Filament\Client\Resources\DebtorResource;

use App\Enums\DebtorStatus;
use App\Enums\DocumentType;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Carbon\Carbon;

class DebtorFormSchema
{
    /**
     * Get the common form schema for debtors
     *
     * @param bool $isCreate Whether this is for create or edit mode
     * @param callable $calculateTotalAmount Callback to calculate total amount
     * @param callable|null $calculateNewInvoicesTotal Callback to calculate new invoices total (edit only)
     * @return array
     */
    public static function getSchema(bool $isCreate, callable $calculateTotalAmount, callable $calculateNewInvoicesTotal = null): array
    {
        $schema = [
            Section::make('Debtor Information')
                ->schema([
                    TextInput::make('name')
                        ->label('Business Name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('kra_pin')
                        ->label('KRA PIN')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Business Email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                ])
                ->columns(2),
        ];

        // Different sections based on create or edit
        if ($isCreate) {
            // For create mode
            $schema[] = self::getCreateModeSchemas($calculateTotalAmount);
        } else {
            // For edit mode
            $schema[] = self::getEditModeSchemas($calculateNewInvoicesTotal);
        }

        // Document section (common for both create and edit)
        $schema[] = self::getDocumentSection();

        // Terms & Conditions (only for create)
        if ($isCreate) {
            $schema[] = self::getTermsSection();
        }

        return $schema;
    }

    /**
     * Get schema sections specific to create mode
     */
    private static function getCreateModeSchemas(callable $calculateTotalAmount): Section
    {
        return Section::make('Invoice Information')
            ->schema([
                Placeholder::make('calculated_amount_owed')
                    ->label('Calculated Amount Owed')
                    ->content(function (Get $get, Set $set) use ($calculateTotalAmount): string {
                        return 'KES ' . number_format($calculateTotalAmount($get, $set), 2);
                    }),

                TextInput::make('amount_owed')
                    ->label('Amount Owed')
                    ->numeric()
                    ->prefix('KES')
                    ->disabled()
                    ->dehydrated(false),

                Select::make('status')
                    ->label('Status')
                    ->disabled()
                    ->options([
                        DebtorStatus::PENDING->value => DebtorStatus::PENDING->label(),
                        DebtorStatus::ACTIVE->value => DebtorStatus::ACTIVE->label(),
                    ])
                    ->default(DebtorStatus::PENDING->value)
                    ->required(),

                Repeater::make('invoices')
                    ->label('Invoices')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->required()
                            ->maxLength(100)
                            ->reactive(),

                        Forms\Components\DatePicker::make('invoice_date')
                            ->label('Invoice Date')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calculatePaymentTerms($get, $set);
                            }),

                        Forms\Components\DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set) {
                                self::calculatePaymentTerms($get, $set);
                            }),

                        TextInput::make('invoice_amount')
                            ->label('Invoice Amount')
                            ->numeric()
                            ->required()
                            ->prefix('KES')
                            ->reactive(),

                        TextInput::make('due_amount')
                            ->label('Due Amount')
                            ->helperText('Amount still owed on this invoice')
                            ->numeric()
                            ->required()
                            ->prefix('KES')
                            ->reactive()
                            ->afterStateUpdated(function (Get $get, Set $set) use ($calculateTotalAmount) {
                                $calculateTotalAmount($get, $set);
                            }),

                        TextInput::make('payment_terms')
                            ->label('Payment Terms (Days)')
                            ->numeric()
                            ->integer()
                            ->disabled()
                            ->required()
                            ->placeholder('e.g. 30, 60, 90')
                            ->reactive(),
                    ])
                    ->columns(3)
                    ->defaultItems(1)
                    ->minItems(1)
                    ->addActionLabel('Add Another Invoice')
                    ->collapsible()
                    ->afterStateUpdated(function (Get $get, Set $set) use ($calculateTotalAmount) {
                        $calculateTotalAmount($get, $set);
                    }),
            ]);
    }

    /**
     * Get schema sections specific to edit mode
     */
    private static function getEditModeSchemas(callable $calculateNewInvoicesTotal = null): Section
    {
        return Section::make('Invoices and Status')
            ->schema([
                Placeholder::make('existing_invoices_total')
                    ->label('Total from Existing Invoices')
                    ->content(function (): string {
                        return 'KES 0.00'; // This will be overridden in the EditDebtor class
                    }),

                Placeholder::make('new_invoices_total')
                    ->label('Total from New Invoices')
                    ->content(function (): string {
                        return 'KES 0.00'; // This will be overridden in the EditDebtor class
                    }),

                Placeholder::make('calculated_amount_owed')
                    ->label('Total Amount Owed')
                    ->content(function (): string {
                        return 'KES 0.00'; // This will be overridden in the EditDebtor class
                    }),

                TextInput::make('amount_owed')
                    ->label('Amount Owed (Read-only)')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false)
                    ->prefix('KES'),

                Select::make('status')
                    ->label('Status')
                    ->options([
                        DebtorStatus::PENDING->value => DebtorStatus::PENDING->label(),
                        DebtorStatus::ACTIVE->value => DebtorStatus::ACTIVE->label(),
                    ])
                    ->required(),

                Section::make('Add New Invoice')
                    ->schema([
                        Repeater::make('new_invoices')
                            ->label('Add New Invoices')
                            ->schema([
                                TextInput::make('invoice_number')
                                    ->label('Invoice Number')
                                    ->required()
                                    ->maxLength(100)
                                    ->reactive(),

                                Forms\Components\DatePicker::make('invoice_date')
                                    ->label('Invoice Date')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::calculatePaymentTerms($get, $set);
                                    }),

                                Forms\Components\DatePicker::make('due_date')
                                    ->label('Due Date')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::calculatePaymentTerms($get, $set);
                                    }),

                                TextInput::make('invoice_amount')
                                    ->label('Invoice Amount')
                                    ->numeric()
                                    ->required()
                                    ->prefix('KES')
                                    ->reactive(),

                                TextInput::make('due_amount')
                                    ->label('Due Amount')
                                    ->helperText('Amount still owed on this invoice')
                                    ->numeric()
                                    ->required()
                                    ->prefix('KES')
                                    ->reactive()
                                    ->afterStateUpdated(function (Get $get, Set $set) use ($calculateNewInvoicesTotal) {
                                        $calculateNewInvoicesTotal($get);
                                    }),

                                TextInput::make('payment_terms')
                                    ->label('Payment Terms (Days)')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->placeholder('e.g. 30, 60, 90')
                                    ->reactive(),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->addActionLabel('Add New Invoice')
                            ->collapsible()
                            ->afterStateUpdated(function (Get $get, Set $set) use ($calculateNewInvoicesTotal) {
                                $calculateNewInvoicesTotal($get);
                            }),
                    ])
            ]);
    }

    /**
     * Get document upload section
     */
    private static function getDocumentSection(): Section
    {
        return Section::make('Supporting Documents')
            ->schema([
                Repeater::make('documents')
                    ->label('Supporting Documents')
                    ->schema([
                        Select::make('document_type')
                            ->label('Document Type')
                            ->options(function () {
                                // Only show debt and payment document types
                                $documentTypes = array_merge(
                                    DocumentType::getDebtDocumentTypes(),
                                    DocumentType::getPaymentDocumentTypes()
                                );

                                $options = [];
                                foreach ($documentTypes as $type) {
                                    $options[$type->value] = $type->label();
                                }
                                return $options;
                            })
                            ->required(),

                        FileUpload::make('files')
                            ->label('Upload Documents')
                            ->helperText('Upload invoices, contracts, or other relevant documents (max 2MB)')
                            ->multiple()
                            ->directory('debtor-documents')
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(2048) // 2MB max
                            ->required()
                            ->maxFiles(1) // Limit to 1 file per group
                            ->loadingIndicatorPosition('left')
                            ->uploadProgressIndicatorPosition('left')
                            ->removeUploadedFileButtonPosition('right')
                            ->uploadButtonPosition('left')
                            ->panelAspectRatio('2:1')
                            ->panelLayout('compact'),
                    ])
                    ->columns(2)
                    ->minItems(1)
                    ->addActionLabel('Add Document Group')
                    ->collapsible(),
            ]);
    }

    /**
     * Get terms and conditions section
     */
    private static function getTermsSection(): Section
    {
        return Section::make('Terms & Conditions')
            ->schema([
                Forms\Components\Checkbox::make('liability_confirmation')
                    ->label('I confirm that all the information provided is accurate and I bear full liability for its correctness')
                    ->required(),

                Forms\Components\Checkbox::make('terms_accepted')
                    ->label('I have read and accepted the Terms & Conditions')
                    ->required(),
            ]);
    }

    /**
     * Calculate payment terms from invoice_date and due_date
     */
    private static function calculatePaymentTerms(Get $get, Set $set): void
    {
        $invoiceDate = $get('invoice_date');
        $dueDate = $get('due_date');

        if ($invoiceDate && $dueDate) {
            try {
                $invoiceCarbon = Carbon::parse($invoiceDate);
                $dueCarbon = Carbon::parse($dueDate);

                // Calculate the difference in days
                $paymentTerms = $dueCarbon->diffInDays($invoiceCarbon);

                // Update the payment_terms field
                $set('payment_terms', $paymentTerms);
            } catch (\Exception $e) {
                // If there's an error, don't update
            }
        }
    }
}
