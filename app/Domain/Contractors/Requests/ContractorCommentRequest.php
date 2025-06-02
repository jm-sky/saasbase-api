<?php

namespace App\Domain\Contractors\Requests;

use App\Http\Requests\BaseFormRequest;
use App\Rules\NoProfanity;

class ContractorCommentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', new NoProfanity()],
        ];
    }
}
