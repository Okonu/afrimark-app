<?php

namespace App\Jobs;

use App\Enums\DocumentType;
use App\Models\BusinessDocument;
use App\Models\DebtorDocument;
use App\Models\DisputeDocument;
use App\Services\DocumentProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDocumentJob implements ShouldQueue
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
     * The document to process.
     *
     * @var mixed
     */
    protected $document;

    /**
     * Create a new job instance.
     *
     * @param mixed $document
     * @return void
     */
    public function __construct($document)
    {
        $this->document = $document;
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
        $documentType = $this->document instanceof BusinessDocument ? 'business' :
            ($this->document instanceof DebtorDocument ? 'debtor' :
                ($this->document instanceof DisputeDocument ? 'dispute' : 'unknown'));

        Log::info("Processing document job started", [
            'document_id' => $this->document->id,
            'document_type' => $documentType,
            'original_filename' => $this->document->original_filename,
            'file_path' => $this->document->file_path,
            'previous_status' => $this->document->processing_status
        ]);

        try {
            $this->document->processing_status = 'processing';
            $this->document->save();

            Log::info("Document status updated to processing", [
                'document_id' => $this->document->id
            ]);

            $result = null;

            // Route documents to correct endpoints based on the document type
            if ($this->document instanceof BusinessDocument) {
                // All business documents go to reg-docs endpoint
                Log::info("Sending business document to reg-docs endpoint", [
                    'document_id' => $this->document->id,
                    'endpoint' => '/reg-docs-upload/'
                ]);
                $result = $docService->processRegistrationDocument($this->document->file_path);
            } elseif ($this->document instanceof DebtorDocument) {
                // All debtor documents go to invoice-upload endpoint
                Log::info("Sending debtor document to invoice-upload endpoint", [
                    'document_id' => $this->document->id,
                    'endpoint' => '/invoice-upload/'
                ]);
                $result = $docService->processInvoice($this->document->file_path);
            } elseif ($this->document instanceof DisputeDocument) {
                // All dispute documents go to contract-upload endpoint
                Log::info("Sending dispute document to contract-upload endpoint", [
                    'document_id' => $this->document->id,
                    'endpoint' => '/contract-upload/'
                ]);
                $result = $docService->processContract($this->document->file_path);
            } else {
                throw new \Exception("Unknown document type");
            }

            // If we have a result, store it
            if ($result) {
                $this->document->storeApiResponse($result);
                Log::info("Document processed successfully", [
                    'document_id' => $this->document->id,
                    'status_code' => $result['status_code'] ?? 'unknown',
                    'success' => $result['success'] ?? false
                ]);
            } else {
                // Handle failure
                $this->document->processing_status = 'failed';
                $this->document->processing_result = [
                    'success' => false,
                    'error' => 'No processing result returned',
                    'timestamp' => now()->toIso8601String()
                ];
                $this->document->save();

                Log::error("Failed to process document: no result returned", [
                    'document_id' => $this->document->id
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Failed to process document", [
                'document_id' => $this->document->id,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);

            // Update document with error information
            $this->document->processing_status = 'failed';
            $this->document->processing_result = [
                'success' => false,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'timestamp' => now()->toIso8601String()
            ];
            $this->document->save();

            // Rethrow the exception to trigger job retry or failure
            throw $e;
        }
    }
}
