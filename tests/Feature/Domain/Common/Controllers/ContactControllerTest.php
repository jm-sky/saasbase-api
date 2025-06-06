<?php

namespace Tests\Feature\Domain\Common\Controllers;

use App\Domain\Common\Controllers\ContactController;
use App\Domain\Common\Models\Contact;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Scout\Scout;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * @internal
 */
#[CoversClass(ContactController::class)]
class ContactControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private Tenant $tenant;

    private string $baseUrl = '/api/v1/contacts';

    protected function setUp(): void
    {
        parent::setUp();

        $this->markTestSkipped('Skipping because of Scout');

        $this->tenant = Tenant::factory()->create();

        $this->authenticateUser($this->tenant);

        // Scout::fake();
    }

    public function testCanSearchContacts(): void
    {
        Tenant::bypassTenant($this->tenant->id, function () {
            Contact::factory()->count(3)->create();
        });

        $uniqueContact = Contact::factory()->create([
            'first_name' => 'UniqueSearchName',
        ]);

        $response = $this->getJson($this->baseUrl . '/search?q=UniqueSearchName');

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'firstName',
                        'lastName',
                        'position',
                        'email',
                        'phoneNumber',
                        'emails',
                        'phoneNumbers',
                        'notes',
                        'userId',
                        'contactableType',
                        'contactableId',
                        'createdAt',
                        'updatedAt',
                    ],
                ],
                'meta' => [
                    'currentPage',
                    'lastPage',
                    'perPage',
                    'total',
                ],
            ])
            ->assertJsonFragment(['firstName' => 'UniqueSearchName'])
        ;
    }

    public function testSearchRequiresQueryParam(): void
    {
        $this->markTestSkipped('Skipping because of Scout');

        $response = $this->getJson($this->baseUrl . '/search');
        $response->assertStatus(Response::HTTP_BAD_REQUEST)
            ->assertJson(['message' => 'Search query is required'])
        ;
    }
}
