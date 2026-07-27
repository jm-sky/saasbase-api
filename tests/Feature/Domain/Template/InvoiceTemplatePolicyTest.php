<?php

namespace Tests\Feature\Domain\Template;

use App\Domain\Auth\JwtHelper;
use App\Domain\Auth\Models\User;
use App\Domain\Rights\Models\Permission;
use App\Domain\Template\Enums\TemplateCategory;
use App\Domain\Template\Models\InvoiceTemplate;
use App\Domain\Template\Policies\InvoiceTemplatePolicy;
use App\Domain\Tenant\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;
use Tests\Traits\WithAuthenticatedUser;

/**
 * Direct policy assertions — HTTP paths depend on Spatie team-context
 * quirks around hasPermissionTo(); these lock the ownership rules that
 * protect global templates.
 *
 * @internal
 */
#[CoversClass(InvoiceTemplatePolicy::class)]
class InvoiceTemplatePolicyTest extends TestCase
{
    use RefreshDatabase;
    use WithAuthenticatedUser;

    private Tenant $tenant;

    private InvoiceTemplatePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
        $this->policy = new InvoiceTemplatePolicy;
    }

    public function test_user_without_permission_cannot_view_any(): void
    {
        $user = $this->authenticateWithJwt();

        $this->assertFalse($this->policy->viewAny($user));
        $this->assertFalse($this->policy->create($user));
    }

    public function test_user_with_permission_can_view_global_but_not_mutate(): void
    {
        $user = $this->authenticateWithJwt();
        $this->grantInvoiceTemplatesManage($user);

        $global = InvoiceTemplate::withoutEvents(function () {
            return InvoiceTemplate::create([
                'tenant_id' => null,
                'name' => 'Global default',
                'content' => '<p>{{number}}</p>',
                'category' => TemplateCategory::INVOICE,
                'preview_data' => [],
                'settings' => [],
                'is_active' => true,
                'is_default' => true,
            ]);
        });

        $this->assertNull($global->fresh()->tenant_id);
        $this->assertTrue($this->policy->view($user, $global));
        $this->assertFalse($this->policy->update($user, $global));
        $this->assertFalse($this->policy->delete($user, $global));
        $this->assertFalse($this->policy->setDefault($user, $global));
    }

    public function test_user_with_permission_can_mutate_own_tenant_template(): void
    {
        $user = $this->authenticateWithJwt();
        $this->grantInvoiceTemplatesManage($user);

        $owned = Tenant::bypassTenant($this->tenant->id, function () {
            return InvoiceTemplate::create([
                'tenant_id' => $this->tenant->id,
                'name' => 'Tenant template',
                'content' => '<p>{{number}}</p>',
                'category' => TemplateCategory::INVOICE,
                'preview_data' => [],
                'settings' => [],
                'is_active' => true,
                'is_default' => false,
            ]);
        });

        $this->assertTrue($this->policy->view($user, $owned));
        $this->assertTrue($this->policy->update($user, $owned));
        $this->assertTrue($this->policy->delete($user, $owned));
    }

    private function authenticateWithJwt(): User
    {
        $user = User::factory()->create();
        $user->tenants()->attach($this->tenant, ['role' => 'Admin']);

        $token = JwtHelper::createTokenWithTenant($user, $this->tenant->id);
        $this->withHeader('Authorization', 'Bearer '.$token);

        // Establish guard + JWT payload so $user->tenant_id / hasPermissionTo work.
        Auth::guard('api')->setToken($token)->authenticate();

        return Auth::guard('api')->user();
    }

    private function grantInvoiceTemplatesManage(User $user): void
    {
        Tenant::bypassTenant(Tenant::GLOBAL_TENANT_ID, function () {
            Permission::findOrCreate('invoice_templates.manage', 'api');
        });

        /** @var PermissionRegistrar $registrar */
        $registrar = app(PermissionRegistrar::class);
        $previous = $registrar->getPermissionsTeamId();
        $registrar->setPermissionsTeamId($this->tenant->id);

        try {
            $user->givePermissionTo('invoice_templates.manage');
        } finally {
            $registrar->setPermissionsTeamId($previous);
        }

        $registrar->setPermissionsTeamId($this->tenant->id);
        Auth::setUser($user->fresh());
        // Re-bind JWT so getTenantId() still resolves after setUser().
        $token = JwtHelper::createTokenWithTenant(Auth::user(), $this->tenant->id);
        Auth::guard('api')->setToken($token)->authenticate();
        $registrar->setPermissionsTeamId($this->tenant->id);
    }
}
