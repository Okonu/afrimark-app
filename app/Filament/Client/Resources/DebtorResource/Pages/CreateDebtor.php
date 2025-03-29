<?php

namespace App\Filament\Client\Resources\DebtorResource\Pages;

use App\Enums\DebtorStatus;
use App\Filament\Client\Resources\DebtorResource;
use App\Filament\Client\Resources\DebtorResource\DebtorFormSchema;
use App\Jobs\SendDebtorNotification;
use App\Services\Calculations\InvoiceCalculationService;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class CreateDebtor extends CreateRecord
{
    protected static string $resource = DebtorResource::class;

    public $calculatedAmountOwed = 0;

    // Dependency injection via mount method
    public function mount(): void
    {
        parent::mount();
    }

    public function form(Form $form): Form
    {
        return $form->schema(
            DebtorFormSchema::getSchema(
                true,
                function (Get $get, Set $set) {
                    return $this->calculateTotalAmount($get, $set);
                }
            )
        );
    }

    protected function calculateTotalAmount(Get $get, Set $set): float
    {
        $invoices = $get('invoices');
        $total = 0;

        if (is_array($invoices)) {
            foreach ($invoices as $invoice) {
                if (isset($invoice['due_amount']) && is_numeric($invoice['due_amount'])) {
                    $total += floatval($invoice['due_amount']);
                }
            }
        }

        $this->calculatedAmountOwed = $total;
        $set('amount_owed', $total);

        return $total;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            'name' => $data['name'],
            'kra_pin' => $data['kra_pin'],
            'email' => $data['email'],
            'status' => DebtorStatus::PENDING->value,
            'listing_goes_live_at' => now()->addDays(7),
            'verification_token' => Str::random(64),
        ];
    }

    /**
     * Create the debtor record with transaction for database consistency
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function handleRecordCreation(array $data): Model
    {
        // Use a transaction to ensure database consistency
        return DB::transaction(function () use ($data) {
            // Create the debtor first
            $debtor = static::getModel()::create($data);

            return $debtor;
        });
    }

    protected function afterCreate(): void
    {
        try {
            // Wrap all related operations in a transaction
            DB::transaction(function () {
                $business = Auth::user()->businesses()->first();

                if ($business) {
                    // 1. First create the business_debtor relationship with default values
                    // (calculation service will update it after creating invoices)
                    $this->record->businesses()->attach($business->id, [
                        'amount_owed' => 0,
                        'average_payment_terms' => 0,
                        'median_payment_terms' => 0,
                        'average_days_overdue' => 0,
                        'median_days_overdue' => 0,
                        'average_dbt_ratio' => 0,
                        'median_dbt_ratio' => 0,
                    ]);

                    // 2. Prepare invoices data for bulk creation
                    $invoicesData = $this->data['invoices'] ?? [];

                    // 3. Calculate all invoices at once using the optimized service
                    $calculationService = app(InvoiceCalculationService::class);
                    $result = $calculationService->bulkCreateInvoices(
                        $this->record->id,
                        $business->id,
                        $invoicesData
                    );
                }

                // 4. Store document records - bulk insert for efficiency
                if (isset($this->data['documents']) && is_array($this->data['documents'])) {
                    $this->storeDocumentRecords();
                }
            });

            // Queue notification instead of dispatching immediately
            SendDebtorNotification::dispatchWithImmediate($this->record);

            // Show success notification
            Notification::make()
                ->title('Debtor Created')
                ->body('The debtor has been added with ' . count($this->data['invoices'] ?? []) . ' invoice(s) and ' .
                    count($this->data['documents'] ?? []) . ' document group(s).')
                ->success()
                ->send();

        } catch (\Exception $e) {
            // Log the error
            \Illuminate\Support\Facades\Log::error('Error in afterCreate: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'debtor_id' => $this->record->id ?? 'unknown'
            ]);

            // Show error notification
            Notification::make()
                ->title('Error Adding Supporting Information')
                ->body('The debtor was created but there was an error adding some supporting information: ' . $e->getMessage())
                ->warning()
                ->send();
        }
    }

    /**
     * Store document records with minimal overhead
     */
    protected function storeDocumentRecords(): void
    {
        // Prepare all document records for a single bulk insert
        $documentRecords = [];
        $now = now();

        foreach ($this->data['documents'] as $documentGroup) {
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
                }
            }
        }

        // Bulk insert all document records at once (more efficient)
        if (!empty($documentRecords)) {
            DB::table('debtor_documents')->insert($documentRecords);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
