<?php

namespace Tests\Unit\Domains\Tenant;

use App\Domains\Tenant\Requests\TenantRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TenantRequestTest extends TestCase
{
    private function getValidator(array $data)
    {
        $request = new TenantRequest();
        return Validator::make($data, $request->rules());
    }

    public function test_validation_passes_with_valid_data(): void
    {
        $data = [
            'name' => 'Test Tenant',
            'slug' => 'test-tenant'
        ];

        $validator = $this->getValidator($data);

        $this->assertTrue($validator->passes());
    }

    public function test_validation_fails_when_name_is_missing(): void
    {
        $data = [
            'slug' => 'test-tenant'
        ];

        $validator = $this->getValidator($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function test_validation_fails_when_name_is_too_long(): void
    {
        $data = [
            'name' => str_repeat('a', 256),
            'slug' => 'test-tenant'
        ];

        $validator = $this->getValidator($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function test_validation_fails_when_slug_is_missing(): void
    {
        $data = [
            'name' => 'Test Tenant'
        ];

        $validator = $this->getValidator($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('slug'));
    }

    public function test_validation_fails_when_slug_is_too_long(): void
    {
        $data = [
            'name' => 'Test Tenant',
            'slug' => str_repeat('a', 256)
        ];

        $validator = $this->getValidator($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('slug'));
    }

    public function test_validation_fails_when_slug_is_not_unique(): void
    {
        $existingTenant = \App\Domains\Tenant\Models\Tenant::factory()->create();

        $data = [
            'name' => 'Test Tenant',
            'slug' => $existingTenant->slug
        ];

        $validator = $this->getValidator($data);

        $this->assertFalse($validator->passes());
        $this->assertTrue($validator->errors()->has('slug'));
    }
}
