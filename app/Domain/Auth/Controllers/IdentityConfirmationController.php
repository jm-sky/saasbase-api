<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Auth\Models\UserPersonalData;
use App\Domain\Auth\Requests\SubmitSignedIdentityConfirmationRequest;
use App\Domain\Common\Models\Media;
use App\Http\Controllers\Controller;
use App\Services\PdfSignatureVerifierService;
use App\Services\XmlValidatorService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class IdentityConfirmationController extends Controller
{
    /**
     * POST /api/identity/confirmation/template
     * Generates an XML for identity confirmation and returns file URL and expiry.
     */
    public function generateTemplate(Request $request): BinaryFileResponse
    {
        /** @var User $user */
        $user    = Auth::user();
        $token   = (string) Str::ulid();

        $media = $user->getFirstMedia('identity_confirmation_template');

        if (!$media) {
            $media = $this->generateAndStoreTemplateXml($user, $token);
        }

        $path     = $media->getPath();
        $filename = $media->file_name;

        return response()->download($path, $filename, [
            'Content-Type'        => 'application/xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * POST /api/identity/confirmation/submit
     * Accepts signed XML, verifies signature, stores result, returns status and details.
     */
    public function submitSigned(SubmitSignedIdentityConfirmationRequest $request, XmlValidatorService $xmlValidator): JsonResponse
    {
        /** @var User $user */
        $user       = Auth::user();
        $file       = $request->file('file');
        $xmlContent = file_get_contents($file->getPathname());

        // 1. Validate XML against XSD using XmlValidatorService
        $xsdPath = public_path('xml/identity/v1/identity-confirmation.xsd');

        try {
            $xmlValidator->validate($xmlContent, $xsdPath);
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 'invalid_xml', 'error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 2. Verify signature (reuse PdfSignatureVerifierService for CMS signature)
        $verifier     = app(PdfSignatureVerifierService::class);
        $verifyResult = $verifier->verifySignature($file->getPathname());

        if (empty($verifyResult['valid'])) {
            return response()->json([
                'status'         => 'invalid_signature',
                'error'          => $verifyResult['error'] ?? 'Signature invalid',
                'signature_info' => $verifyResult['details'] ?? null,
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // 3. Extract data from XML
        $xml  = simplexml_load_string($xmlContent);
        $ns   = $xml->getNamespaces(true);
        $data = [
            'first_name'         => (string) $xml->FirstName,
            'last_name'          => (string) $xml->LastName,
            'full_name'          => (string) $xml->FullName,
            'birth_date'         => (string) $xml->BirthDate,
            'pesel'              => (string) $xml->PESEL,
            'generated_at'       => (string) $xml->GeneratedAt,
            'confirmation_token' => (string) $xml->ConfirmationToken,
            'application_name'   => (string) $xml->ApplicationName,
        ];

        // 4. Compare with user profile
        $confirmed = [
            'full_name'  => trim($user->first_name . ' ' . $user->last_name) === trim($data['full_name']),
            'pesel'      => $user->pesel && $user->pesel === $data['pesel'],
            'birth_date' => $user->birth_date && Carbon::parse($user->birth_date)->toDateString() === $data['birth_date'],
        ];

        // 5. Optionally create/update UserPersonalData if pesel is missing but present in signature
        if (!$user->pesel && $data['pesel']) {
            $personalData = UserPersonalData::updateOrCreate(
                ['user_id' => $user->id],
                ['pesel' => $data['pesel']]
            );
        }

        // 6. Store signed XML in identity_confirmation_final, remove previous template
        $signedMedia = $user->addMedia($file)
            ->usingFileName('identity_confirmation_' . $user->id . '_' . time() . '.xml')
            ->toMediaCollection('identity_confirmation_final')
        ;

        $user->clearMediaCollection('identity_confirmation_template');

        return response()->json([
            'status'         => ($confirmed['full_name'] && $confirmed['pesel'] && $confirmed['birth_date']) ? 'verified' : 'unverified',
            'confirmed'      => $confirmed,
            'signatureInfo'  => $verifyResult['details'] ?? null,
        ]);
    }

    /**
     * Helper to generate XML for identity confirmation.
     */
    protected function generateIdentityXml(array $data): string
    {
        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><IdentityConfirmation xmlns="https://saasbase.madeyski.org/xml/identity/v1"></IdentityConfirmation>');

        foreach ($data as $key => $value) {
            $xml->addChild($key, htmlspecialchars($value));
        }

        return $xml->asXML();
    }

    protected function generateAndStoreTemplateXml(User $user, string $token): Media
    {
        $now    = Carbon::now('UTC');
        $xml    = $this->generateTemplateXml($user, $token);
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $xml);
        rewind($stream);

        $media = $user->addMediaFromStream($stream)
            ->usingFileName('identity_confirmation_' . $user->id . '_' . $now->timestamp . '.xml')
            ->withCustomProperties(['token' => $token])
            ->toMediaCollection('identity_confirmation_template')
        ;
        fclose($stream);

        return $media;
    }

    protected function generateTemplateXml(User $user, string $token): string
    {
        $now     = Carbon::now('UTC');
        $appName = config('app.name');

        $fullName  = $user->full_name;
        $birthDate = $user->profile?->birth_date ? Carbon::parse($user->profile->birth_date)->toDateString() : '';
        $pesel     = $user->personalData?->pesel ?? '';

        return $this->generateIdentityXml([
            'FirstName'         => $user->first_name,
            'LastName'          => $user->last_name,
            'FullName'          => $fullName,
            'BirthDate'         => $birthDate,
            'PESEL'             => $pesel,
            'GeneratedAt'       => $now->toIso8601String(),
            'ConfirmationToken' => $token,
            'ApplicationName'   => $appName,
        ]);
    }
}
