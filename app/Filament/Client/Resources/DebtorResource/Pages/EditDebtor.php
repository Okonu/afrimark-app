<?php

namespace App\Filament\Client\Resources\DebtorResource\Pages;

use App\Filament\Client\Resources\DebtorResource;
use App\Filament\Client\Resources\DebtorResource\DebtorFormSchema;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class EditDebtor extends EditRecord
{
    protected static string $resource = DebtorResource::class;

    public $calculatedAmountOwed = 0;
    public $existingInvoicesTotal = 0;
    public $newInvoicesTotal = 0;

    public function form(Form $form): Form
    {
        return $form->schema(
            DebtorFormSchema::getSchema(
                false,
                function () {}, // Not used in edit mode
                function (Get $get) {
                    return $this->calculateNewInvoicesTotal($get);
                }
            )
        );
    }

    protected function calculateNewInvoicesTotal(Get $get): void
    {
        $invoices = $get('new_invoices');
        $total = 0;

        if (is_array($invoices)) {
            foreach ($invoices as $invoice) {
                if (isset($invoice['due_amount']) && is_numeric($invoice['due_amount'])) {
                    $total += floatval($invoice['due_amount']);
                }
            }
        }

        $this->newInvoicesTotal = $total;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $business = Auth::user()->businesses()->first();

        if ($business) {
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
        return array_intersect_key($data, array_flip([
            'name',
            'kra_pin',
            'email',
            'status',
        ]));
    }

    protected function afterSave(): void
    {
        try {
            // Use a transaction for all database operations
            DB::transaction(function () {
                $business = Auth::user()->businesses()->first();

                if ($business) {
                    // Calculate the total amount owed (existing + new)
                    $this->existingInvoicesTotal = $this->record->invoices()
                        ->where('business_id', $business->id)
                        ->sum('due_amount');

                    $this->calculatedAmountOwed = $this->existingInvoicesTotal + $this->newInvoicesTotal;

                    // Update the business-debtor relationship
                    $this->record->businesses()->updateExistingPivot($business->id, [
                        'amount_owed' => $this->calculatedAmountOwed,
                    ]);
                }

                // Create invoices (no background processing)
                if (isset($this->data['new_invoices']) && is_array($this->data['new_invoices']) && !empty($this->data['new_invoices'])) {
                    $this->storeNewInvoices($business);
                }

                // Store documents without processing
                if (isset($this->data['new_documents']) && is_array($this->data['new_documents']) && !empty($this->data['new_documents'])) {
                    $this->storeNewDocuments();
                }
            });

            // Show success notifications outside transaction
            $this->showSuccessNotifications();

        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error in afterSave: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'debtor_id' => $this->record->id
            ]);

            // Show error notification
            Notification::make()
                ->title('Error Updating Debtor')
                ->body('Some changes were saved but there was an error updating all information: ' . $e->getMessage())
                ->warning()
                ->send();
        }
    }

    /**
     * Store new invoices efficiently
     */
    protected function storeNewInvoices($business): void
    {
        $invoiceRecords = [];
        $now = now();

        foreach ($this->data['new_invoices'] as $invoiceData) {
            $invoiceRecords[] = [
                'debtor_id' => $this->record->id,
                'business_id' => $business->id,
                'invoice_number' => $invoiceData['invoice_number'],
                'invoice_date' => $invoiceData['invoice_date'],
                'due_date' => $invoiceData['due_date'],
                'invoice_amount' => $invoiceData['invoice_amount'],
                'due_amount' => $invoiceData['due_amount'],
                'payment_terms' => $invoiceData['payment_terms'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Bulk insert all invoices at once
        if (!empty($invoiceRecords)) {
            DB::table('invoices')->insert($invoiceRecords);
        }
    }

    /**
     * Store new documents efficiently
     */
    protected function storeNewDocuments(): void
    {
        $documentCount = 0;
        $documentRecords = [];
        $now = now();

        foreach ($this->data['new_documents'] as $documentGroup) {
            $documentType = $documentGroup['document_type'] ?? null;
            $files = $documentGroup['files'] ?? [];

            if ($documentType && is_array($files) && count($files) > 0) {
                foreach ($files as $filePath) {
                    $documentRecords[] = [
                        'debtor_id' => $this->record->id,
                        'type' => $documentType,
                        'file_path' => $filePath,
                        'original_filename' => basename($filePath),
                        'uploaded_by' => Auth::id(),
                        'processing_status' => 'stored',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                    $documentCount++;
                }
            }
        }

        // Bulk insert all documents at once
        if (!empty($documentRecords)) {
            DB::table('debtor_documents')->insert($documentRecords);
        }
    }

    /**
     * Show success notifications
     */
    protected function showSuccessNotifications(): void
    {
        if (isset($this->data['new_invoices']) && is_array($this->data['new_invoices']) && !empty($this->data['new_invoices'])) {
            Notification::make()
                ->title('Invoices Added')
                ->body(count($this->data['new_invoices']) . ' new invoice(s) have been added to this debtor.')
                ->success()
                ->send();
        }

        if (isset($this->data['new_documents']) && is_array($this->data['new_documents']) && !empty($this->data['new_documents'])) {
            $documentCount = 0;

            foreach ($this->data['new_documents'] as $documentGroup) {
                $files = $documentGroup['files'] ?? [];
                if (is_array($files)) {
                    $documentCount += count($files);
                }
            }

            Notification::make()
                ->title('Documents Added')
                ->body($documentCount . ' new document(s) have been added.')
                ->success()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
