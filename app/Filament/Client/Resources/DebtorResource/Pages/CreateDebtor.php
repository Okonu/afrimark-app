<?php

namespace App\Filament\Client\Resources\DebtorResource\Pages;

use App\Enums\DebtorStatus;
use App\Enums\DocumentType;
use App\Filament\Client\Resources\DebtorResource;
use App\Models\BusinessDebtor;
use App\Models\DebtorDocument;
use App\Services\Debtor\DebtorService;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateDebtor extends CreateRecord
{
    protected static string $resource = DebtorResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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

                        TextInput::make('amount_owed')
                            ->label('Amount Owed')
                            ->numeric()
                            ->required()
                            ->prefix('KES'),

                        Select::make('status')
                            ->label('Status')
                            ->disabled()
                            ->options([
                                DebtorStatus::PENDING->value => DebtorStatus::PENDING->label(),
                                DebtorStatus::ACTIVE->value => DebtorStatus::ACTIVE->label(),
                            ])
                            ->default(DebtorStatus::PENDING->value)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Invoice Information')
                    ->schema([
                        Repeater::make('invoices')
                            ->label('Invoices')
                            ->schema([
                                TextInput::make('invoice_number')
                                    ->label('Invoice Number')
                                    ->required()
                                    ->maxLength(100),

                                Forms\Components\DatePicker::make('invoice_date')
                                    ->label('Invoice Date')
                                    ->required(),

                                Forms\Components\DatePicker::make('due_date')
                                    ->label('Due Date')
                                    ->required(),

                                TextInput::make('invoice_amount')
                                    ->label('Invoice Amount')
                                    ->numeric()
                                    ->required()
                                    ->prefix('KES'),

                                TextInput::make('due_amount')
                                    ->label('Due Amount')
                                    ->helperText('Amount still owed on this invoice')
                                    ->numeric()
                                    ->required()
                                    ->prefix('KES'),

                                TextInput::make('payment_terms')
                                    ->label('Payment Terms (Days)')
                                    ->numeric()
                                    ->integer()
                                    ->required()
                                    ->placeholder('e.g. 30, 60, 90'),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel('Add Another Invoice')
                            ->collapsible(),
                    ]),

                Section::make('Supporting Documents')
                    ->schema([
                        Repeater::make('documents')
                            ->label('Supporting Documents')
                            ->schema([
                                Select::make('document_type')
                                    ->label('Document Type')
                                    ->options(function () {
                                        $options = [];
                                        foreach (DocumentType::cases() as $type) {
                                            $options[$type->value] = $type->label();
                                        }
                                        return $options;
                                    })
                                    ->required(),

                                FileUpload::make('files')
                                    ->label('Upload Documents')
                                    ->helperText('Upload invoices, contracts, or any other relevant documents')
                                    ->multiple()
                                    ->directory('debtor-documents')
                                    ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                    ->maxSize(10240)
                                    ->required(),
                            ])
                            ->columns(2)
                            ->minItems(1)
                            ->addActionLabel('Add Document Group')
                            ->collapsible(),
                    ]),

                Section::make('Terms & Conditions')
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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Filter out fields that don't belong in the Debtor model
        $debtorData = array_intersect_key($data, array_flip([
            'name',
            'kra_pin',
            'email',
            'status',
        ]));

        // Add default fields
        $debtorData['listing_goes_live_at'] = now()->addDays(7);
        $debtorData['verification_token'] = \Illuminate\Support\Str::random(64);

        return $debtorData;
    }

    protected function afterCreate(): void
    {
        $debtorService = app(DebtorService::class);
        $business = Auth::user()->businesses()->first();

        if ($business) {
            // Create the business_debtor relationship with pivot data
            $this->record->businesses()->attach($business->id, [
                'amount_owed' => $this->data['amount_owed'] ?? 0,
                'average_payment_terms' => 0,
                'median_payment_terms' => 0,
                'average_days_overdue' => 0,
                'median_days_overdue' => 0,
                'average_dbt_ratio' => 0,
                'median_dbt_ratio' => 0,
            ]);

            // Create invoice records if provided
            if (isset($this->data['invoices']) && is_array($this->data['invoices'])) {
                foreach ($this->data['invoices'] as $invoiceData) {
                    // Create invoice record linked to debtor and business
                    $this->record->invoices()->create([
                        'business_id' => $business->id,
                        'invoice_number' => $invoiceData['invoice_number'],
                        'invoice_date' => $invoiceData['invoice_date'],
                        'due_date' => $invoiceData['due_date'],
                        'invoice_amount' => $invoiceData['invoice_amount'],
                        'due_amount' => $invoiceData['due_amount'],
                        'payment_terms' => $invoiceData['payment_terms'],
                        // Other metrics will be calculated by the model boot method
                    ]);
                }
            }
        }

        // Process each document group
        if (isset($this->data['documents']) && is_array($this->data['documents'])) {
            foreach ($this->data['documents'] as $documentGroup) {
                $documentType = $documentGroup['document_type'] ?? null;
                $files = $documentGroup['files'] ?? [];

                if ($documentType && is_array($files) && count($files) > 0) {
                    foreach ($files as $file) {
                        // Create document record
                        $document = $this->record->documents()->create([
                            'type' => $documentType,
                            'file_path' => $file,
                            'original_filename' => basename($file),
                            'uploaded_by' => Auth::id(),
                            'processing_status' => 'pending', // This will trigger the DocumentProcessable trait
                        ]);
                    }
                }
            }
        }

        // Send notification about the new debtor
        try {
            $debtorService->sendDebtorNotification($this->record);

            Notification::make()
                ->title('Debtor Created')
                ->body('The debtor has been added with ' . count($this->data['invoices'] ?? []) . ' invoice(s) and a notification has been sent.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Debtor Created')
                ->body('The debtor has been added, but we encountered an issue sending the notification.')
                ->warning()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
