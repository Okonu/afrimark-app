<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Afrimark Model API URL
    |--------------------------------------------------------------------------
    |
    | This is the URL of the Afrimark Model API service that provides credit scoring
    | and risk assessment for businesses.
    |
    */
    'model_api_url' => env('AFRIMARK_MODEL_API_URL', 'https://afri-model.afrimark.io'),

    /*
    |--------------------------------------------------------------------------
    | Document Processing API URL
    |--------------------------------------------------------------------------
    |
    | This is the URL of the Afrimark Document Processing API that extracts data
    | from uploaded documents such as invoices, contracts, and registration papers.
    |
    */
    'document_api_url' => env('AFRIMARK_DOCUMENT_API_URL', 'https://afri-model.afrimark.io'),

    /*
    |--------------------------------------------------------------------------
    | API Timeout
    |--------------------------------------------------------------------------
    |
    | The maximum time in seconds to wait for API responses.
    |
    */
    'api_timeout' => env('AFRIMARK_API_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Document Processing Queue
    |--------------------------------------------------------------------------
    |
    | The queue to use for document processing jobs.
    |
    */
    'document_processing_queue' => env('AFRIMARK_DOCUMENT_QUEUE', 'document-processing'),

    /*
    |--------------------------------------------------------------------------
    | Session Caching
    |--------------------------------------------------------------------------
    |
    | Credit scores are fetched when a user logs in and cached in the session
    | for the entire duration of the session.
    |
    */
];
