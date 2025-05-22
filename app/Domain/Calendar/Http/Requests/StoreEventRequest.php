<?php

namespace App\Domain\Calendar\Http\Requests;

use App\Domain\Calendar\Enums\EventStatus;
use App\Domain\Calendar\Enums\EventVisibility;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreEventRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title'                         => ['required', 'string', 'max:255'],
            'description'                   => ['nullable', 'string'],
            'startAt'                       => ['required', 'date'],
            'endAt'                         => ['required', 'date', 'after:start_at'],
            'isAllDay'                      => ['boolean'],
            'location'                      => ['nullable', 'string', 'max:255'],
            'color'                         => ['nullable', 'string', 'max:50'],
            'status'                        => ['required', Rule::enum(EventStatus::class)],
            'visibility'                    => ['required', Rule::enum(EventVisibility::class)],
            'timezone'                      => ['required', 'string', 'timezone'],
            'recurrenceRule'                => ['nullable', 'string'],
            'reminderSettings'              => ['nullable', 'array'],
            'reminderSettings.email'        => ['boolean'],
            'reminderSettings.push'         => ['boolean'],
            'reminderSettings.remindBefore' => ['string'],
            'relatedType'                   => ['nullable', 'string'],
            'relatedId'                     => ['nullable', 'uuid'],
            'attendees'                     => ['array'],
            'attendees.*.attendeeType'      => ['required', 'string'],
            'attendees.*.attendeeId'        => ['required', 'uuid'],
            'attendees.*.responseStatus'    => ['required', 'string'],
            'attendees.*.customNote'        => ['nullable', 'string'],
        ];
    }
}
