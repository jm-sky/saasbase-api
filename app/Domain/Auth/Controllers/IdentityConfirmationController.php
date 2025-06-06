<?php

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IdentityConfirmationController extends Controller
{
    /**
     * POST /api/identity/confirmation/template
     * Generates a PDF for identity confirmation and returns file URL and expiry.
     */
    public function generateTemplate(Request $request): JsonResponse
    {
        // TODO: Implement PDF generation, store in media library, return file_url and expires_at
        return response()->json([
            'file_url'   => null,
            'expires_at' => null,
        ]);
    }

    /**
     * POST /api/identity/confirmation/submit
     * Accepts signed PDF, verifies signature, stores result, returns status and details.
     */
    public function submitSigned(Request $request): JsonResponse
    {
        // TODO: Implement file upload, signature verification, data comparison, media storage, and response
        return response()->json([
            'status'    => 'pending',
            'confirmed' => [
                'full_name'  => false,
                'pesel'      => false,
                'birth_date' => false,
            ],
            'signature_info' => null,
        ]);
    }
}
