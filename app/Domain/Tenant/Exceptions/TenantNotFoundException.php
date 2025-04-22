<?php

namespace App\Domain\Tenant\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;

class TenantNotFoundException extends \Exception
{
    protected $message = 'Tenant context not found. Please ensure the user is properly authenticated for the correct tenant.';

    public function render($request): JsonResponse
    {
        return ResponseFacade::json([
            'error' => $this->message,
        ], Response::HTTP_FORBIDDEN);
    }
}
