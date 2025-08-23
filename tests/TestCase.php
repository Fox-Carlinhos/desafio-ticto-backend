<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Disable external HTTP calls by default
        if (!config('app.allow_http_tests', false)) {
            $this->mockExternalServices();
        }
    }

    /**
     * Mock external services by default
     */
    protected function mockExternalServices(): void
    {
        // Override in specific tests if needed
    }

    /**
     * Create an admin user for testing
     */
    protected function createAdmin(array $attributes = []): \App\Models\User
    {
        return \App\Models\User::factory()->admin()->create($attributes);
    }

    /**
     * Create an employee user for testing
     */
    protected function createEmployee(array $attributes = []): \App\Models\User
    {
        return \App\Models\User::factory()->employee()->create($attributes);
    }

    /**
     * Create a complete employee with profile and address
     */
    protected function createCompleteEmployee(array $userAttributes = [], array $employeeAttributes = [], array $addressAttributes = []): \App\Models\Employee
    {
        $user = $this->createEmployee($userAttributes);
        
        $employee = \App\Models\Employee::factory()
            ->for($user)
            ->create($employeeAttributes);
            
        \App\Models\Address::factory()
            ->for($employee)
            ->create($addressAttributes);
            
        return $employee->load(['user', 'address']);
    }

    /**
     * Authenticate as admin for testing
     */
    protected function actingAsAdmin(array $attributes = []): static
    {
        $admin = $this->createAdmin($attributes);
        return $this->actingAs($admin);
    }

    /**
     * Authenticate as employee for testing
     */
    protected function actingAsEmployee(array $attributes = []): static
    {
        $employee = $this->createEmployee($attributes);
        return $this->actingAs($employee);
    }

    /**
     * Assert JSON response has expected structure
     */
    protected function assertJsonResponseStructure(array $structure, $response = null): void
    {
        if ($response) {
            $response->assertJsonStructure($structure);
        } else {
            $this->assertJsonStructure($structure);
        }
    }

    /**
     * Assert successful API response
     */
    protected function assertSuccessResponse($response, string $message = null): void
    {
        $response->assertStatus(200)
            ->assertJson(['success' => true]);
            
        if ($message) {
            $response->assertJson(['message' => $message]);
        }
    }

    /**
     * Assert error API response
     */
    protected function assertErrorResponse($response, int $status, string $message = null): void
    {
        $response->assertStatus($status)
            ->assertJson(['success' => false]);
            
        if ($message) {
            $response->assertJson(['message' => $message]);
        }
    }
}
