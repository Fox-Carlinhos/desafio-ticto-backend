<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;
use App\Http\Middleware\AdminMiddleware;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;

class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_user_can_access_protected_route(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/employees');

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_access_protected_route(): void
    {
        $response = $this->getJson('/api/employees');

        $response->assertStatus(401);
    }

    public function test_employee_user_cannot_access_admin_route(): void
    {
        $employee = User::factory()->employee()->create(['is_active' => true]);
        Sanctum::actingAs($employee);

        $response = $this->getJson('/api/employees');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Acesso negado. Apenas administradores podem acessar este recurso.',
            ]);
    }

    public function test_inactive_admin_cannot_access_protected_route(): void
    {
        $admin = User::factory()->admin()->inactive()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/employees');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Usuário inativo',
            ]);
    }

    public function test_middleware_allows_access_for_valid_admin(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($admin) {
            return $admin;
        });

        $middleware = new AdminMiddleware();
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

        $middleware = new AdminMiddleware();
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(401, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Usuário não autenticado', $data['message']);
    }

    public function test_middleware_blocks_non_admin_user(): void
    {
        $employee = User::factory()->employee()->create(['is_active' => true]);

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($employee) {
            return $employee;
        });

        $middleware = new AdminMiddleware();
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Acesso negado. Apenas administradores podem acessar este recurso.', $data['message']);
    }

    public function test_middleware_blocks_inactive_admin(): void
    {
        $admin = User::factory()->admin()->inactive()->create();

        $request = Request::create('/test', 'GET');
        $request->setUserResolver(function () use ($admin) {
            return $admin;
        });

        $middleware = new AdminMiddleware();
        $response = $middleware->handle($request, function ($req) {
            return response()->json(['success' => true]);
        });

        $this->assertEquals(403, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('Usuário inativo', $data['message']);
    }

    public function test_admin_can_access_employee_management_routes(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/employees');
        $response->assertStatus(200);

        $employee = $this->createCompleteEmployee();

        $response = $this->getJson("/api/employees/{$employee->id}");
        $response->assertStatus(200);

        $response = $this->putJson("/api/employees/{$employee->id}", [
            'name' => 'Updated Name',
        ]);
        $response->assertStatus(200);

        $response = $this->deleteJson("/api/employees/{$employee->id}");
        $response->assertStatus(200);
    }

    public function test_admin_can_access_reports_routes(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/reports/time-records');
        $response->assertStatus(200);

        $response = $this->getJson('/api/reports/summary');
        $response->assertStatus(200);

        $response = $this->getJson('/api/reports/export?start_date=2024-01-01&end_date=2024-01-31');
        $response->assertStatus(200);
    }

    public function test_middleware_works_with_different_http_methods(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/employees', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'full_name' => 'Test User Full Name',
            'cpf' => '11144477735',
            'position' => 'Developer',
            'birth_date' => '1990-01-01',
            'cep' => '01310100',
        ]);

        $this->assertNotEquals(401, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_middleware_response_format_is_consistent(): void
    {
        $employee = User::factory()->employee()->create(['is_active' => true]);
        Sanctum::actingAs($employee);

        $response = $this->getJson('/api/employees');

        $response->assertStatus(403)
            ->assertJsonStructure([
                'success',
                'message',
            ])
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_admin_middleware_preserves_request_data(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);

        $requestData = ['test' => 'data'];
        $request = Request::create('/test', 'POST', $requestData);
        $request->setUserResolver(function () use ($admin) {
            return $admin;
        });

        $middleware = new AdminMiddleware();
        $receivedRequest = null;

        $response = $middleware->handle($request, function ($req) use (&$receivedRequest) {
            $receivedRequest = $req;
            return response()->json(['success' => true]);
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertInstanceOf(Request::class, $receivedRequest);
        assert($receivedRequest instanceof Request);
        $this->assertEquals($requestData['test'], $receivedRequest->input('test'));
    }

    public function test_middleware_works_with_route_parameters(): void
    {
        $admin = User::factory()->admin()->create(['is_active' => true]);
        $employee = $this->createCompleteEmployee();

        Sanctum::actingAs($admin);

        $response = $this->getJson("/api/employees/{$employee->id}");

        $response->assertStatus(200);
    }
}
