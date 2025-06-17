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
            'statusId'      => ['sometimes', 'ulid', 'exists:task_statuses,id'],
            'priority'      => ['nullable', 'string'],
            'assigneeId'    => ['nullable', 'ulid', 'exists:users,id'],
            'dueDate'       => ['nullable', 'date'],
        ];
    }
}
