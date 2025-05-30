<?php

namespace App\Domain\EDoreczenia\Controllers;

use App\Domain\EDoreczenia\DTOs\SendMessageDto;
use App\Domain\EDoreczenia\Models\EDoreczeniaMessage;
use App\Domain\EDoreczenia\Providers\EDoreczeniaProviderManager;
use App\Domain\EDoreczenia\Requests\SendMessageRequest;
use App\Domain\EDoreczenia\Requests\UpdateMessageRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
    public function __construct(
        private readonly EDoreczeniaProviderManager $providerManager
    ) {
        $this->authorizeResource(EDoreczeniaMessage::class, 'message');
    }

    /**
     * Display a listing of the messages.
     */
    public function index(Request $request): JsonResponse
    {
        $messages = EDoreczeniaMessage::where('tenant_id', $request->user()->tenant_id)
            ->with(['creator', 'attachments'])
            ->latest()
            ->paginate()
        ;

        return response()->json($messages);
    }

    /**
     * Store a newly created message and send it.
     */
    public function store(SendMessageRequest $request): JsonResponse
    {
        $provider = $this->providerManager->getTenantProvider($request->user()->tenant);

        if (!$provider) {
            return response()->json(
                ['message' => 'No valid certificate found for this tenant'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // Create message DTO
        $messageDto = new SendMessageDto(
            subject: $request->input('subject'),
            content: $request->input('content'),
            recipients: $request->input('recipients'),
            attachments: $request->input('attachments'),
            refToMessageId: $request->input('ref_to_message_id'),
            createdAt: now()
        );

        // Send message through provider
        $result = $provider->send($messageDto);

        if (!$result->success) {
            return response()->json(
                ['message' => $result->error],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // Create message record
        $message = EDoreczeniaMessage::create([
            'tenant_id'   => $request->user()->tenant_id,
            'provider'    => $provider->getProviderName(),
            'external_id' => $result->messageId,
            'content'     => $messageDto->content,
            'created_by'  => $request->user()->id,
        ]);

        // Store attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('edoreczenia/attachments');
                $message->attachments()->create([
                    'filename'  => $file->getClientOriginalName(),
                    'mime_type' => $file->getMimeType(),
                    'size'      => $file->getSize(),
                    'url'       => $path,
                ]);
            }
        }

        return response()->json($message->load(['creator', 'attachments']), Response::HTTP_CREATED);
    }

    /**
     * Display the specified message.
     */
    public function show(EDoreczeniaMessage $message): JsonResponse
    {
        return response()->json($message->load(['creator', 'attachments']));
    }

    /**
     * Update the specified message.
     */
    public function update(UpdateMessageRequest $request, EDoreczeniaMessage $message): JsonResponse
    {
        $message->update($request->validated());

        return response()->json($message);
    }

    /**
     * Remove the specified message.
     */
    public function destroy(EDoreczeniaMessage $message): JsonResponse
    {
        // Delete attachments
        foreach ($message->attachments as $attachment) {
            Storage::delete($attachment->url);
            $attachment->delete();
        }

        $message->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Synchronize messages with the provider.
     */
    public function sync(Request $request): JsonResponse
    {
        $provider = $this->providerManager->getTenantProvider($request->user()->tenant);

        if (!$provider) {
            return response()->json(
                ['message' => 'No valid certificate found for this tenant'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $result = $provider->syncMessages();

        if (!$result->success) {
            return response()->json(
                ['message' => $result->error],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return response()->json(['message' => 'Messages synchronized successfully']);
    }
}
