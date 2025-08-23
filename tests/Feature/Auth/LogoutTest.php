<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_logout_with_valid_token(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout realizado com sucesso',
            ]);
    }

    public function test_logout_requires_authentication(): void
    {
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401);
    }

        public function test_logout_deletes_current_token(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('auth_token');

        $this->assertCount(1, $user->tokens);

        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertCount(0, $user->tokens);
    }

    public function test_logout_only_deletes_current_token_not_all_tokens(): void
    {
        $user = User::factory()->create();

        $token1 = $user->createToken('auth_token_1');
        $token2 = $user->createToken('auth_token_2');

        $this->assertCount(2, $user->tokens);

        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token1->plainTextToken
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertCount(1, $user->tokens);
        $this->assertEquals('auth_token_2', $user->tokens->first()->name);
    }

    public function test_admin_user_can_logout(): void
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout realizado com sucesso',
            ]);
    }

    public function test_employee_user_can_logout(): void
    {
        $employee = User::factory()->employee()->create();
        Sanctum::actingAs($employee);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout realizado com sucesso',
            ]);
    }

    public function test_inactive_user_can_still_logout(): void
    {
        $user = User::factory()->inactive()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout realizado com sucesso',
            ]);
    }

    public function test_logout_response_structure(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Logout realizado com sucesso',
            ]);
    }

    public function test_logout_with_invalid_token_format(): void
    {
        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer invalid-token-format'
        ]);

        $response->assertStatus(401);
    }

    public function test_logout_with_expired_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');

        $token->accessToken->update([
            'created_at' => now()->subDays(365),
        ]);

        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ]);

        $response->assertStatus(200);
    }

    public function test_logout_with_deleted_user(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');

        $user->delete();

        $response = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $token->plainTextToken
        ]);

        $response->assertStatus(401);
    }

    public function test_multiple_logout_calls(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token');
        $tokenString = $token->plainTextToken;

        $response1 = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $tokenString
        ]);
        $response1->assertStatus(200);

        $user->refresh();
        $this->assertCount(0, $user->tokens);

        $this->refreshApplication();

        $response2 = $this->postJson('/api/logout', [], [
            'Authorization' => 'Bearer ' . $tokenString
        ]);
        $response2->assertStatus(401);
    }
}
