<?php

namespace App\Jobs;

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

    protected $document;
    protected int $maxAttempts = 3;

    /**
     * Create a new job instance.
     */
    public function __construct($document)
    {
        $this->document = $document;
        $this->onQueue('document-processing');
    }

    /**
     * Execute the job.
     */
    public function handle(DocumentProcessingService $service): void
    {
        Log::info("Processing document: {$this->document->id}");

        try {
            $filePath = $this->document->file_path;
            $documentType = $this->determineDocumentType();
            $result = null;

            // Process the document based on its type
            switch ($documentType) {
                case 'invoice':
                    $result = $service->processInvoice($filePath);
                    break;
                case 'contract':
                    $result = $service->processContract($filePath);
                    break;
                case 'registration':
                default:
                    $result = $service->processRegistrationDocument($filePath);
                    break;
            }

            // Store the result regardless of success/failure
            if ($result) {
                $this->document->storeApiResponse($result);
                Log::info("Document {$this->document->id} processed with API response");
            } else {
                // If we got null back, create a basic error response
                $errorResponse = [
                    'success' => false,
                    'error' => 'No response from service',
                    'timestamp' => now()->toIso8601String(),
                    'request_id' => uniqid('doc_err_', true),
                ];

                $this->document->storeApiResponse($errorResponse);
                Log::error("Document {$this->document->id} processing failed - no response from service");

                // Retry logic
                if ($this->attempts() < $this->maxAttempts) {
                    $this->release(60 * $this->attempts()); // Exponential backoff
                }
            }
        } catch (\Exception $e) {
            Log::error("Error processing document {$this->document->id}: {$e->getMessage()}");

            // Create an error response
            $errorResponse = [
                'success' => false,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'timestamp' => now()->toIso8601String(),
                'request_id' => uniqid('doc_err_', true),
            ];

            $this->document->storeApiResponse($errorResponse);

            // Retry logic
            if ($this->attempts() < $this->maxAttempts) {
                $this->release(60 * $this->attempts()); // Exponential backoff
            }
        }
    }

    /**
     * Determine the document type for processing
     *
     * @return string
     */
    protected function determineDocumentType(): string
    {
        // Try to determine from model class name
        $className = class_basename($this->document);

        if (strpos($className, 'Business') !== false) {
            return 'registration';
        } elseif (strpos($className, 'Debtor') !== false) {
            return 'invoice';
        } elseif (strpos($className, 'Dispute') !== false) {
            return 'contract';
        }

        // Try to determine from filename
        $fileName = strtolower($this->document->original_filename ?? '');

        if (strpos($fileName, 'invoice') !== false) {
            return 'invoice';
        } elseif (strpos($fileName, 'contract') !== false) {
            return 'contract';
        } elseif (strpos($fileName, 'registration') !== false ||
            strpos($fileName, 'certificate') !== false ||
            strpos($fileName, 'incorporation') !== false) {
            return 'registration';
        }

        // Default to registration
        return 'registration';
    }
}
