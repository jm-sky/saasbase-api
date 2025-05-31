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
        $skills = $user->skills()
            ->withPivot(['level', 'acquired_at'])
            ->with('skill')
            ->get()
        ;

        return UserSkillResource::collection($skills);
    }

    public function store(StoreUserSkillRequest $request): UserSkillResource
    {
        $data = $request->validated();

        /** @var User $user */
        $user = Auth::user();

        // Create a new UserSkill model
        $userSkill = new UserSkill([
            'user_id'     => $user->id,
            'skill_id'    => $data['skill_id'],
            'level'       => $data['level'],
            'acquired_at' => $data['acquired_at'] ?? null,
        ]);

        $userSkill->save();
        $userSkill->load(['user', 'skill']);

        return new UserSkillResource($userSkill);
    }

    public function show(UserSkill $userSkill): UserSkillResource
    {
        /** @var User $user */
        $user = Auth::user();

        // Check if the user skill belongs to the authenticated user
        if ($userSkill->user_id !== $user->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $userSkill->load(['user', 'skill']);

        return new UserSkillResource($userSkill);
    }

    public function update(UpdateUserSkillRequest $request, UserSkill $userSkill): UserSkillResource
    {
        /** @var User $user */
        $user = Auth::user();

        // Check if the user skill belongs to the authenticated user
        if ($userSkill->user_id !== $user->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $data = $request->validated();

        // Update the pivot data
        $userSkill->update([
            'level'       => $data['level'],
            'acquired_at' => $data['acquired_at'] ?? null,
        ]);

        $userSkill->load(['user', 'skill']);

        return new UserSkillResource($userSkill);
    }

    public function destroy(UserSkill $userSkill): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        // Check if the user skill belongs to the authenticated user
        if ($userSkill->user_id !== $user->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $userSkill->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
