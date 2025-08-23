<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class ChangePasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_password_with_valid_data(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Senha alterada com sucesso',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
        $this->assertFalse(Hash::check('oldpassword123', $user->password));
    }

    public function test_change_password_requires_authentication(): void
    {
        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(401);
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Senha atual incorreta',
            ]);

        $user->refresh();
        $this->assertTrue(Hash::check('oldpassword123', $user->password));
    }

    public function test_change_password_requires_current_password(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_change_password_requires_new_password(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['new_password']);
    }

    public function test_change_password_requires_new_password_confirmation(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['new_password']);
    }

    public function test_change_password_requires_password_confirmation_match(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'differentpassword',
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['new_password']);
    }

    public function test_change_password_enforces_minimum_length(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'new_password' => '123',
            'new_password_confirmation' => '123',
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['new_password']);
    }

    public function test_admin_user_can_change_password(): void
    {
        $admin = User::factory()->admin()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Senha alterada com sucesso',
            ]);
    }

    public function test_employee_user_can_change_password(): void
    {
        $employee = User::factory()->employee()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        Sanctum::actingAs($employee);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Senha alterada com sucesso',
            ]);
    }

    public function test_inactive_user_can_change_password(): void
    {
        $user = User::factory()->inactive()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Senha alterada com sucesso',
            ]);
    }

    public function test_change_password_with_same_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('samepassword123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'samepassword123',
            'new_password' => 'samepassword123',
            'new_password_confirmation' => 'samepassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Senha alterada com sucesso',
            ]);
    }

    public function test_change_password_response_structure(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Senha alterada com sucesso',
            ]);
    }

    public function test_change_password_validation_messages(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', []);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Dados de validação inválidos',
            ])
            ->assertJsonValidationErrors([
                'current_password',
                'new_password',
            ]);
    }

    public function test_change_password_handles_special_characters(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old@password#123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'old@password#123',
            'new_password' => 'new$password&456!',
            'new_password_confirmation' => 'new$password&456!',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('new$password&456!', $user->password));
    }

    public function test_change_password_handles_unicode_characters(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('senhaantiga123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'senhaantiga123',
            'new_password' => 'senhanova123ção',
            'new_password_confirmation' => 'senhanova123ção',
        ]);

        $response->assertStatus(200);

        $user->refresh();
        $this->assertTrue(Hash::check('senhanova123ção', $user->password));
    }
}
