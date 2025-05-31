<?php

namespace App\Domain\EDoreczenia\Controllers;

use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\EDoreczenia\Models\EDoreczeniaCertificate;
use App\Domain\EDoreczenia\Providers\EDoreczeniaProviderManager;
use App\Domain\EDoreczenia\Requests\SearchCertificateRequest;
use App\Domain\EDoreczenia\Requests\StoreCertificateRequest;
use App\Domain\EDoreczenia\Requests\UpdateCertificateRequest;
use App\Domain\EDoreczenia\Resources\EDoreczeniaCertificateResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class CertificateController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct(
        private readonly EDoreczeniaProviderManager $providerManager
    ) {
        $this->authorizeResource(EDoreczeniaCertificate::class, 'certificate');
        $this->modelClass  = EDoreczeniaCertificate::class;
        $this->defaultWith = ['creator'];

        $this->filters = [
            AllowedFilter::exact('status'),
            AllowedFilter::exact('provider'),
        ];

        $this->sorts = [
            'validFrom' => 'valid_from',
            'validTo'   => 'valid_to',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-valid_to';
    }

    public function index(SearchCertificateRequest $request): AnonymousResourceCollection
    {
        $certificates = $this->getIndexPaginator($request);

        return EDoreczeniaCertificateResource::collection($certificates['data'])
            ->additional(['meta' => $certificates['meta']])
        ;
    }

    public function store(StoreCertificateRequest $request): EDoreczeniaCertificateResource
    {
        $certificate = EDoreczeniaCertificate::create([
            'tenant_id'     => $request->user()->tenant_id,
            'user_id'       => $request->user()->id,
            'provider'      => $request->input('provider'),
            'serial_number' => $request->input('serialNumber'),
            'valid_from'    => $request->input('validFrom'),
            'valid_to'      => $request->input('validTo'),
            'status'        => 'active',
        ]);

        if ($request->hasFile('certificate_file')) {
            $certificate->addMediaFromRequest('certificate_file')
                ->toMediaCollection('certificates')
            ;
        }

        return new EDoreczeniaCertificateResource($certificate);
    }

    public function show(EDoreczeniaCertificate $certificate): EDoreczeniaCertificateResource
    {
        return new EDoreczeniaCertificateResource($certificate);
    }

    public function update(UpdateCertificateRequest $request, EDoreczeniaCertificate $certificate): EDoreczeniaCertificateResource
    {
        $certificate->update($request->validated());

        if ($request->hasFile('certificate_file')) {
            $certificate->addMediaFromRequest('certificate_file')
                ->toMediaCollection('certificates')
            ;
        }

        return new EDoreczeniaCertificateResource($certificate);
    }

    public function destroy(EDoreczeniaCertificate $certificate): Response
    {
        $certificate->delete();

        return response()->noContent();
    }
}
