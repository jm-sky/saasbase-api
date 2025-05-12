<?php

namespace App\Domain\Common\Traits;

use App\Domain\Common\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Str;

trait HasTags
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(
            Tag::class,
            'taggable',
            'taggables',
            'taggable_id',
            'tag_id'
        );
    }

    public function addTag(string $name, ?string $tenantId = null): Tag
    {
        $slug = Str::slug($name);
        $tag  = Tag::firstOrCreate([
            'tenant_id' => $tenantId,
            'slug'      => $slug,
        ], [
            'name' => $name,
        ]);
        $this->tags()->syncWithoutDetaching([$tag->id]);

        return $tag;
    }

    public function removeTag(string $name, ?string $tenantId = null): void
    {
        $slug = Str::slug($name);
        $tag  = Tag::where('tenant_id', $tenantId)->where('slug', $slug)->first();

        if ($tag) {
            $this->tags()->detach($tag->id);
        }
    }

    public function syncTags(array $names, ?string $tenantId = null): void
    {
        $tagIds = collect($names)->map(function ($name) use ($tenantId) {
            $slug = Str::slug($name);

            return Tag::firstOrCreate([
                'tenant_id' => $tenantId,
                'slug'      => $slug,
            ], [
                'name' => $name,
            ])->id;
        })->all();
        $this->tags()->sync($tagIds);
    }

    public function getTagNames(): array
    {
        return $this->tags->pluck('name')->all();
    }
}
