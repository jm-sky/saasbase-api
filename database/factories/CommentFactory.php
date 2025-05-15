<?php

namespace Database\Factories;

use App\Domain\Auth\Models\User;
use App\Domain\Common\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommentFactory extends Factory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'tenant_id'        => null, // Will be set when creating
            'user_id'          => User::factory(),
            'content'          => $this->faker->paragraph(),
            'commentable_id'   => null, // Will be set when creating
            'commentable_type' => null, // Will be set when creating
            'meta'             => null,
        ];
    }
}
