<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Http\Middleware\EmployeeMiddleware;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;

class EmployeeMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_user_can_access_protected_route(): void
    {
        $user = User::factory()->employee()->create(['is_active' => true]);
        $employee = Employee::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/time-records');

        $this->assertNotEquals(401, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $response = $this->postJson('/api/time-records');

        $response->assertStatus(401);
    }

    public function test_admin_user_cannot_access_employee_route(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/time-records');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Acesso negado. Apenas funcionários podem acessar este recurso.',
            ]);
    }

    public function test_inactive_employee_cannot_access_protected_route(): void
    {
        $employee = User::factory()->employee()->inactive()->create();
        Sanctum::actingAs($employee);

        $response = $this->postJson('/api/time-records');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Usuário inativo',
            ]);
    }

    public function test_middleware_allows_access_for_valid_employee(): void
    {
        $employee = User::factory()->employee()->create(['is_active' => true]);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($employee) {
            return $employee;
        });

        $middleware = new EmployeeMiddleware();
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
    }

    public function test_middleware_blocks_unauthenticated_request(): void
    {
        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () {
            return null;
        });

        $middleware = new EmployeeMiddleware();
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Usuário não autenticado', $data['message']);
    }

    public function test_middleware_blocks_non_employee_user(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($admin) {
            return $admin;
        });

        $middleware = new EmployeeMiddleware();
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Acesso negado. Apenas funcionários podem acessar este recurso.', $data['message']);
    }

    public function test_middleware_blocks_inactive_employee(): void
    {
        $employee = User::factory()->employee()->inactive()->create();

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($employee) {
            return $employee;
        });

        $middleware = new EmployeeMiddleware();
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Usuário inativo', $data['message']);
    }

    public function test_employee_can_access_time_record_routes(): void
    {
        $user = User::factory()->employee()->create(['is_active' => true]);
        $employee = Employee::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/time-records');
        $response->assertStatus(201);

        $response = $this->getJson('/api/time-records');
        $response->assertStatus(200);

        $response = $this->getJson('/api/time-records/summary');
        $response->assertStatus(200);

        $response = $this->getJson('/api/time-records/today');
        $response->assertStatus(200);
    }

    public function test_middleware_works_with_different_http_methods(): void
    {
        $user = User::factory()->employee()->create(['is_active' => true]);
        $employee = Employee::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/time-records');
        $response->assertStatus(200);

        $response = $this->postJson('/api/time-records');
        $response->assertStatus(201);
    }

    public function test_middleware_response_format_is_consistent(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/time-records');

        $response->assertStatus(403)
            ->assertJsonStructure([
                'success',
                'message',
            ])
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_employee_middleware_preserves_request_data(): void
    {
        $employee = User::factory()->employee()->create(['is_active' => true]);

        $requestData = ['test' => 'data'];
        $request = Request::create('/test', 'POST', $requestData);
        $request->setUserResolver(function () use ($employee) {
            return $employee;
        });

        $middleware = new EmployeeMiddleware();
        $receivedRequest = null;

        $response = $middleware->handle($request, function ($req) use (&$receivedRequest) {
            $receivedRequest = $req;
            return response()->json(['success' => true]);
        });

        $this->assertSame(200, $response->getStatusCode());
        $this->assertInstanceOf(Request::class, $receivedRequest);

        assert($receivedRequest instanceof Request);
        $this->assertEquals($requestData['test'], $receivedRequest->input('test'));
    }

    public function test_employee_cannot_access_admin_routes(): void
    {
        $user = User::factory()->employee()->create(['is_active' => true]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/employees');
        $response->assertStatus(403);

        $response = $this->getJson('/api/reports/time-records');
        $response->assertStatus(403);

        $response = $this->getJson('/api/reports/summary');
        $response->assertStatus(403);
    }

    public function test_employee_can_access_change_password(): void
    {
        $user = User::factory()->employee()->create([
            'is_active' => true,
            'password' => bcrypt('oldpassword123'),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/change-password', [
            'current_password' => 'oldpassword123',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123',
        ]);

        $this->assertNotEquals(401, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_employee_can_access_me_endpoint(): void
    {
        $user = User::factory()->employee()->create(['is_active' => true]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200);
    }

    public function test_middleware_handles_user_role_changes(): void
    {
        $user = User::factory()->employee()->create(['is_active' => true]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/time-records');
        $this->assertNotEquals(403, $response->getStatusCode());

        $user->update(['role' => 'admin']);

        $response = $this->getJson('/api/time-records');
        $response->assertStatus(403);
    }

    public function test_middleware_handles_user_deactivation(): void
    {
        $user = User::factory()->employee()->create(['is_active' => true]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/time-records');
        $this->assertNotEquals(403, $response->getStatusCode());

        $user->update(['is_active' => false]);

        $response = $this->getJson('/api/time-records');
        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Usuário inativo',
            ]);
    }

    public function test_middleware_works_with_route_parameters(): void
    {
        $user = User::factory()->employee()->create(['is_active' => true]);
        $employee = Employee::factory()->for($user)->create();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/time-records?date=2024-01-15');
        $response->assertStatus(200);

        $response = $this->getJson('/api/time-records?start_date=2024-01-01&end_date=2024-01-31');
        $response->assertStatus(200);
    }
}
