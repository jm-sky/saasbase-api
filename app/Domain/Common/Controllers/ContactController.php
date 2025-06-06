<?php

namespace App\Domain\Common\Controllers;

use App\Domain\Common\Filters\AdvancedFilter;
use App\Domain\Common\Filters\ComboSearchFilter;
use App\Domain\Common\Models\Contact;
use App\Domain\Common\Requests\ContactRequest;
use App\Domain\Common\Resources\ContactResource;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Spatie\QueryBuilder\AllowedFilter;

class ContactController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = Contact::class;
        $this->filters    = [
            AllowedFilter::custom('search', new ComboSearchFilter([
                'first_name',
                'last_name',
                'email',
                'phone_number',
                'position',
                'notes',
            ])),
            AllowedFilter::custom('firstName', new AdvancedFilter(), 'first_name'),
            AllowedFilter::custom('lastName', new AdvancedFilter(), 'last_name'),
            AllowedFilter::custom('email', new AdvancedFilter()),
            AllowedFilter::custom('phoneNumber', new AdvancedFilter(), 'phone_number'),
            AllowedFilter::custom('position', new AdvancedFilter()),
            AllowedFilter::custom('userId', new AdvancedFilter(), 'user_id'),
            AllowedFilter::custom('contactableId', new AdvancedFilter(), 'contactable_id'),
            AllowedFilter::custom('contactableType', new AdvancedFilter(), 'contactable_type'),
            AllowedFilter::custom('createdAt', new AdvancedFilter(), 'created_at'),
            AllowedFilter::custom('updatedAt', new AdvancedFilter(), 'updated_at'),
        ];
        $this->sorts = [
            'firstName'     => 'first_name',
            'lastName'      => 'last_name',
            'email',
            'phoneNumber'   => 'phone_number',
            'position',
            'createdAt'     => 'created_at',
            'updatedAt'     => 'updated_at',
        ];
        $this->defaultSort = '-created_at';
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $contacts = $this->getIndexQuery($request)->paginate();

        return ContactResource::collection($contacts)
            ->additional([
                'meta' => [
                    'currentPage' => $contacts->currentPage(),
                    'lastPage'    => $contacts->lastPage(),
                    'perPage'     => $contacts->perPage(),
                    'total'       => $contacts->total(),
                ],
            ])
        ;
    }

    public function store(ContactRequest $request): JsonResponse
    {
        $contact = Contact::create($request->validated());

        return response()->json([
            'message' => 'Contact created successfully.',
            'data'    => new ContactResource($contact),
        ], Response::HTTP_CREATED);
    }

    public function show(Contact $contact): ContactResource
    {
        return new ContactResource($contact);
    }

    public function update(ContactRequest $request, Contact $contact): JsonResponse
    {
        $contact->update($request->validated());

        return response()->json([
            'message' => 'Contact updated successfully.',
            'data'    => new ContactResource($contact->fresh()),
        ]);
    }

    public function destroy(Contact $contact): Response
    {
        $contact->delete();

        return response()->noContent();
    }

    public function search(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $query   = $request->input('q');
        $perPage = $request->input('perPage', $this->defaultPerPage);

        if (!$query) {
            return response()->json(['message' => 'Search query is required'], Response::HTTP_BAD_REQUEST);
        }

        $results = Contact::search($query)
            ->query(function ($builder) use ($request) {
                return $this->getIndexQuery($request);
            })
            ->paginate($perPage)
        ;

        return ContactResource::collection($results)
            ->additional([
                'meta' => [
                    'currentPage' => $results->currentPage(),
                    'lastPage'    => $results->lastPage(),
                    'perPage'     => $results->perPage(),
                    'total'       => $results->total(),
                ],
            ])
        ;
    }
}
