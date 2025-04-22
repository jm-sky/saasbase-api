<?php

namespace App\Domain\Skills\Controllers;

use App\Domain\Skills\DTOs\UserSkillDTO;
use App\Domain\Skills\Models\UserSkill;
use App\Domain\Skills\Requests\UserSkillRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class UserSkillController extends Controller
{
    public function index(): JsonResponse
    {
        $userSkills = UserSkill::with(['user', 'skill'])->get();

        return response()->json(
            $userSkills->map(fn (UserSkill $skill) => UserSkillDTO::fromModel($skill))
        );
    }

    public function store(UserSkillRequest $request): JsonResponse
    {
        $dto       = UserSkillDTO::from($request->validated());
        $userSkill = UserSkill::create([
            'user_id'     => $dto->userId,
            'skill_id'    => $dto->skillId,
            'level'       => $dto->level,
            'acquired_at' => $dto->acquiredAt,
        ]);

        return response()->json(
            UserSkillDTO::fromModel($userSkill),
            Response::HTTP_CREATED
        );
    }

    public function show(UserSkill $userSkill): JsonResponse
    {
        $userSkill->load(['user', 'skill']);

        return response()->json(
            UserSkillDTO::fromModel($userSkill)
        );
    }

    public function update(UserSkillRequest $request, UserSkill $userSkill): JsonResponse
    {
        $dto = UserSkillDTO::from($request->validated());
        $userSkill->update([
            'user_id'     => $dto->userId,
            'skill_id'    => $dto->skillId,
            'level'       => $dto->level,
            'acquired_at' => $dto->acquiredAt,
        ]);

        return response()->json(
            UserSkillDTO::fromModel($userSkill)
        );
    }

    public function destroy(UserSkill $userSkill): JsonResponse
    {
        $userSkill->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
