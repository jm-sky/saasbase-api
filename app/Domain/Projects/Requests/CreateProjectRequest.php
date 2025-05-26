<?php

namespace App\Domain\Projects\Requests;

use App\Http\Requests\BaseFormRequest;

class CreateProjectRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'statusId'    => ['required', 'uuid', 'exists:project_statuses,id'],
            'startDate'   => ['nullable', 'date'],
            'endDate'     => ['nullable', 'date'],
        ];
    }
}
