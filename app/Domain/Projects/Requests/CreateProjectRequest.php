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

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();

        return [
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'status_id'   => $validated['statusId'],
            'start_date'  => $validated['startDate'] ?? null,
            'end_date'    => $validated['endDate'] ?? null,
        ];
    }
}
