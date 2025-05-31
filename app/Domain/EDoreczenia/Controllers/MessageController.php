<?php

namespace App\Domain\EDoreczenia\Controllers;

use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\EDoreczenia\Models\EDoreczeniaMessage;
use App\Domain\EDoreczenia\Providers\EDoreczeniaProviderManager;
use App\Domain\EDoreczenia\Requests\SearchMessageRequest;
use App\Domain\EDoreczenia\Requests\SendMessageRequest;
use App\Domain\EDoreczenia\Requests\UpdateMessageRequest;
use App\Domain\EDoreczenia\Resources\EDoreczeniaMessageResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;

class MessageController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct(
        private readonly EDoreczeniaProviderManager $providerManager
    ) {
        $this->authorizeResource(EDoreczeniaMessage::class, 'message');
        $this->modelClass  = EDoreczeniaMessage::class;
        $this->defaultWith = ['creator', 'attachments'];

        $this->filters = [
            AllowedFilter::exact('status'),
            AllowedFilter::exact('provider'),
        ];

        $this->sorts = [
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $this->defaultSort = '-created_at';
    }

    public function index(SearchMessageRequest $request): AnonymousResourceCollection
    {
        $messages = $this->getIndexPaginator($request);

        return EDoreczeniaMessageResource::collection($messages['data'])
            ->additional(['meta' => $messages['meta']])
        ;
    }

    public function store(SendMessageRequest $request): EDoreczeniaMessageResource
    {
        $message = EDoreczeniaMessage::create([
            'tenant_id' => $request->user()->tenant_id,
            'user_id'   => $request->user()->id,
            'provider'  => $request->input('provider'),
            'recipient' => $request->input('recipient'),
            'subject'   => $request->input('subject'),
            'content'   => $request->input('content'),
            'status'    => 'pending',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $message->addMedia($file)
                    ->toMediaCollection('attachments')
                ;
            }
        }

        $this->providerManager->send($message);

        return new EDoreczeniaMessageResource($message);
    }

    public function show(EDoreczeniaMessage $message): EDoreczeniaMessageResource
    {
        return new EDoreczeniaMessageResource($message);
    }

    public function update(UpdateMessageRequest $request, EDoreczeniaMessage $message): EDoreczeniaMessageResource
    {
        $message->update($request->validated());

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $message->addMedia($file)
                    ->toMediaCollection('attachments')
                ;
            }
        }

        return new EDoreczeniaMessageResource($message);
    }

    public function destroy(EDoreczeniaMessage $message): void
    {
        $message->delete();
    }
}
