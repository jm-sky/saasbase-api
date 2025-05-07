<?php

namespace Database\Factories;

use App\Domain\Feeds\Models\Feed;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Feed>
 */
class FeedFactory extends Factory
{
    protected $model = Feed::class;

    public function definition(): array
    {
        return [
            'id'          => $this->faker->uuid(),
            'tenant_id'   => $this->faker->uuid(),
            'user_id'     => $this->faker->uuid(),
            'title'       => $this->faker->sentence(6, true),
            'content'     => $this->faker->paragraphs(3, true),
            'content_html'=> null,
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
