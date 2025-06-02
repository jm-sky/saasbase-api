<?php

namespace App\Domain\Feeds\Requests;

use App\Http\Requests\BaseFormRequest;
use App\Rules\NoProfanity;

class StoreFeedRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true; // lub dodaj własną logikę autoryzacji jeśli potrzeba
    }

    public function rules(): array
    {
        return [
            'title'   => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', new NoProfanity()],
        ];
    }
}
