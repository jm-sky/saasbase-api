<?php

namespace App\Domain\EDoreczenia\Controllers;

use App\Domain\EDoreczenia\DTOs\CertificateInfoDto;
use App\Domain\EDoreczenia\Models\EDoreczeniaCertificate;
use App\Domain\EDoreczenia\Providers\EDoreczeniaProviderManager;
use App\Domain\EDoreczenia\Requests\StoreCertificateRequest;
use App\Domain\EDoreczenia\Requests\UpdateCertificateRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CertificateController extends Controller
{
    public function __construct(
        private readonly EDoreczeniaProviderManager $providerManager
    ) {
        $this->authorizeResource(EDoreczeniaCertificate::class, 'certificate');
    }

    /**
     * Display a listing of the certificates.
     */
    public function index(Request $request): JsonResponse
    {
        $certificates = EDoreczeniaCertificate::where('tenant_id', $request->user()->tenant_id)
            ->with('creator')
            ->get()
        ;

        return response()->json($certificates);
    }

    /**
     * Store a newly created certificate.
     */
    public function store(StoreCertificateRequest $request): JsonResponse
    {
        $file     = $request->file('certificate');
        $password = $request->input('password');

        // Store the certificate file
        $path = $file->store(config('edoreczenia.certificates.storage_path'));

        // Create certificate info DTO
        $certificateInfo = new CertificateInfoDto(
            fingerprint: $request->input('fingerprint'),
            subjectCn: $request->input('subject_cn'),
            validFrom: $request->input('valid_from'),
            validTo: $request->input('valid_to')
        );

        // Verify certificate with provider
        $provider = $this->providerManager->getProvider($request->input('provider'));

        if (!$provider || !$provider->verifyCertificate($certificateInfo)) {
            Storage::delete($path);

            return response()->json(['message' => 'Invalid certificate'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Create certificate record
        $certificate = EDoreczeniaCertificate::create([
            'tenant_id'   => $request->user()->tenant_id,
            'provider'    => $request->input('provider'),
            'file_path'   => $path,
            'fingerprint' => $certificateInfo->fingerprint,
            'subject_cn'  => $certificateInfo->subjectCn,
            'valid_from'  => $certificateInfo->validFrom,
            'valid_to'    => $certificateInfo->validTo,
            'is_valid'    => true,
            'created_by'  => $request->user()->id,
        ]);

        return response()->json($certificate, Response::HTTP_CREATED);
    }

    /**
     * Display the specified certificate.
     */
    public function show(EDoreczeniaCertificate $certificate): JsonResponse
    {
        return response()->json($certificate->load('creator'));
    }

    /**
     * Update the specified certificate.
     */
    public function update(UpdateCertificateRequest $request, EDoreczeniaCertificate $certificate): JsonResponse
    {
        $certificate->update($request->validated());

        return response()->json($certificate);
    }

    /**
     * Remove the specified certificate.
     */
    public function destroy(EDoreczeniaCertificate $certificate): JsonResponse
    {
        Storage::delete($certificate->file_path);
        $certificate->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
