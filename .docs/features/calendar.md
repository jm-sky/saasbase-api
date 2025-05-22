# Calendar Module (Models & Relationships)

## 1. events
Main table for calendar events.

- id: UUID / bigIncrements – primary key
- tenant_id: UUID – tenant scope
- title: string – event title
- description: text – optional
- start_at: datetime – start time
- end_at: datetime – end time
- is_all_day: boolean – full-day flag
- location: string – optional physical/online location
- color: string – optional color tag for UI
- status: string – event status (see EventStatus enum)
- visibility: string – event visibility (public/private)
- timezone: string – event timezone (e.g. 'UTC', 'Europe/Warsaw')
- recurrence_rule: text – iCal recurrence rule for repeating events
- reminder_settings: json – notification preferences (e.g. {"email": true, "push": true, "remind_before": "1h"})
- created_by_id: foreignId → users – who created the event
- related_type: string – polymorphic (e.g. 'Project', 'Contractor')
- related_id: uuid – polymorphic
- created_at / updated_at

### EventStatus Enum:
- Scheduled: Event is planned and active
- Cancelled: Event has been cancelled
- Completed: Event has been completed
- Tentative: Event is not yet confirmed

### Relations:
- belongsTo user (creator)
- morphTo related (project, contractor, etc.)
- hasMany event_attendees
- hasMany event_reminders

---

## 2. event_attendees
Entities that are attending the event.

- id
- event_id: foreignId → events
- attendee_type: string – polymorphic (e.g. 'User', 'ContactPerson')
- attendee_id: uuid
- response_status: string – attending / maybe / declined
- response_at: datetime – optional
- custom_note: string – optional message from attendee

### Relations:
- belongsTo event
- morphTo attendee

---

## 3. event_reminders
Reminders for calendar events.

- id
- event_id: foreignId → events
- user_id: foreignId → users
- reminder_at: datetime – when to send the reminder
- reminder_type: string – type of reminder (email, push, etc.)
- is_sent: boolean – whether reminder was sent
- created_at / updated_at

### Relations:
- belongsTo event
- belongsTo user

---

## 4. (optional) contact_people
If not already existing — contractor's contact people.

- id
- contractor_id: foreignId → contractors
- name, email, phone...

---

## Notes:
- Events can relate to any model using `morphTo: related()`, e.g. project, task, deal, product.
- Attendees use polymorphism to support users and contact people.
- You may index `(related_type, related_id)` and `(attendee_type, attendee_id)` for performance.
- Consider indexing `(tenant_id, start_at, end_at)` for date range queries.
- Recurrence rules follow iCal RFC 5545 format for maximum compatibility.

## Queries example:
- All events for given project: `Event::whereMorphedTo('related', $project)->get();`
- All events where user is attending: `EventAttendee::whereMorphedTo('attendee', $user)->with('event')->get();`
- Upcoming events: `Event::where('start_at', '>', now())->where('status', EventStatus::SCHEDULED)->get();`