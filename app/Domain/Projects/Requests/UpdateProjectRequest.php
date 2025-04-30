<?php

namespace App\Domain\Projects\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'name'        => $validated['name'] ?? null,
            'description' => $validated['description'] ?? null,
            'status_id'   => $validated['statusId'] ?? null,
            'start_date'  => $validated['startDate'] ?? null,
            'end_date'    => $validated['endDate'] ?? null,
        ];
    }
}
