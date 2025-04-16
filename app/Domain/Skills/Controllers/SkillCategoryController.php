<?php

namespace App\Domain\Skills\Controllers;

use App\Domain\Skills\DTO\SkillCategoryDTO;
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
        $dto = SkillCategoryDTO::from($request->validated());
        $category = SkillCategory::create((array) $dto);

        return response()->json(
            SkillCategoryDTO::from($category),
            Response::HTTP_CREATED
        );
    }

    public function show(SkillCategory $category): JsonResponse
    {
        $category->load('skills');
        return response()->json(
            SkillCategoryDTO::from($category)
        );
    }

    public function update(SkillCategoryRequest $request, SkillCategory $category): JsonResponse
    {
        $dto = SkillCategoryDTO::from($request->validated());
        $category->update((array) $dto);

        return response()->json(
            SkillCategoryDTO::from($category)
        );
    }

    public function destroy(SkillCategory $category): JsonResponse
    {
        $category->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
