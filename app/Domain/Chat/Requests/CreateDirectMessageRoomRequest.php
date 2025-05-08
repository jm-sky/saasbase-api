<?php

namespace App\Domain\Chat\Requests;

use App\Http\Requests\BaseFormRequest;

class CreateDirectMessageRoomRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'userId' => ['required', 'uuid', 'exists:users,id'],
        ];
    }

    public function getUserId(): ?string
    {
        return $this->input('userId');
    }
}
