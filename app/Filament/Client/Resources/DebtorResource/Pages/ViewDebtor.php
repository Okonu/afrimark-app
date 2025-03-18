<?php

namespace App\Filament\Client\Resources\DebtorResource\Pages;

use App\Enums\DebtorStatus;
use App\Enums\DocumentType;
use App\Filament\Client\Resources\DebtorResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\IconEntry;
use Illuminate\Support\Facades\Auth;

class ViewDebtor extends ViewRecord
{
    protected static string $resource = DebtorResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Debtor Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Business Name'),

                        TextEntry::make('kra_pin')
                            ->label('KRA PIN'),

                        TextEntry::make('email')
                            ->label('Business Email'),

                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'active' => 'success',
                                default => 'gray',
                            }),

                        TextEntry::make('pivot.amount_owed')
                            ->state(function ($record) {
                                $business = Auth::user()->businesses()->first();
                                if ($business) {
                                    $businessDebtor = $record->businesses()
                                        ->where('business_id', $business->id)
                                        ->first();

                                    if ($businessDebtor) {
                                        return $businessDebtor->pivot->amount_owed;
                                    }
                                }
                                return 0;
                            })
                            ->money('KES')
                            ->label('Amount Owed'),

                        TextEntry::make('listing_goes_live_at')
                            ->dateTime()
                            ->label('Listing Date'),

                        TextEntry::make('statusUpdatedBy.name')
                            ->label('Last Updated By')
                            ->visible(fn ($record) => $record->status_updated_by),

                        TextEntry::make('status_updated_at')
                            ->dateTime()
                            ->label('Last Updated At')
                            ->visible(fn ($record) => $record->status_updated_at),

                        TextEntry::make('status_notes')
                            ->label('Status Notes')
                            ->markdown()
                            ->visible(fn ($record) => $record->status_notes),
                    ])
                    ->columns(2),

                Tabs::make('Debtor Records')
                    ->tabs([
                        Tabs\Tab::make('Invoices')
                            ->schema([
                                TextEntry::make('invoices_count')
                                    ->label('Total Invoices')
                                    ->state(fn ($record) => $record->invoices->count())
                                    ->size('lg')
                                    ->weight('bold'),

                                RepeatableEntry::make('invoices')
                                    ->schema([
                                        TextEntry::make('invoice_number')
                                            ->label('Invoice #'),

                                        TextEntry::make('invoice_date')
                                            ->date()
                                            ->label('Invoice Date'),

                                        TextEntry::make('due_date')
                                            ->date()
                                            ->label('Due Date'),

                                        TextEntry::make('invoice_amount')
                                            ->money('KES')
                                            ->label('Invoice Amount'),

                                        TextEntry::make('due_amount')
                                            ->money('KES')
                                            ->label('Due Amount'),

                                        TextEntry::make('payment_terms')
                                            ->label('Terms')
                                            ->formatStateUsing(fn ($state) => $state . ' days'),

                                        TextEntry::make('days_overdue')
                                            ->label('Overdue')
                                            ->formatStateUsing(function ($state, $record) {
                                                if ($state <= 0) {
                                                    return 'Not overdue';
                                                }
                                                return $state . ' days overdue';
                                            })
                                            ->color(fn ($record) => $record->days_overdue > 0 ? 'danger' : 'success'),

                                        Infolists\Components\Actions::make([
                                            Infolists\Components\Actions\Action::make('view')
                                                ->icon('heroicon-o-eye')
                                                ->url(fn ($record) => route('filament.client.resources.invoices.view', $record->id))
                                                ->openUrlInNewTab(),

                                            Infolists\Components\Actions\Action::make('edit')
                                                ->icon('heroicon-o-pencil')
                                                ->url(fn ($record) => route('filament.client.resources.invoices.edit', $record->id)),

                                            Infolists\Components\Actions\Action::make('add_document')
                                                ->icon('heroicon-o-document-plus')
                                                ->form([
                                                    \Filament\Forms\Components\Select::make('document_type')
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

                                                    \Filament\Forms\Components\FileUpload::make('file')
                                                        ->label('Upload Document')
                                                        ->directory('debtor-documents')
                                                        ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                                                        ->maxSize(10240)
                                                        ->required(),
                                                ])
                                                ->action(function ($record, array $data) {
                                                    // Create the document using debtor_documents table
                                                    $this->record->documents()->create([
                                                        'type' => $data['document_type'],
                                                        'file_path' => $data['file'],
                                                        'original_filename' => basename($data['file']),
                                                        'uploaded_by' => auth()->id(),
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
                                    ->columns(4),
                            ]),

                        Tabs\Tab::make('Documents')
                            ->schema([
                                TextEntry::make('documents_count')
                                    ->label('Total Documents')
                                    ->state(fn ($record) => $record->documents->count())
                                    ->size('lg')
                                    ->weight('bold'),

                                RepeatableEntry::make('documents')
                                    ->schema([
                                        TextEntry::make('type')
                                            ->label('Document Type')
                                            ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),

                                        TextEntry::make('related_invoice_id')
                                            ->label('Related Invoice')
                                            ->formatStateUsing(function ($state, $record) {
                                                if (!$state) return 'None';

                                                $invoice = \App\Models\Invoice::find($state);
                                                return $invoice ? $invoice->invoice_number : 'Unknown';
                                            }),

                                        TextEntry::make('original_filename')
                                            ->label('Filename')
                                            ->formatStateUsing(fn ($state, $record) => $state ?? basename($record->file_path)),

                                        TextEntry::make('uploader.name')
                                            ->label('Uploaded By'),

                                        TextEntry::make('created_at')
                                            ->label('Upload Date')
                                            ->dateTime(),

                                        IconEntry::make('processing_status')
                                            ->label('Status')
                                            ->icon(fn (string $state): string => match ($state) {
                                                'pending' => 'heroicon-o-clock',
                                                'queued' => 'heroicon-o-queue-list',
                                                'completed' => 'heroicon-o-check-circle',
                                                'failed' => 'heroicon-o-x-circle',
                                                default => 'heroicon-o-question-mark-circle',
                                            })
                                            ->color(fn (string $state): string => match ($state) {
                                                'pending' => 'warning',
                                                'queued' => 'info',
                                                'completed' => 'success',
                                                'failed' => 'danger',
                                                default => 'gray',
                                            }),

                                        Infolists\Components\Actions::make([
                                            Infolists\Components\Actions\Action::make('view')
                                                ->icon('heroicon-o-eye')
                                                ->url(fn ($record) => asset('storage/' . $record->file_path))
                                                ->openUrlInNewTab(),

                                            Infolists\Components\Actions\Action::make('view_invoice')
                                                ->icon('heroicon-o-document-text')
                                                ->label('View Invoice')
                                                ->url(fn ($record) => $record->related_invoice_id ? route('filament.client.resources.invoices.view', $record->related_invoice_id) : null)
                                                ->openUrlInNewTab()
                                                ->visible(fn ($record) => $record->related_invoice_id !== null),
                                        ]),
                                    ])
                                    ->columns(3),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'w-full']),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->url(fn () => $this->getResource()::getUrl('edit', ['record' => $this->record])),

            Action::make('update_status')
                ->label('Update Status')
                ->color('success')
                ->form([
                    \Filament\Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options([
                            DebtorStatus::PENDING->value => DebtorStatus::PENDING->label(),
                            DebtorStatus::ACTIVE->value => DebtorStatus::ACTIVE->label(),
                        ])
                        ->required(),

                    \Filament\Forms\Components\Textarea::make('notes')
                        ->label('Additional Notes')
                        ->placeholder('Enter any additional information')
                        ->maxLength(500),
                ])
                ->action(function (array $data): void {
                    $this->record->update([
                        'status' => $data['status'],
                        'status_notes' => $data['notes'] ?? null,
                        'status_updated_by' => auth()->id(),
                        'status_updated_at' => now(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Status Updated')
                        ->body("The status for {$this->record->name} has been updated to " . DebtorStatus::from($data['status'])->label() . ".")
                        ->success()
                        ->send();
                }),

            Action::make('add_invoice')
                ->label('Add Invoice')
                ->color('primary')
                ->icon('heroicon-o-document-plus')
                ->form([
                    \Filament\Forms\Components\TextInput::make('invoice_number')
                        ->label('Invoice Number')
                        ->required()
                        ->maxLength(100),

                    \Filament\Forms\Components\DatePicker::make('invoice_date')
                        ->label('Invoice Date')
                        ->required(),

                    \Filament\Forms\Components\DatePicker::make('due_date')
                        ->label('Due Date')
                        ->required(),

                    \Filament\Forms\Components\TextInput::make('invoice_amount')
                        ->label('Invoice Amount')
                        ->numeric()
                        ->required()
                        ->prefix('KES'),

                    \Filament\Forms\Components\TextInput::make('due_amount')
                        ->label('Due Amount')
                        ->helperText('Amount still owed on this invoice')
                        ->numeric()
                        ->required()
                        ->prefix('KES'),

                    \Filament\Forms\Components\TextInput::make('payment_terms')
                        ->label('Payment Terms (Days)')
                        ->numeric()
                        ->integer()
                        ->required()
                        ->placeholder('e.g. 30, 60, 90'),
                ])
                ->action(function (array $data): void {
                    $business = Auth::user()->businesses()->first();

                    if ($business) {
                        $invoice = $this->record->invoices()->create([
                            'business_id' => $business->id,
                            'invoice_number' => $data['invoice_number'],
                            'invoice_date' => $data['invoice_date'],
                            'due_date' => $data['due_date'],
                            'invoice_amount' => $data['invoice_amount'],
                            'due_amount' => $data['due_amount'],
                            'payment_terms' => $data['payment_terms'],
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Invoice Added')
                            ->body("Invoice #{$data['invoice_number']} has been added to this debtor.")
                            ->success()
                            ->send();
                    }
                }),

            Action::make('add_document')
                ->label('Add Document')
                ->color('secondary')
                ->icon('heroicon-o-document-plus')
                ->form([
                    \Filament\Forms\Components\Select::make('document_type')
                        ->label('Document Type')
                        ->options(function () {
                            $options = [];
                            foreach (DocumentType::cases() as $type) {
                                $options[$type->value] = $type->label();
                            }
                            return $options;
                        })
                        ->required(),

                    \Filament\Forms\Components\Select::make('related_invoice_id')
                        ->label('Related Invoice (Optional)')
                        ->options(function () {
                            $options = [];
                            foreach ($this->record->invoices as $invoice) {
                                $options[$invoice->id] = $invoice->invoice_number;
                            }
                            return $options;
                        })
                        ->placeholder('Select an invoice or leave empty'),

                    \Filament\Forms\Components\FileUpload::make('file')
                        ->label('Upload Document')
                        ->directory('debtor-documents')
                        ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                        ->maxSize(10240)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $this->record->documents()->create([
                        'type' => $data['document_type'],
                        'file_path' => $data['file'],
                        'original_filename' => basename($data['file']),
                        'uploaded_by' => auth()->id(),
                        'processing_status' => 'pending',
                        'related_invoice_id' => $data['related_invoice_id'] ?? null,
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('Document Added')
                        ->body("The document has been added to {$this->record->name}")
                        ->success()
                        ->send();
                }),
        ];
    }
}
