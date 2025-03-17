<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
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
        $this->timeout = config('afrimark.api_timeout', 30);
    }

    /**
     * Process an invoice document using the API
     *
     * @param string $filePath Path to the file in storage
     * @return array|null
     */
    public function processInvoice(string $filePath): ?array
    {
        return $this->processDocument($filePath, '/invoice-upload/');
    }

    /**
     * Process a contract document using the API
     *
     * @param string $filePath Path to the file in storage
     * @return array|null
     */
    public function processContract(string $filePath): ?array
    {
        return $this->processDocument($filePath, '/contract-upload/');
    }

    /**
     * Process a registration document using the API
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
        if (!Storage::exists($filePath)) {
            Log::error("Document processing failed: File does not exist at path {$filePath}");
            return null;
        }

        try {
            Log::info("Sending document to API: {$filePath} via {$endpoint}");

            $response = Http::timeout($this->timeout)
                ->attach('file', Storage::get($filePath), basename($filePath))
                ->post("{$this->apiUrl}{$endpoint}");

            Log::info("Received API response for {$filePath}, status: {$response->status()}");

            // Store the entire response regardless of success or failure
            $result = [
                'status_code' => $response->status(),
                'response_body' => $response->json() ?: $response->body(),
                'endpoint' => $endpoint,
                'timestamp' => now()->toIso8601String(),
                'request_id' => uniqid('doc_', true),
            ];

            if ($response->successful()) {
                $result['success'] = true;
                Log::info("Document processed successfully via {$endpoint}");
            } else {
                $result['success'] = false;
                Log::error("Document processing failed: API returned status code {$response->status()}", [
                    'response' => $response->body(),
                ]);
            }

            return $result;

        } catch (\Exception $e) {
            Log::error("Document processing error: {$e->getMessage()}", [
                'file_path' => $filePath,
                'endpoint' => $endpoint,
                'exception' => get_class($e),
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
            return $response->successful() && $response->json('status') === 'healthy';
        } catch (\Exception $e) {
            Log::error("API health check failed: {$e->getMessage()}");
            return false;
        }
    }
}
