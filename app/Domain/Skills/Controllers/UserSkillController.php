<?php

namespace App\Domain\Skills\Controllers;

use App\Domain\Skills\Models\UserSkill;
use App\Domain\Skills\Requests\UserSkillRequest;
use App\Domain\Skills\Resources\UserSkillResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UserSkillController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $userSkills = UserSkill::with(['user', 'skill'])->paginate();

        return UserSkillResource::collection($userSkills);
    }

    public function store(UserSkillRequest $request): UserSkillResource
    {
        $userSkill = UserSkill::create($request->validated());

        return new UserSkillResource($userSkill);
    }

    public function show(UserSkill $userSkill): UserSkillResource
    {
        $userSkill->load(['user', 'skill']);

        return new UserSkillResource($userSkill);
    }

    public function update(UserSkillRequest $request, UserSkill $userSkill): UserSkillResource
    {
        $userSkill->update($request->validated());

        return new UserSkillResource($userSkill);
    }

    public function destroy(UserSkill $userSkill): JsonResponse
    {
        $userSkill->delete();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }
}
