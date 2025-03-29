<?php

namespace App\Traits;

use App\Services\DocumentProcessingService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

trait DocumentProcessable
{
    /**
     * Boot the trait
     */
    public static function bootDocumentProcessable()
    {
        static::created(function ($document) {
            // Process document immediately if it has a file
            if ($document->file_path && $document->shouldProcess()) {
                $document->processImmediately();
            }
        });
    }

    /**
     * Process the document immediately
     *
     * @return bool
     */
    public function processImmediately(): bool
    {
        if (!$this->shouldProcess()) {
            Log::info("Document ID {$this->id} will not be processed - conditions not met", [
                'document_id' => $this->id,
                'file_exists' => Storage::exists($this->file_path),
                'already_processed' => $this->processed_at !== null || $this->processing_status === 'completed',
                'file_extension' => pathinfo($this->file_path, PATHINFO_EXTENSION)
            ]);
            return false;
        }

        $previousStatus = $this->processing_status;
        $this->processing_status = 'processing';
        $this->save();

        Log::info("Processing document immediately", [
            'document_id' => $this->id,
            'document_type' => $this instanceof \App\Models\BusinessDocument ? 'business' :
                ($this instanceof \App\Models\DebtorDocument ? 'debtor' :
                    ($this instanceof \App\Models\DisputeDocument ? 'dispute' : 'unknown')),
            'file_path' => $this->file_path,
            'previous_status' => $previousStatus
        ]);

        try {
            $docService = app(DocumentProcessingService::class);
            $result = null;

            // Route documents to correct endpoints based on the document type
            if ($this instanceof \App\Models\BusinessDocument) {
                // All business documents go to reg-docs endpoint
                Log::info("Sending business document to reg-docs endpoint", [
                    'document_id' => $this->id,
                    'endpoint' => '/reg-docs-upload/',
                    'file_size' => Storage::size($this->file_path),
                    'file_name' => basename($this->file_path)
                ]);
                $result = $docService->processRegistrationDocument($this->file_path);
            } elseif ($this instanceof \App\Models\DebtorDocument) {
                // All debtor documents go to invoice-upload endpoint
                Log::info("Sending debtor document to invoice-upload endpoint", [
                    'document_id' => $this->id,
                    'endpoint' => '/invoice-upload/',
                    'file_size' => Storage::size($this->file_path),
                    'file_name' => basename($this->file_path)
                ]);
                $result = $docService->processInvoice($this->file_path);
            } elseif ($this instanceof \App\Models\DisputeDocument) {
                // All dispute documents go to contract-upload endpoint
                Log::info("Sending dispute document to contract-upload endpoint", [
                    'document_id' => $this->id,
                    'endpoint' => '/contract-upload/',
                    'file_size' => Storage::size($this->file_path),
                    'file_name' => basename($this->file_path)
                ]);
                $result = $docService->processContract($this->file_path);
            } else {
                throw new \Exception("Unknown document type");
            }

            // Store the result
            if ($result) {
                $this->storeApiResponse($result);
                Log::info("Document processed successfully", [
                    'document_id' => $this->id,
                    'status_code' => $result['status_code'] ?? 'unknown',
                    'success' => $result['success'] ?? false,
                    'response_size' => is_array($result['response_body']) ? count($result['response_body']) : strlen((string)$result['response_body'])
                ]);
                return true;
            } else {
                // Handle failure
                $this->processing_status = 'failed';
                $this->processing_result = [
                    'success' => false,
                    'error' => 'No processing result returned',
                    'timestamp' => now()->toIso8601String()
                ];
                $this->save();

                Log::error("Failed to process document: no result returned", [
                    'document_id' => $this->id
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Error processing document", [
                'document_id' => $this->id,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'file_path' => $this->file_path,
                'trace' => $e->getTraceAsString()
            ]);

            $this->processing_status = 'failed';
            $this->processing_result = [
                'success' => false,
                'error' => $e->getMessage(),
                'exception' => get_class($e),
                'timestamp' => now()->toIso8601String()
            ];
            $this->save();

            return false;
        }
    }

    /**
     * Queue the document for processing (kept for backward compatibility)
     *
     * @return bool
     */
    public function queueForProcessing(): bool
    {
        // Just call the immediate processing method
        return $this->processImmediately();
    }

    /**
     * Determine if the document should be processed
     *
     * @return bool
     */
    public function shouldProcess(): bool
    {
        // Check if the document has already been processed
        if ($this->processed_at || $this->processing_status === 'completed') {
            return false;
        }

        // Check if the file exists
        if (!Storage::exists($this->file_path)) {
            return false;
        }

        // For now, only process PDF files
        $extension = pathinfo($this->file_path, PATHINFO_EXTENSION);
        return strtolower($extension) === 'pdf';
    }

    /**
     * Store the API response
     *
     * @param array $response
     * @return bool
     */
    public function storeApiResponse(array $response): bool
    {
        try {
            $previousStatus = $this->processing_status;
            $this->processing_result = $response;
            $this->processed_at = now();
            $this->processing_status = $response['success'] ? 'completed' : 'failed';
            $this->save();

            Log::info("API response stored for document", [
                'document_id' => $this->id,
                'status' => $this->processing_status,
                'previous_status' => $previousStatus,
                'success' => $response['success'],
                'file_path' => $this->file_path,
                'api_endpoint' => $response['endpoint'] ?? 'unknown',
                'status_code' => $response['status_code'] ?? 'unknown'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to store API response for document", [
                'document_id' => $this->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Check if the document has been processed
     *
     * @return bool
     */
    public function isProcessed(): bool
    {
        return $this->processed_at !== null;
    }

    /**
     * Check if processing was successful
     *
     * @return bool
     */
    public function isProcessingSuccessful(): bool
    {
        if (!$this->isProcessed()) {
            return false;
        }

        return $this->processing_status === 'completed' &&
            isset($this->processing_result['success']) &&
            $this->processing_result['success'] === true;
    }

    /**
     * Get the entire API response
     *
     * @return array|null
     */
    public function getApiResponse(): ?array
    {
        return $this->processing_result;
    }

    /**
     * Get the extracted data from the API response
     *
     * @return mixed
     */
    public function getExtractedData()
    {
        if (!$this->isProcessingSuccessful()) {
            return null;
        }

        // Handle different response formats from different endpoints
        $response = $this->processing_result['response_body'] ?? null;

        if (!$response) {
            return null;
        }

        // Try common response structure patterns
        if (isset($response['extracted_data'])) {
            return $response['extracted_data'];
        }

        if (isset($response['filename']) && isset($response['extracted_data'])) {
            return $response['extracted_data'];
        }

        // For contract endpoint which may return direct text
        if (is_string($response)) {
            return $response;
        }

        // Return the whole response as a fallback
        return $response;
    }

    /**
     * Get the processing status
     *
     * @return string|null
     */
    public function getProcessingStatus(): ?string
    {
        return $this->processing_status;
    }

    /**
     * Get the request ID for the processing request
     *
     * @return string|null
     */
    public function getProcessingRequestId(): ?string
    {
        if (!$this->processing_result) {
            return null;
        }

        return $this->processing_result['request_id'] ?? null;
    }

    /**
     * Get the timestamp when the document was processed by the API
     *
     * @return string|null
     */
    public function getProcessingTimestamp(): ?string
    {
        if (!$this->processing_result) {
            return null;
        }

        return $this->processing_result['timestamp'] ?? null;
    }

    /**
     * Get formatted processing results for display
     *
     * @return array
     */
    public function getFormattedResults(): array
    {
        $data = $this->getExtractedData();

        if (!$data) {
            return [
                'status' => 'No data available',
                'content' => []
            ];
        }

        // If it's already an array, return it
        if (is_array($data)) {
            return [
                'status' => 'Processed successfully',
                'content' => $data
            ];
        }

        // If it's a string (like contract analysis), format it
        if (is_string($data)) {
            return [
                'status' => 'Processed successfully',
                'content' => [
                    'analysis' => $data
                ]
            ];
        }

        // Fallback
        return [
            'status' => 'Data in unknown format',
            'content' => (array)$data
        ];
    }
}
