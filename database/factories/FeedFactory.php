<?php

namespace Database\Factories;

use App\Domain\Feeds\Models\Feed;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Feed>
 */
class FeedFactory extends Factory
{
    protected $model = Feed::class;

    public function definition(): array
    {
        return [
            'id'          => Str::ulid()->toString(),
            'tenant_id'   => Str::ulid()->toString(),
            'user_id'     => Str::ulid()->toString(),
            'title'       => $this->faker->sentence(6, true),
            'content'     => $this->faker->paragraphs(3, true),
            'content_html'=> null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
