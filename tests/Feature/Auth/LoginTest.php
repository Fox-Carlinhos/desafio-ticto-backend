<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login realizado com sucesso',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'is_active',
                    ],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.access_token'));
        $this->assertEquals($user->id, $response->json('data.user.id'));
        $this->assertEquals($user->email, $response->json('data.user.email'));
    }

    public function test_login_fails_with_invalid_email(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Email ou senha inválidos',
            ]);
    }

    public function test_login_fails_with_invalid_password(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Email ou senha inválidos',
            ]);
    }

    public function test_login_fails_for_inactive_user(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Usuário inativo',
            ]);
    }

    public function test_login_validation_fails_with_missing_email(): void
    {
        $response = $this->postJson('/api/login', [
            'password' => 'password123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Dados de validação inválidos',
            ])
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_validation_fails_with_missing_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Dados de validação inválidos',
            ])
            ->assertJsonValidationErrors(['password']);
    }

    public function test_login_validation_fails_with_invalid_email_format(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'invalid-email',
            'password' => 'password123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Dados de validação inválidos',
            ])
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_validation_fails_with_short_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => '123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Dados de validação inválidos',
            ])
            ->assertJsonValidationErrors(['password']);
    }

    public function test_successful_login_is_logged(): void
    {
        Log::spy();

        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        Log::shouldHaveReceived('info')
            ->once()
            ->with('Successful authentication', \Mockery::type('array'));
    }

    public function test_failed_login_is_logged(): void
    {
        Log::spy();

        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        Log::shouldHaveReceived('warning')
            ->once()
            ->with('Failed authentication attempt', \Mockery::type('array'));
    }

    public function test_admin_user_can_login(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'role' => 'admin',
                    ],
                ],
            ]);
    }

    public function test_employee_user_can_login(): void
    {
        $employee = User::factory()->employee()->create([
            'email' => 'employee@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'employee@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'role' => 'employee',
                    ],
                ],
            ]);
    }

    public function test_login_creates_sanctum_token(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->assertCount(0, $user->tokens);

        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $user->refresh();
        $this->assertCount(1, $user->tokens);
        $this->assertEquals('auth_token', $user->tokens->first()->name);
    }

    public function test_multiple_logins_create_multiple_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $user->refresh();
        $this->assertCount(2, $user->tokens);
    }

    public function test_login_response_includes_complete_user_data(): void
    {
        $user = User::factory()->create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'joao@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => 'João Silva',
                        'email' => 'joao@example.com',
                        'role' => 'admin',
                        'is_active' => true,
                    ],
                ],
            ]);

        $responseData = $response->json('data.user');
        $this->assertArrayNotHasKey('password', $responseData);
        $this->assertArrayNotHasKey('remember_token', $responseData);
    }

    public function test_login_with_case_insensitive_email(): void
    {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'USER@EXAMPLE.COM',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login realizado com sucesso',
            ]);
    }
}
