<?php

namespace App\Domain\Skills\Controllers;

use App\Domain\Skills\DTOs\SkillDTO;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Requests\SkillRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SkillController extends Controller
{
    public function index(): JsonResponse
    {
        $skills = Skill::with('category')->paginate();
        return response()->json(
            SkillDTO::collect($skills)
        );
    }

    public function store(SkillRequest $request): JsonResponse
    {
        $dto = SkillDTO::from($request->validated());
        $skill = Skill::create((array) $dto);

        return response()->json(
            SkillDTO::from($skill),
            Response::HTTP_CREATED
        );
    }

    public function show(Skill $skill): JsonResponse
    {
        $skill->load('category');
        return response()->json(
            SkillDTO::from($skill)
        );
    }

    public function update(SkillRequest $request, Skill $skill): JsonResponse
    {
        $dto = SkillDTO::from($request->validated());
        $skill->update((array) $dto);

        return response()->json(
            SkillDTO::from($skill)
        );
    }

    public function destroy(Skill $skill): JsonResponse
    {
        $skill->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
