<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentProcessingService
{
    protected string $apiUrl;
    protected int $timeout;

    public function __construct()
    {
        $this->apiUrl = config('afrimark.document_api_url', 'https://afri-model.afrimark.io');
        $this->timeout = config('afrimark.api_timeout', 120);
    }

    /**
     * Process a debtor document (invoice, payment proof, etc.) using the API
     *
     * @param string $filePath Path to the file in storage
     * @return array|null
     */
    public function processInvoice(string $filePath): ?array
    {
        return $this->processDocument($filePath, '/invoice-upload/');
    }

    /**
     * Process a dispute document (contract, evidence, etc.) using the API
     *
     * @param string $filePath Path to the file in storage
     * @return array|null
     */
    public function processContract(string $filePath): ?array
    {
        return $this->processDocument($filePath, '/contract-upload/');
    }

    /**
     * Process a business document (registration, KRA PIN, etc.) using the API
     *
     * @param string $filePath Path to the file in storage
     * @return array|null
     */
    public function processRegistrationDocument(string $filePath): ?array
    {
        return $this->processDocument($filePath, '/reg-docs-upload/');
    }

    /**
     * Process a document through the API
     *
     * @param string $filePath Path to the file in storage
     * @param string $endpoint API endpoint
     * @return array|null
     */
    protected function processDocument(string $filePath, string $endpoint): ?array
    {
        $fileName = basename($filePath);

        Log::info("Starting document processing", [
            'file_path' => $filePath,
            'file_name' => $fileName,
            'endpoint' => $endpoint,
            'api_url' => $this->apiUrl . $endpoint
        ]);

        if (!Storage::exists($filePath)) {
            Log::error("Document processing failed: File does not exist", [
                'file_path' => $filePath,
                'endpoint' => $endpoint
            ]);
            return [
                'success' => false,
                'error' => 'File not found',
                'status_code' => 404
            ];
        }

        // Check file extension - we only want to process PDFs for now
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            Log::error("Document processing failed: Only PDF files are supported", [
                'file_path' => $filePath,
                'file_extension' => $extension,
                'endpoint' => $endpoint
            ]);
            return [
                'success' => false,
                'error' => 'Only PDF files are supported at this time',
                'status_code' => 400
            ];
        }

        try {
            Log::info("Sending document to API", [
                'file_path' => $filePath,
                'file_size' => Storage::size($filePath),
                'endpoint' => $endpoint,
                'api_url' => $this->apiUrl . $endpoint
            ]);

            // Get the file content and create a temporary file resource
            $tempFile = tmpfile();
            fwrite($tempFile, Storage::get($filePath));
            $tempFilePath = stream_get_meta_data($tempFile)['uri'];

            // Create a file resource for HTTP upload
            $file = fopen($tempFilePath, 'r');

            // Get the original filename
            $originalFilename = basename($filePath);

            // Log API request start time
            $startTime = microtime(true);

            // Send the request with proper file attachment
            $response = Http::timeout($this->timeout)
                ->attach('file', $file, $originalFilename)
                ->post("{$this->apiUrl}{$endpoint}");

            // Calculate request duration
            $duration = round((microtime(true) - $startTime) * 1000); // Duration in milliseconds

            // Close and remove the temp file
            fclose($tempFile);

            Log::info("Received API response", [
                'file_path' => $filePath,
                'status_code' => $response->status(),
                'duration_ms' => $duration,
                'response_size' => strlen($response->body()),
                'endpoint' => $endpoint
            ]);

            // Store the entire response regardless of success or failure
            $result = [
                'status_code' => $response->status(),
                'response_body' => $response->json() ?: $response->body(),
                'endpoint' => $endpoint,
                'timestamp' => now()->toIso8601String(),
                'request_id' => uniqid('doc_', true),
                'duration_ms' => $duration
            ];

            if ($response->successful()) {
                $result['success'] = true;
                Log::info("Document processed successfully", [
                    'file_path' => $filePath,
                    'status_code' => $response->status(),
                    'endpoint' => $endpoint,
                    'duration_ms' => $duration
                ]);
            } else {
                $result['success'] = false;
                Log::error("Document processing failed: API error", [
                    'file_path' => $filePath,
                    'status_code' => $response->status(),
                    'response' => substr($response->body(), 0, 1000), // Log first 1000 chars of response
                    'endpoint' => $endpoint
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("Document processing error: Exception occurred", [
                'file_path' => $filePath,
                'endpoint' => $endpoint,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Also store error responses
            return [
                'status_code' => 500,
                'success' => false,
                'error' => $e->getMessage(),
                'endpoint' => $endpoint,
                'timestamp' => now()->toIso8601String(),
                'request_id' => uniqid('doc_err_', true),
            ];
        }
    }

    /**
     * Check if the API health endpoint is responding
     *
     * @return bool
     */
    public function checkApiHealth(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->apiUrl}/health");
            return $response->successful();
        } catch (\Exception $e) {
            Log::error("API health check failed: {$e->getMessage()}");
            return false;
        }
    }
}
