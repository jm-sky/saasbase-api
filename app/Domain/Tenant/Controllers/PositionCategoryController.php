<?php

namespace App\Domain\Tenant\Controllers;

use App\Domain\Auth\Models\User;
use App\Domain\Tenant\Models\PositionCategory;
use App\Domain\Tenant\Requests\StorePositionCategoryRequest;
use App\Domain\Tenant\Requests\UpdatePositionCategoryRequest;
use App\Domain\Tenant\Resources\PositionCategoryResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PositionCategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = PositionCategory::query()
            ->ordered()
            ->get()
        ;

        return PositionCategoryResource::collection($categories);
    }

    public function store(StorePositionCategoryRequest $request): PositionCategoryResource
    {
        $data     = $request->validated();
        $category = PositionCategory::create($data);

        return new PositionCategoryResource($category);
    }

    public function update(UpdatePositionCategoryRequest $request, PositionCategory $positionCategory): PositionCategoryResource
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->tenant_id !== $positionCategory->tenant_id) {
            throw new \Exception('You are not allowed to update this position category');
        }

        $positionCategory->update($request->validated());

        return new PositionCategoryResource($positionCategory);
    }

    public function destroy(PositionCategory $positionCategory): Response
    {
        /** @var User $user */
        $user = Auth::user();

        if ($user->tenant_id !== $positionCategory->tenant_id) {
            throw new \Exception('You are not allowed to delete this position category');
        }

        $positionCategory->delete();

        return response()->noContent();
    }
}
