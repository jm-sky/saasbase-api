<?php

namespace App\Domain\Contractors\Controllers;

use App\Domain\Common\DTOs\TagDTO;
use App\Domain\Common\Requests\TagRequest;
use App\Domain\Contractors\Models\Contractor;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ContractorTagsController
{
    public function index(Contractor $contractor): JsonResponse
    {
        $tags = $contractor->getTagNames();

        return response()->json([
            'data' => $tags,
        ]);
    }

    public function store(TagRequest $request, Contractor $contractor): JsonResponse
    {
        $tag = $contractor->addTag($request->input('tag'), $contractor->tenant_id);

        return response()->json([
            'data' => TagDTO::fromModel($tag)->toArray(),
        ], Response::HTTP_CREATED);
    }

    public function destroy(Contractor $contractor, string $tag): JsonResponse
    {
        $contractor->removeTag($tag, $contractor->tenant_id);

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    public function sync(TagRequest $request, Contractor $contractor): JsonResponse
    {
        $tags = $request->input('tags', []);
        $contractor->syncTags($tags, $contractor->tenant_id);

        return response()->json([
            'data' => $contractor->getTagNames(),
        ]);
    }
}
