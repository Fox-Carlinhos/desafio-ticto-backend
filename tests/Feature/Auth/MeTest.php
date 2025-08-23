<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class MeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_get_profile_data(): void
    {
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => 'João Silva',
                    'email' => 'joao@example.com',
                    'role' => 'admin',
                    'is_active' => true,
                ],
            ]);
    }

    public function test_me_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }

    public function test_me_endpoint_response_structure(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'is_active',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_me_endpoint_excludes_sensitive_data(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $responseData = $response->json('data');

        $this->assertArrayNotHasKey('password', $responseData);
        $this->assertArrayNotHasKey('remember_token', $responseData);
    }

    public function test_admin_user_me_endpoint(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'role' => 'admin',
                ],
            ]);
    }

    public function test_employee_user_me_endpoint(): void
    {
        $employee = User::factory()->employee()->create();
        Sanctum::actingAs($employee);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'role' => 'employee',
                ],
            ]);
    }

    public function test_employee_user_me_includes_employee_profile(): void
    {
        $user = User::factory()->employee()->create();
        $employee = Employee::factory()->for($user)->create([
            'full_name' => 'João Silva Santos',
            'position' => 'Desenvolvedor Backend',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'employee' => [
                        'id' => $employee->id,
                        'full_name' => 'João Silva Santos',
                        'position' => 'Desenvolvedor Backend',
                    ],
                ],
            ]);
    }

    public function test_admin_user_me_includes_managed_employees_count(): void
    {
        $admin = User::factory()->admin()->create();

        Employee::factory()->count(3)->withManager($admin)->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'managed_employees_count' => 3,
                ],
            ]);
    }

    public function test_inactive_user_can_access_me_endpoint(): void
    {
        $user = User::factory()->inactive()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'is_active' => false,
                ],
            ]);
    }

    public function test_user_without_employee_profile_me_endpoint(): void
    {
        $user = User::factory()->employee()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'employee' => null,
                ],
            ]);
    }

    public function test_admin_without_managed_employees_me_endpoint(): void
    {
        $admin = User::factory()->admin()->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'managed_employees_count' => 0,
                ],
            ]);
    }

    public function test_me_endpoint_with_deleted_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');

        $token->accessToken->delete();

        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ]);

        $response->assertStatus(401);
    }

    public function test_me_endpoint_includes_timestamps(): void
    {
        $user = User::factory()->create([
            'created_at' => '2024-01-01 10:00:00',
            'updated_at' => '2024-01-02 15:30:00',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'created_at',
                    'updated_at',
                ],
            ]);

        $responseData = $response->json('data');
        $this->assertEquals($user->created_at->toISOString(), $responseData['created_at']);
        $this->assertEquals($user->updated_at->toISOString(), $responseData['updated_at']);
    }

    public function test_me_endpoint_includes_email_verification_status(): void
    {
        $verifiedUser = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($verifiedUser);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('data.email_verified_at'));

        $unverifiedUser = User::factory()->unverified()->create();
        Sanctum::actingAs($unverifiedUser);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200);
        $this->assertNull($response->json('data.email_verified_at'));
    }

    public function test_me_endpoint_with_expired_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');

        $token->accessToken->update([
            'created_at' => now()->subDays(365),
        ]);

        $response = $this->getJson('/api/me', [
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ]);

        $response->assertStatus(200);
    }
}
