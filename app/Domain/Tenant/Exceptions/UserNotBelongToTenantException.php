<?php

namespace App\Domain\Tenant\Exceptions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Response as ResponseFacade;

class UserNotBelongToTenantException extends \Exception
{
    protected $message = 'User does not belong to tenant.';

    public function render($request): JsonResponse
    {
        return ResponseFacade::json([
            'error' => $this->message,
        ], Response::HTTP_FORBIDDEN);
    }
}
