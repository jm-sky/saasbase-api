# General Contacts 

We should replace Contractor Contact Person with General Contacts + polimorphic relation to Contractor. 

## Contact model 
- first name: ?string
- last name: ?string
- position: ?string
- email: ?string
- phone_number: ?string
- emails: ?jsonb - `[{ label, email }]` - LATER
- phone_numbers: ?jsonb - `[{ label, phone }]` - LATER
- notes: ?string 
- user_id: ?string - can be app user
- addresses[] - polimorphic relation 
- tags[] - polimorphic relation 
- profileImage - Media Library collection 'profile' (check: `app/Domain/Auth/Models/User.php` for example)

---

# General Contacts Implementation Plan

## Overview
Replace the current `ContractorContactPerson` with a new polymorphic `Contact` model that can be associated with any entity, starting with Contractors.

## Database Changes

### 1. Modify Existing Migration
Update `2024_04_14_000152_create_contractor_contact_people_table.php` to:

```php
Schema::create('contacts', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->foreignUlid('tenant_id')->nullable()->constrained()->cascadeOnDelete();
    $table->string('first_name')->nullable();
    $table->string('last_name')->nullable();
    $table->string('position')->nullable();
    $table->string('email')->nullable();
    $table->string('phone_number')->nullable();
    $table->jsonb('emails')->nullable(); // For future use
    $table->jsonb('phone_numbers')->nullable(); // For future use
    $table->text('notes')->nullable();
    $table->foreignUlid('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->ulidMorphs('contactable');
    $table->timestamps();
    $table->softDeletes();

    $table->index(['first_name', 'last_name']);
    $table->index('email');
    $table->index('phone_number');
});
```

rename it to `2024_04_14_000152_create_contacts_table.php`

## Model Changes

### 1. Create New Contact Model
Create `app/Domain/Common/Models/Contact.php`:

```php
<?php

namespace App\Domain\Common\Models;

use App\Domain\Common\Traits\HasActivityLog;
use App\Domain\Common\Traits\HasActivityLogging;
use App\Domain\Common\Traits\HaveAddresses;
use App\Domain\Common\Traits\HasTags;
use App\Domain\Tenant\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Contact extends BaseModel implements HasMedia
{
    use SoftDeletes;
    use BelongsToTenant;
    use InteractsWithMedia;
    use HasTags;
    use HaveAddresses;
    use HasActivityLog;
    use HasActivityLogging;

    protected $fillable = [
        'first_name',
        'last_name',
        'position',
        'email',
        'phone_number',
        'emails',
        'phone_numbers',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'emails' => 'array',
        'phone_numbers' => 'array',
    ];

    // Add name attribute tht join first & last name

    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile')
            ->singleFile()
            ->acceptsFile(fn (File $file) => in_array($file->mimeType, ['image/jpeg', 'image/png', 'image/webp']));
    }
}
```

### 2. Update Contractor Model
Modify `app/Domain/Contractors/Models/Contractor.php`:

```php
// Replace the contacts() method with:
public function contacts(): MorphMany
{
    return $this->morphMany(Contact::class, 'contactable');
}
```

## DTO Changes

### 1. Create ContactDTO
Create `app/Domain/Common/DTOs/ContactDTO.php`:

```php
<?php

namespace App\Domain\Common\DTOs;

use App\Domain\Common\Models\Contact;
use Carbon\Carbon;

class ContactDTO extends BaseDTO
{
    public function __construct(
        public readonly ?string $id = null,
        public readonly ?string $tenantId = null,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?string $position = null,
        public readonly ?string $email = null,
        public readonly ?string $phoneNumber = null,
        public readonly ?array $emails = null,
        public readonly ?array $phoneNumbers = null,
        public readonly ?string $notes = null,
        public readonly ?string $userId = null,
        public readonly ?string $contactableId = null,
        public readonly ?string $contactableType = null,
        public readonly ?Carbon $createdAt = null,
        public readonly ?Carbon $updatedAt = null,
        public readonly ?Carbon $deletedAt = null,
        public readonly ?array $tags = null,
        public readonly ?MediaDTO $profileImage = null,
    ) {
    }

    public static function fromModel(Contact $model): static
    {
        return new self(
            id: $model->id,
            tenantId: $model->tenant_id,
            firstName: $model->first_name,
            lastName: $model->last_name,
            position: $model->position,
            email: $model->email,
            phoneNumber: $model->phone_number,
            emails: $model->emails,
            phoneNumbers: $model->phone_numbers,
            notes: $model->notes,
            userId: $model->user_id,
            contactableId: $model->contactable_id,
            contactableType: $model->contactable_type,
            createdAt: $model->created_at,
            updatedAt: $model->updated_at,
            deletedAt: $model->deleted_at,
            tags: $model->tags,
            profileImage: $model->getFirstMedia('profile') ? MediaDTO::fromModel($model->getFirstMedia('profile')) : null,
        );
    }

    // Add toArray()
}
```

## Create resource


## Controller Changes

### 1. Create ContactController
Create `app/Domain/Common/Controllers/ContactController.php`:

```php
<?php

namespace App\Domain\Common\Controllers;

use App\Domain\Common\DTOs\ContactDTO;
use App\Domain\Common\Models\Contact;
use App\Domain\Common\Requests\ContactRequest;
use App\Domain\Common\Traits\HasIndexQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ContactController extends Controller
{
    use HasIndexQuery;

    protected int $defaultPerPage = 15;

    public function __construct()
    {
        $this->modelClass = Contact::class;
        // Add filters
    }

    public function index(): JsonResponse
    {
        $contacts = $this->getIndexQuery()->paginate();

        // Return resource collection
        // check: app/Domain/Contractors/Controllers/ContractorController.php
        return response()->json([
            'data' => collect($contacts->items())->map(fn (Contact $contact) => ContactDTO::fromModel($contact)),
            'meta' => [
                'currentPage' => $contacts->currentPage(),
                'lastPage' => $contacts->lastPage(),
                'perPage' => $contacts->perPage(),
                'total' => $contacts->total(),
            ],
        ]);
    }

    public function store(ContactRequest $request): JsonResponse
    {
        $contact = Contact::create($request->validated());

        // Return resource
        return response()->json([
            'message' => 'Contact created successfully.',
            'data' => ContactDTO::fromModel($contact),
        ], Response::HTTP_CREATED);
    }

    public function show(Contact $contact): JsonResponse
    {
        // Return resource
        return response()->json([
            'data' => ContactDTO::fromModel($contact),
        ]);
    }

    public function update(ContactRequest $request, Contact $contact): JsonResponse
    {
        $contact->update($request->validated());

        // Return resource
        return response()->json([
            'message' => 'Contact updated successfully.',
            'data' => ContactDTO::fromModel($contact->fresh()),
        ]);
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return response()->json(['message' => 'Contact deleted successfully.'], Response::HTTP_NO_CONTENT);
    }
}
```

## Request Validation

### 1. Create ContactRequest
Create `app/Domain/Common/Requests/ContactRequest.php`:

```php
<?php

namespace App\Domain\Common\Requests;

use App\Http\Requests\BaseFormRequest;

class ContactRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // should be camelCase
        return [
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'emails' => ['nullable', 'array'],
            'emails.*.label' => ['required_with:emails.*.email', 'string', 'max:255'],
            'emails.*.email' => ['required_with:emails.*.label', 'email', 'max:255'],
            'phone_numbers' => ['nullable', 'array'],
            'phone_numbers.*.label' => ['required_with:phone_numbers.*.phone', 'string', 'max:255'],
            'phone_numbers.*.phone' => ['required_with:phone_numbers.*.label', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'user_id' => ['nullable', 'ulid', 'exists:users,id'],
            'contactable_id' => ['required', 'ulid'],
            'contactable_type' => ['required', 'string'],
        ];
    }
}
```

## Migration

Edit existing migration and rename it


## API Routes

Add to `routes/api.php`:

```php
Route::apiResource('contacts', ContactController::class);
```

## Testing

1. Update existing tests to use the new Contact model
2. Create new tests for the ContactController
3. Test polymorphic relationships
4. Test media attachments
5. Test address and tag relationships

## Considerations

1. Data Migration:
   - Existing contractor contacts need to be migrated to the new structure
   - Consider running the migration in a maintenance window
   - Create a backup before running the migration

2. API Changes:
   - Update frontend to use new contact endpoints
   - Update any existing integrations using the old endpoints
   - Consider versioning the API if needed

3. Performance:
   - Add appropriate indexes for polymorphic relationships
   - Consider caching for frequently accessed contacts
   - Monitor query performance with the new structure

4. Security:
   - Ensure proper authorization checks for contact access
   - Validate polymorphic relationships
   - Implement proper tenant scoping

5. Future Extensions:
   - Plan for additional contactable types
   - Consider implementing contact groups
   - Plan for contact synchronization features
