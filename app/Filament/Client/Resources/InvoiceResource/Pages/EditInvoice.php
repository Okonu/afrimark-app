<?php

namespace App\Filament\Client\Resources\InvoiceResource\Pages;

use App\Enums\DocumentType;
use App\Filament\Client\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
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
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pre-fill the checkboxes
        $data['liability_confirmation'] = true;
        $data['terms_accepted'] = true;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!isset($data['due_amount']) && isset($data['invoice_amount'])) {
            $data['due_amount'] = $data['invoice_amount'];
        }

        // Remove the documents data as it's not part of the Invoice model
        if (isset($data['documents'])) {
            $this->documents = $data['documents'];
            unset($data['documents']);
        }

        // Remove terms & liability fields as they're not stored in the database
        if (isset($data['liability_confirmation'])) {
            unset($data['liability_confirmation']);
        }

        if (isset($data['terms_accepted'])) {
            unset($data['terms_accepted']);
        }

        if (isset($data['disclaimer'])) {
            unset($data['disclaimer']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        // Handle the documents if provided
        if (isset($this->documents) && is_array($this->documents)) {
            foreach ($this->documents as $document) {
                // Skip if missing required fields
                if (!isset($document['document_type']) || !isset($document['file'])) {
                    continue;
                }

                $this->record->debtor->documents()->create([
                    'type' => $document['document_type'],
                    'file_path' => $document['file'],
                    'original_filename' => basename($document['file']),
                    'uploaded_by' => Auth::id(),
                    'processing_status' => 'pending',
                    'related_invoice_id' => $this->record->id,
                ]);
            }

            $count = count($this->documents);
            Notification::make()
                ->title('Documents Uploaded')
                ->body("$count document(s) have been attached to this invoice.")
                ->success()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
