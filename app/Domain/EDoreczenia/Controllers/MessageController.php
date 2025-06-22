<?php

namespace App\Domain\EDoreczenia\Controllers;

use App\Domain\Common\Traits\HasIndexQuery;
use App\Domain\EDoreczenia\DTOs\AttachmentDto;
use App\Domain\EDoreczenia\DTOs\RecipientDto;
use App\Domain\EDoreczenia\DTOs\SendMessageDto;
use App\Domain\EDoreczenia\Models\EDoreczeniaMessage;
use App\Domain\EDoreczenia\Providers\EDoreczeniaProviderManager;
use App\Domain\EDoreczenia\Requests\SearchMessageRequest;
use App\Domain\EDoreczenia\Requests\SendMessageRequest;
use App\Domain\EDoreczenia\Requests\UpdateMessageRequest;
use App\Domain\EDoreczenia\Resources\EDoreczeniaMessageResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Spatie\QueryBuilder\AllowedFilter;
use Symfony\Component\HttpFoundation\Response;

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
        /** @var EDoreczeniaMessage $message */
        $message = EDoreczeniaMessage::create([
            'tenant_id' => $request->user()->tenant_id,
            'user_id'   => $request->user()->id,
            'provider'  => $request->input('provider'),
            'recipient' => $request->input('recipients'),
            'subject'   => $request->input('subject'),
            'content'   => $request->input('content'),
            'status'    => 'pending',
        ]);

        $attachments = collect($request->file('attachments'))
            ->map(function (UploadedFile $file) use ($message) {
                $media = $message->addMedia($file)
                    ->toMediaCollection('attachments')
                ;

                return new AttachmentDto(
                    fileName: $media->file_name,
                    filePath: $media->getPath(),
                    fileSize: $media->size,
                    mimeType: $media->mime_type,
                    createdAt: $media->created_at
                );
            })
        ;

        $recipients = collect($request->input('recipients'))
            ->map(fn (array $recipient) => new RecipientDto(
                email: $recipient['email'],
                name: $recipient['name'],
                identifier: $recipient['identifier']
            ))
        ;

        $sendMessageDto = new SendMessageDto(
            subject: $request->input('subject'),
            content: $request->input('content'),
            recipients: $recipients->all(),
            attachments: $attachments->all(),
            refToMessageId: $request->input('ref_to_message_id'),
            createdAt: $message->created_at
        );

        $provider = $this->providerManager->getProvider($request->input('provider'));

        if (!$provider) {
            abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Provider not found or is not configured.');
        }

        try {
            $result = $provider->send($sendMessageDto);

            $message->update([
                'status'      => $result->success ? 'sent' : 'failed',
                'message_id'  => $result->messageId,
                'sent_at'     => $result->sentAt,
                'status_info' => $result->success ? null : $result->error,
            ]);
        } catch (\Throwable $e) {
            $message->update([
                'status'      => 'failed',
                'status_info' => $e->getMessage(),
            ]);
        }

        return new EDoreczeniaMessageResource($message->refresh());
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
