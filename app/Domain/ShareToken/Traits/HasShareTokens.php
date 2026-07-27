<?php

namespace App\Domain\ShareToken\Traits;

use App\Domain\ShareToken\Models\ShareToken;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasShareTokens
{
    /**
     * share_tokens is a polymorphic table (shareable_type + shareable_id),
     * but this used a plain hasMany() on shareable_id alone, ignoring
     * shareable_type entirely — a false ID match against a different
     * shareable model would leak/attach the wrong token.
     */
    public function shareTokens(): MorphMany
    {
        return $this->morphMany(ShareToken::class, 'shareable');
    }
}
