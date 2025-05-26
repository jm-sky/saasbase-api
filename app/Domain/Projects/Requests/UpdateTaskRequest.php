<?php

namespace App\Domain\Projects\Requests;

use App\Http\Requests\BaseFormRequest;

class UpdateTaskRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'title'         => ['sometimes', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'statusId'      => ['sometimes', 'uuid', 'exists:task_statuses,id'],
            'priority'      => ['nullable', 'string'],
            'assignedToId'  => ['nullable', 'uuid', 'exists:users,id'],
            'dueDate'       => ['nullable', 'date'],
        ];
    }
}
