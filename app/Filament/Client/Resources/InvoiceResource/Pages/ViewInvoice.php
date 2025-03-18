<?php

namespace App\Filament\Client\Resources\InvoiceResource\Pages;

use App\Enums\DocumentType;
use App\Filament\Client\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Invoice Details')
                    ->schema([
                        TextEntry::make('invoice_number')
                            ->label('Invoice Number'),

                        TextEntry::make('debtor.name')
                            ->label('Debtor'),

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
                            ->label('Payment Terms')
                            ->formatStateUsing(fn ($state) => $state . ' days'),

                        TextEntry::make('days_overdue')
                            ->label('Days Overdue')
                            ->formatStateUsing(function ($state) {
                                if ($state <= 0) {
                                    return 'Not overdue';
                                }
                                return $state . ' days overdue';
                            })
                            ->color(fn ($record) => $record->days_overdue > 0 ? 'danger' : 'success'),
                    ])
                    ->columns(2),

                Section::make('Documents')
                    ->schema([
                        RepeatableEntry::make('documents')
                            ->hiddenLabel()
                            ->schema([
                                TextEntry::make('type')
                                    ->label('Document Type')
                                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),

                                TextEntry::make('file_path')
                                    ->label('Document')
                                    ->url(fn ($record) => $record->file_path ? asset('storage/' . $record->file_path) : null)
                                    ->openUrlInNewTab()
                                    ->formatStateUsing(fn ($record) => $record->original_filename ?? basename($record->file_path)),

                                TextEntry::make('uploader.name')
                                    ->label('Uploaded By'),

                                TextEntry::make('created_at')
                                    ->label('Upload Date')
                                    ->dateTime(),

                                IconEntry::make('processing_status')
                                    ->label('Processing Status')
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
                            ])
                            ->columns(3),
                    ])
                    ->visible(fn ($record) => $record->documents()->exists())
                    ->collapsible(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Actions\Action::make('add_document')
                ->label('Add Document')
                ->color('primary')
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
                ->action(function (array $data) {
                    // Create document in the debtor_documents table
                    $this->record->debtor->documents()->create([
                        'type' => $data['document_type'],
                        'file_path' => $data['file'],
                        'original_filename' => basename($data['file']),
                        'uploaded_by' => Auth::id(),
                        'processing_status' => 'pending',
                        'related_invoice_id' => $this->record->id,
                    ]);

                    Notification::make()
                        ->title('Document Added')
                        ->body("Document has been added to Invoice #{$this->record->invoice_number}")
                        ->success()
                        ->send();
                }),

            Actions\Action::make('view_debtor')
                ->label('View Debtor')
                ->color('secondary')
                ->icon('heroicon-o-user')
                ->url(fn () => route('filament.client.resources.debtors.view', $this->record->debtor_id))
                ->openUrlInNewTab(),
        ];
    }
}
