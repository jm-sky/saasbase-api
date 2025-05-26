<?php

namespace App\Domain\Projects\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateProjectRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statusId'    => ['sometimes', 'uuid', 'exists:project_statuses,id'],
            'startDate'   => ['nullable', 'date'],
            'endDate'     => ['nullable', 'date'],
        ];
    }
}
