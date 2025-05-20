<?php

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\UserIdentityDocument;
use App\Domain\Auth\Models\UserPersonalData;
use App\Domain\Auth\Requests\StoreUserIdentityDocumentRequest;
use App\Domain\Auth\Requests\StoreUserPersonalDataRequest;
use App\Domain\Auth\Resources\UserIdentityDocumentResource;
use App\Domain\Auth\Resources\UserPersonalDataResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserIdentityController extends Controller
{
    public function storePersonalData(StoreUserPersonalDataRequest $request): JsonResponse
    {
        $personalData = UserPersonalData::updateOrCreate(
            ['user_id' => Auth::id()],
            $request->validated()
        );

        return response()->json(new UserPersonalDataResource($personalData), 201);
    }

    public function getPersonalData(): JsonResponse
    {
        $personalData = UserPersonalData::where('user_id', Auth::id())->first();

        return response()->json(new UserPersonalDataResource($personalData));
    }

    public function storeIdentityDocument(StoreUserIdentityDocumentRequest $request): JsonResponse
    {
        $document = UserIdentityDocument::create([
            'user_id' => Auth::id(),
            ...$request->validated(),
        ]);

        if ($request->hasFile('document_image')) {
            $document->addMediaFromRequest('document_image')
                ->toMediaCollection('document_images')
            ;
        }

        return response()->json(new UserIdentityDocumentResource($document), 201);
    }

    public function getIdentityDocuments(): JsonResponse
    {
        $documents = UserIdentityDocument::where('user_id', Auth::id())->get();

        return response()->json(UserIdentityDocumentResource::collection($documents));
    }

    public function getIdentityDocument(UserIdentityDocument $document): JsonResponse
    {
        if ($document->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(new UserIdentityDocumentResource($document));
    }
}
