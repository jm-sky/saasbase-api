<?php

namespace App\Domain\Calendar\Http\Requests;

use App\Domain\Calendar\Enums\EventStatus;
use App\Domain\Calendar\Enums\EventVisibility;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                         => ['sometimes', 'required', 'string', 'max:255'],
            'description'                   => ['nullable', 'string'],
            'startAt'                       => ['sometimes', 'required', 'date'],
            'endAt'                         => ['sometimes', 'required', 'date', 'after:start_at'],
            'isAllDay'                      => ['boolean'],
            'location'                      => ['nullable', 'string', 'max:255'],
            'color'                         => ['nullable', 'string', 'max:50'],
            'status'                        => ['sometimes', 'required', Rule::enum(EventStatus::class)],
            'visibility'                    => ['sometimes', 'required', Rule::enum(EventVisibility::class)],
            'timezone'                      => ['sometimes', 'required', 'string', 'timezone'],
            'recurrenceRule'                => ['nullable', 'string'],
            'reminderSettings'              => ['nullable', 'array'],
            'reminderSettings.email'        => ['boolean'],
            'reminderSettings.push'         => ['boolean'],
            'reminderSettings.remindBefore' => ['string'],
            'relatedType'                   => ['nullable', 'string'],
            'relatedId'                     => ['nullable', 'ulid'],
        ];
    }
}
