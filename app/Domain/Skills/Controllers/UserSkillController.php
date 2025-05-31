<?php

namespace App\Domain\Skills\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Skills\Models\Skill;
use App\Domain\Skills\Models\UserSkill;
use App\Domain\Skills\Requests\UserSkillRequest;
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
        $skills = $user->skills()
            ->withPivot(['level', 'acquired_at'])
            ->get()
        ;

        return UserSkillResource::collection($skills);
    }

    public function store(UserSkillRequest $request): UserSkillResource
    {
        $data = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        // Attach skill to user via the pivot table
        $user->skills()->attach($data['skill_id'], [
            'level'       => $data['level'],
            'acquired_at' => $data['acquired_at'] ?? null,
        ]);

        // Fetch the newly attached skill with pivot data
        $skill = Skill::findOrFail($data['skill_id']);

        return new UserSkillResource($skill->setRelation('pivot', (object) [
            'level'       => $data['level'],
            'acquired_at' => $data['acquired_at'] ?? null,
        ]));
    }

    public function show(string $userSkillId): UserSkillResource
    {
        $userSkill = UserSkill::findOrFail($userSkillId);
        $userSkill->load(['user', 'skill']);

        return new UserSkillResource($userSkill);
    }

    public function update(UserSkillRequest $request, string $userSkillId): UserSkillResource
    {
        $userSkill = UserSkill::findOrFail($userSkillId);
        $userSkill->update($request->validated());

        $userSkill->load(['user', 'skill']);

        return new UserSkillResource($userSkill);
    }

    public function destroy(string $userSkillId): JsonResponse
    {
        $userSkill = UserSkill::findOrFail($userSkillId);
        $userSkill->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
