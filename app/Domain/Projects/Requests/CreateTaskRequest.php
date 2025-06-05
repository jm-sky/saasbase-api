<?php

namespace App\Domain\Projects\Requests;

use App\Http\Requests\BaseFormRequest;

class CreateTaskRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'projectId'     => ['required', 'ulid', 'exists:projects,id'],
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'statusId'      => ['required', 'ulid', 'exists:task_statuses,id'],
            'priority'      => ['nullable', 'string'],
            'assignedToId'  => ['nullable', 'ulid', 'exists:users,id'],
            'dueDate'       => ['nullable', 'date'],
        ];
    }
}
