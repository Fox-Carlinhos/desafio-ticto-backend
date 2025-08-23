<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created_with_valid_data(): void
    {
        $userData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'role' => 'employee',
            'is_active' => true,
        ];

        $user = User::create($userData);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('João Silva', $user->name);
        $this->assertEquals('joao@example.com', $user->email);
        $this->assertEquals('employee', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertNotNull($user->password);
    }

    public function test_password_is_hashed_automatically(): void
    {
        $user = User::factory()->withPassword('plaintext123')->create();

        $this->assertNotEquals('plaintext123', $user->password);
        $this->assertTrue(password_verify('plaintext123', $user->password));
    }

    public function test_is_admin_method_returns_true_for_admin_role(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = User::factory()->employee()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($employee->isAdmin());
    }

    public function test_is_employee_method_returns_true_for_employee_role(): void
    {
        $admin = User::factory()->admin()->create();
        $employee = User::factory()->employee()->create();

        $this->assertFalse($admin->isEmployee());
        $this->assertTrue($employee->isEmployee());
    }

    public function test_active_scope_filters_active_users(): void
    {
        User::factory()->create(['is_active' => true]);
        User::factory()->create(['is_active' => true]);
        User::factory()->inactive()->create();

        $activeUsers = User::active()->get();

        $this->assertCount(2, $activeUsers);
        $this->assertTrue($activeUsers->every(fn($user) => $user->is_active));
    }

    public function test_user_has_employee_relationship(): void
    {
        $user = User::factory()->employee()->create();
        $employee = Employee::factory()->for($user)->create();

        $this->assertInstanceOf(Employee::class, $user->employee);
        $this->assertEquals($employee->id, $user->employee->id);
    }

    public function test_user_has_managed_employees_relationship(): void
    {
        $manager = User::factory()->admin()->create();
        $employee1 = Employee::factory()->withManager($manager)->create();
        $employee2 = Employee::factory()->withManager($manager)->create();

        $managedEmployees = $manager->managedEmployees;

        $this->assertCount(2, $managedEmployees);
        $this->assertTrue($managedEmployees->contains($employee1));
        $this->assertTrue($managedEmployees->contains($employee2));
    }

    public function test_is_active_attribute_is_cast_to_boolean(): void
    {
        $user = User::factory()->create(['is_active' => 1]);

        $this->assertIsBool($user->is_active);
        $this->assertTrue($user->is_active);
    }

    public function test_password_is_hidden_in_serialization(): void
    {
        $user = User::factory()->create();
        $array = $user->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_fillable_attributes_are_correct(): void
    {
        $user = new User();
        $expectedFillable = [
            'name',
            'email',
            'password',
            'role',
            'is_active',
        ];

        $this->assertEquals($expectedFillable, $user->getFillable());
    }

    public function test_email_verified_at_is_cast_to_datetime(): void
    {
        $user = User::factory()->create(['email_verified_at' => '2024-01-01 12:00:00']);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
    }

    public function test_user_factory_creates_valid_user(): void
    {
        $user = User::factory()->create();

        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
        $this->assertContains($user->role, ['admin', 'employee']);
        $this->assertIsBool($user->is_active);
    }

    public function test_admin_factory_creates_admin_user(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertEquals('admin', $admin->role);
        $this->assertTrue($admin->isAdmin());
    }

    public function test_employee_factory_creates_employee_user(): void
    {
        $employee = User::factory()->employee()->create();

        $this->assertEquals('employee', $employee->role);
        $this->assertTrue($employee->isEmployee());
    }

    public function test_inactive_factory_creates_inactive_user(): void
    {
        $user = User::factory()->inactive()->create();

        $this->assertFalse($user->is_active);
    }
}
