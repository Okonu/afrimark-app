<?php

namespace App\Filament\Client\Resources\DebtorResource\Pages;

use App\Enums\DebtorStatus;
use App\Enums\DocumentType;
use App\Filament\Client\Resources\DebtorResource;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class EditDebtor extends EditRecord
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
                            ->options([
                                DebtorStatus::PENDING->value => DebtorStatus::PENDING->label(),
                                DebtorStatus::ACTIVE->value => DebtorStatus::ACTIVE->label(),
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Add New Invoice')
                    ->schema([
                        Repeater::make('new_invoices')
                            ->label('Add New Invoices')
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
                            ->defaultItems(0)
                            ->addActionLabel('Add New Invoice')
                            ->collapsible(),
                    ]),

                Section::make('Upload Additional Documents')
                    ->schema([
                        Repeater::make('new_documents')
                            ->label('Additional Documents')
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
                            ->addActionLabel('Add Document Group')
                            ->collapsible(),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $business = Auth::user()->businesses()->first();

        if ($business) {
            // Get the amount_owed from the pivot table
            $businessDebtor = $this->record->businesses()
                ->where('business_id', $business->id)
                ->first();

            if ($businessDebtor) {
                $data['amount_owed'] = $businessDebtor->pivot->amount_owed;
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Filter out fields that don't belong in the Debtor model
        return array_intersect_key($data, array_flip([
            'name',
            'kra_pin',
            'email',
            'status',
        ]));
    }

    protected function afterSave(): void
    {
        $business = Auth::user()->businesses()->first();

        if ($business && isset($this->data['amount_owed'])) {
            // Update the business_debtor relationship with new amount
            $this->record->businesses()->updateExistingPivot($business->id, [
                'amount_owed' => $this->data['amount_owed'],
                // Don't update other pivot fields here
            ]);
        }

        // Process any new invoices
        if (isset($this->data['new_invoices']) && is_array($this->data['new_invoices']) && !empty($this->data['new_invoices'])) {
            foreach ($this->data['new_invoices'] as $invoiceData) {
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

            Notification::make()
                ->title('Invoices Added')
                ->body(count($this->data['new_invoices']) . ' new invoice(s) have been added to this debtor.')
                ->success()
                ->send();
        }

        // Process any newly added documents
        if (isset($this->data['new_documents']) && is_array($this->data['new_documents'])) {
            foreach ($this->data['new_documents'] as $documentGroup) {
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

            if (count($this->data['new_documents']) > 0) {
                Notification::make()
                    ->title('Documents Added')
                    ->body('New documents have been added to this debtor.')
                    ->success()
                    ->send();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
