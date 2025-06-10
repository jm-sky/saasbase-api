<?php

return [
    'endpoint' => env('AZURE_DOCUMENT_INTELLIGENCE_ENDPOINT', ''),
    'key'      => env('AZURE_DOCUMENT_INTELLIGENCE_KEY', ''),
    'model_id' => env('AZURE_DOCUMENT_MODEL_ID', 'prebuilt-invoice'),
];
