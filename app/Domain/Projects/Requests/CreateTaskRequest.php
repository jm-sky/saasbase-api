<?php

namespace App\Domain\Projects\Requests;

use App\Http\Requests\BaseFormRequest;

class CreateTaskRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return [
            'projectId'     => ['required', 'uuid', 'exists:projects,id'],
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['nullable', 'string'],
            'statusId'      => ['required', 'uuid', 'exists:task_statuses,id'],
            'priority'      => ['nullable', 'string'],
            'assignedToId'  => ['nullable', 'uuid', 'exists:users,id'],
            'dueDate'       => ['nullable', 'date'],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'project_id'     => $validated['projectId'],
            'title'          => $validated['title'],
            'description'    => $validated['description'] ?? null,
            'status_id'      => $validated['statusId'],
            'priority'       => $validated['priority'] ?? null,
            'assigned_to_id' => $validated['assignedToId'] ?? null,
            'due_date'       => $validated['dueDate'] ?? null,
        ];
    }
}
