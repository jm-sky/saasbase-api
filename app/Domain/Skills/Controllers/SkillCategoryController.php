<?php

namespace App\Domain\Skills\Controllers;

use App\Domain\Skills\DTOs\SkillCategoryDTO;
use App\Domain\Skills\Models\SkillCategory;
use App\Domain\Skills\Requests\SkillCategoryRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SkillCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = SkillCategory::with('skills')->paginate();

        return response()->json(
            SkillCategoryDTO::collect($categories)
        );
    }

    public function store(SkillCategoryRequest $request): JsonResponse
    {
        $dto      = SkillCategoryDTO::from($request->validated());
        $category = SkillCategory::create([
            'name'        => $dto->name,
            'description' => $dto->description,
        ]);

        return response()->json(
            ['data' => SkillCategoryDTO::fromModel($category)],
            Response::HTTP_CREATED
        );
    }

    public function show(SkillCategory $category): JsonResponse
    {
        abort_if(!$category->exists(), Response::HTTP_NOT_FOUND);

        $category->load('skills');

        return response()->json(['data' => SkillCategoryDTO::fromModel($category)]);
    }

    public function update(SkillCategoryRequest $request, SkillCategory $category): JsonResponse
    {
        abort_if(!$category->exists(), Response::HTTP_NOT_FOUND);

        $dto = SkillCategoryDTO::from($request->validated());
        $category->update([
            'name'        => $dto->name,
            'description' => $dto->description,
        ]);

        return response()->json(['data' => SkillCategoryDTO::fromModel($category)]);
    }

    public function destroy(SkillCategory $category): JsonResponse
    {
        abort_if(!$category->exists(), Response::HTTP_NOT_FOUND);

        $category->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
