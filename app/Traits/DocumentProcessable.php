<?php

namespace App\Traits;

use App\Jobs\ProcessDocumentJob;
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
            // Queue document for processing if it has a file
            if ($document->file_path && $document->shouldProcess()) {
                $document->queueForProcessing();
            }
        });
    }

    /**
     * Queue the document for processing
     *
     * @return bool
     */
    public function queueForProcessing(): bool
    {
        if (!$this->shouldProcess()) {
            return false;
        }

        $this->processing_status = 'queued';
        $this->save();

        ProcessDocumentJob::dispatch($this);
        Log::info("Document queued for processing: " . $this->id);

        return true;
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

        // Check file extension is supported
        $extension = pathinfo($this->file_path, PATHINFO_EXTENSION);
        $supportedExtensions = ['pdf', 'docx', 'doc', 'xlsx', 'xls'];

        return in_array(strtolower($extension), $supportedExtensions);
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
            $this->processing_result = $response;
            $this->processed_at = now();
            $this->processing_status = $response['success'] ? 'completed' : 'failed';
            $this->save();

            Log::info("API response stored for document {$this->id}, status: {$this->processing_status}");

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to store API response for document {$this->id}: {$e->getMessage()}");
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
}
