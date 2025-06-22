<?php

namespace App\Domain\Skills\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\UserSkill;
use App\Domain\Skills\Requests\StoreUserSkillRequest;
use App\Domain\Skills\Requests\UpdateUserSkillRequest;
use App\Domain\Skills\Resources\UserSkillResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserSkillController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        /** @var User $user */
        $user = Auth::user();

        // Use the "skills" relation with pivot data and exclude soft-deleted records
        $skills = $user->skills()->get();

        return UserSkillResource::collection($skills);
    }

    public function store(StoreUserSkillRequest $request): JsonResponse
    {
        $data = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        /** @var UserSkill $userSkill */
        $userSkill = $user->userSkills()->create([
            'skill_id'    => $data['skill_id'],
            'level'       => $data['level'],
            'acquired_at' => $data['acquired_at'] ?? null,
        ]);

        /** @var Skill $skill */
        $skill = $user->skills()->find($userSkill->skill_id);

        return (new UserSkillResource($skill))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED)
        ;
    }

    public function show(string $userSkillId): UserSkillResource
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var UserSkill|null $userSkill */
        $userSkill = $user->userSkills()->find($userSkillId);

        if (!$userSkill) {
            abort(Response::HTTP_NOT_FOUND);
        }

        // We need to load the skill relation to pass it to the resource
        $userSkill->load('skill');

        return new UserSkillResource($userSkill->skill);
    }

    public function update(UpdateUserSkillRequest $request, string $userSkillId): UserSkillResource
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var UserSkill|null $userSkill */
        $userSkill = $user->userSkills()->find($userSkillId);

        if (!$userSkill) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $data = $request->validated();

        // Update the pivot data
        $userSkill->update([
            'level'       => $data['level'],
            'acquired_at' => $data['acquired_at'] ?? null,
        ]);

        $userSkill->load('skill');
        $userSkill->skill->refresh();

        return new UserSkillResource($userSkill->skill);
    }

    public function destroy(string $userSkillId): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        /** @var UserSkill|null $userSkill */
        $userSkill = $user->userSkills()->find($userSkillId);

        if (!$userSkill) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $userSkill->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
