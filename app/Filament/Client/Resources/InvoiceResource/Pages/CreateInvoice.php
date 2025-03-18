<?php

namespace App\Filament\Client\Resources\InvoiceResource\Pages;

use App\Enums\DocumentType;
use App\Filament\Client\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['business_id'] = Auth::user()->businesses()->first()?->id;

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

    protected function afterCreate(): void
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
        return $this->getResource()::getUrl('index');
    }
}
