<?php

namespace App\Domain\Skills\Controllers;

use App\Domain\Skills\DTO\UserSkillDTO;
use App\Domain\Skills\Models\UserSkill;
use App\Domain\Skills\Requests\UserSkillRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserSkillController extends Controller
{
    public function index(): JsonResponse
    {
        $userSkills = UserSkill::with(['user', 'skill'])->paginate();
        return response()->json(
            UserSkillDTO::collect($userSkills)
        );
    }

    public function store(UserSkillRequest $request): JsonResponse
    {
        $dto = UserSkillDTO::from($request->validated());
        $userSkill = UserSkill::create((array) $dto);

        return response()->json(
            UserSkillDTO::from($userSkill),
            Response::HTTP_CREATED
        );
    }

    public function show(UserSkill $userSkill): JsonResponse
    {
        $userSkill->load(['user', 'skill']);
        return response()->json(
            UserSkillDTO::from($userSkill)
        );
    }

    public function update(UserSkillRequest $request, UserSkill $userSkill): JsonResponse
    {
        $dto = UserSkillDTO::from($request->validated());
        $userSkill->update((array) $dto);

        return response()->json(
            UserSkillDTO::from($userSkill)
        );
    }

    public function destroy(UserSkill $userSkill): JsonResponse
    {
        $userSkill->delete();
        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
