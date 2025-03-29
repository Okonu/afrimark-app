<?php

namespace App\Jobs;

use App\Models\Debtor;
use App\Services\DocumentProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDebtorDocuments implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [30, 60, 120];

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * The debtor instance.
     *
     * @var \App\Models\Debtor
     */
    protected $debtor;

    /**
     * The document groups to process.
     *
     * @var array
     */
    protected $documentGroups;

    /**
     * The ID of the user who uploaded the documents.
     *
     * @var int
     */
    protected $uploadedBy;

    /**
     * Indicates if the job should be marked as failed on timeout.
     *
     * @var bool
     */
    public $failOnTimeout = true;

    /**
     * The maximum number of files to process per batch
     *
     * @var int
     */
    protected $batchSize = 2;

    /**
     * Create a new job instance.
     *
     * @param \App\Models\Debtor $debtor
     * @param array $documentGroups
     * @param int $uploadedBy
     * @return void
     */
    public function __construct(Debtor $debtor, array $documentGroups, int $uploadedBy)
    {
        $this->debtor = $debtor;
        $this->documentGroups = $documentGroups;
        $this->uploadedBy = $uploadedBy;
        $this->onQueue('document-processing');
    }

    /**
     * Execute the job.
     *
     * @param \App\Services\DocumentProcessingService $docService
     * @return void
     */
    public function handle(DocumentProcessingService $docService)
    {
        $documentCount = 0;
        $processedCount = 0;
        $failedCount = 0;

        Log::info("Processing documents for debtor: {$this->debtor->id}", [
            'document_groups' => count($this->documentGroups),
            'debtor_name' => $this->debtor->name,
        ]);

        try {
            foreach ($this->documentGroups as $documentGroup) {
                $documentType = $documentGroup['document_type'] ?? null;
                $files = $documentGroup['files'] ?? [];

                if ($documentType && is_array($files) && count($files) > 0) {
                    // Process files in smaller batches
                    $batches = array_chunk($files, $this->batchSize);

                    foreach ($batches as $batchIndex => $batchFiles) {
                        // For each batch, create a separate job
                        $this->processBatch($documentType, $batchFiles, $documentCount, $processedCount, $failedCount);
                    }
                }
            }

            Log::info("Document processing initialization completed for debtor: {$this->debtor->id}", [
                'total' => $documentCount,
                'queued' => $processedCount,
                'failed' => $failedCount,
            ]);
        } catch (\Exception $e) {
            Log::error("Error processing documents for debtor {$this->debtor->id}: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            throw $e;
        }
    }

    /**
     * Process a batch of files within a document group
     *
     * @param string $documentType
     * @param array $batchFiles
     * @param int &$documentCount
     * @param int &$processedCount
     * @param int &$failedCount
     * @return void
     */
    protected function processBatch($documentType, array $batchFiles, int &$documentCount, int &$processedCount, int &$failedCount): void
    {
        foreach ($batchFiles as $file) {
            $documentCount++;

            try {
                // Create document record with minimal information
                $document = $this->debtor->documents()->create([
                    'type' => $documentType,
                    'file_path' => $file,
                    'original_filename' => basename($file),
                    'uploaded_by' => $this->uploadedBy,
                    'processing_status' => 'pending',
                ]);

                // Create a separate job to process each individual document
                ProcessDocumentJob::dispatch($document)
                    ->onQueue('document-processing');

                $processedCount++;
                Log::info("Document created and queued for processing: {$document->id}");
            } catch (\Exception $e) {
                $failedCount++;
                Log::error("Failed to process document for debtor {$this->debtor->id}: " . $e->getMessage());
            }
        }
    }
}
