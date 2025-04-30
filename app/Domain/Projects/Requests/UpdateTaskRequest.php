<?php

namespace App\Domain\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
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

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'title'          => $validated['title'] ?? null,
            'description'    => $validated['description'] ?? null,
            'status_id'      => $validated['statusId'] ?? null,
            'priority'       => $validated['priority'] ?? null,
            'assigned_to_id' => $validated['assignedToId'] ?? null,
            'due_date'       => $validated['dueDate'] ?? null,
        ];
    }
}
