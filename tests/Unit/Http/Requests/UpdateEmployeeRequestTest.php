<?php

namespace Tests\Unit\Http\Requests;

use Tests\TestCase;
use App\Http\Requests\UpdateEmployeeRequest;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class UpdateEmployeeRequestTest extends TestCase
{
    use RefreshDatabase;

    private function createRequest(array $data = [], ?User $user = null, ?Employee $employee = null): UpdateEmployeeRequest
    {
        $request = new UpdateEmployeeRequest();
        $request->replace($data);

        if ($user) {
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
        }

        if ($employee) {
            $request->setRouteResolver(function () use ($employee) {
                return new class($employee) {
                    private $employee;
                    public function __construct($employee) { $this->employee = $employee; }
                    public function parameter($key) { return $this->employee->id; }
                };
            });
        }

        return $request;
    }

    public function test_authorize_returns_true_for_admin_user(): void
    {
        $admin = User::factory()->admin()->create();
        $request = $this->createRequest([], $admin);

        $this->assertTrue($request->authorize());
    }

    public function test_authorize_returns_false_for_employee_user(): void
    {
        $employee = User::factory()->employee()->create();
        $request = $this->createRequest([], $employee);

        $this->assertFalse($request->authorize());
    }

    public function test_authorize_returns_false_for_null_user(): void
    {
        $request = $this->createRequest();

        $this->assertFalse($request->authorize());
    }

    public function test_validation_passes_with_valid_partial_data(): void
    {
        $existingEmployee = Employee::factory()->create();

        $validData = [
            'name' => 'João Silva Updated',
            'position' => 'Senior Developer',
        ];

        $request = $this->createRequest($validData, null, $existingEmployee);
        $validator = Validator::make($validData, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_all_fields_are_optional_with_sometimes_rule(): void
    {
        $existingEmployee = Employee::factory()->create();
        $request = $this->createRequest([], null, $existingEmployee);
        $validator = Validator::make([], $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_name_field_validation(): void
    {
        $existingEmployee = Employee::factory()->create();

        $data = ['name' => ''];
        $request = $this->createRequest($data, null, $existingEmployee);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());

        $data = ['name' => str_repeat('a', 256)];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_email_field_validation(): void
    {
        $existingEmployee = Employee::factory()->create();

        $data = ['email' => 'invalid-email'];
        $request = $this->createRequest($data, null, $existingEmployee);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());

        $data = ['email' => ''];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_email_uniqueness_ignores_current_user(): void
    {
        $existingEmployee = Employee::factory()->create();
        $anotherUser = User::factory()->create(['email' => 'another@example.com']);

        $data = ['email' => $existingEmployee->user->email];
        $request = $this->createRequest($data, null, $existingEmployee);
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());

        $data = ['email' => $anotherUser->email];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_password_field_validation(): void
    {
        $existingEmployee = Employee::factory()->create();

        $data = ['password' => '123'];
        $request = $this->createRequest($data, null, $existingEmployee);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());

        $data = ['password' => 'password123'];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_cpf_field_validation(): void
    {
        $existingEmployee = Employee::factory()->create();

        $data = ['cpf' => '12345678901'];
        $request = $this->createRequest($data, null, $existingEmployee);
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());
        $this->assertStringContainsString('CPF informado é inválido', $validator->errors()->first('cpf'));

        $data = ['cpf' => '1234567890'];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());
    }

    public function test_cpf_uniqueness_ignores_current_employee(): void
    {
        $existingEmployee = Employee::factory()->create();
        $anotherEmployee = Employee::factory()->create();

        $data = ['cpf' => $existingEmployee->cpf];
        $request = $this->createRequest($data, null, $existingEmployee);
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());

        $data = ['cpf' => $anotherEmployee->cpf];
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());
    }

    public function test_birth_date_validation(): void
    {
        $existingEmployee = Employee::factory()->create();

        $data = ['birth_date' => now()->addDay()->format('Y-m-d')];
        $request = $this->createRequest($data, null, $existingEmployee);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('birth_date', $validator->errors()->toArray());

        $data = ['birth_date' => '1990-05-15'];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_cep_field_validation(): void
    {
        $existingEmployee = Employee::factory()->create();

        $data = ['cep' => '0131010'];
        $request = $this->createRequest($data, null, $existingEmployee);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cep', $validator->errors()->toArray());

        $data = ['cep' => '01310100'];
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_is_active_field_validation(): void
    {
        $existingEmployee = Employee::factory()->create();

        $data = ['is_active' => true];
        $request = $this->createRequest($data, null, $existingEmployee);
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());

        $data = ['is_active' => false];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());

        $data = ['is_active' => 1];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());

        $data = ['is_active' => 0];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
    }

    public function test_prepare_for_validation_cleans_cpf_and_cep(): void
    {
        $existingEmployee = Employee::factory()->create();

        $data = [
            'cpf' => '111.444.777-35',
            'cep' => '01310-100',
        ];

        $request = $this->createRequest($data, null, $existingEmployee);

        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals('11144477735', $request->input('cpf'));
        $this->assertEquals('01310100', $request->input('cep'));
    }

    public function test_prepare_for_validation_handles_null_values(): void
    {
        $existingEmployee = Employee::factory()->create();

        $data = [
            'name' => 'João Silva',
        ];

        $request = $this->createRequest($data, null, $existingEmployee);

        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals('João Silva', $request->input('name'));
        $this->assertNull($request->input('cpf'));
        $this->assertNull($request->input('cep'));
    }

    public function test_get_employee_user_id_method(): void
    {
        $existingEmployee = Employee::factory()->create();
        $request = $this->createRequest([], null, $existingEmployee);

        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('getEmployeeUserId');
        $method->setAccessible(true);

        $userId = $method->invoke($request, $existingEmployee->id);

        $this->assertEquals($existingEmployee->user_id, $userId);
    }

    public function test_get_employee_user_id_returns_null_for_nonexistent_employee(): void
    {
        $request = $this->createRequest();

        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('getEmployeeUserId');
        $method->setAccessible(true);

        $userId = $method->invoke($request, 99999);

        $this->assertNull($userId);
    }

    public function test_custom_messages_are_returned(): void
    {
        $request = $this->createRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('email.email', $messages);
        $this->assertArrayHasKey('email.unique', $messages);
        $this->assertArrayHasKey('password.min', $messages);
        $this->assertArrayHasKey('full_name.required', $messages);
        $this->assertArrayHasKey('cpf.required', $messages);
        $this->assertArrayHasKey('cpf.size', $messages);
        $this->assertArrayHasKey('cpf.unique', $messages);
        $this->assertArrayHasKey('position.required', $messages);
        $this->assertArrayHasKey('birth_date.required', $messages);
        $this->assertArrayHasKey('birth_date.date', $messages);
        $this->assertArrayHasKey('birth_date.before', $messages);
        $this->assertArrayHasKey('cep.required', $messages);
        $this->assertArrayHasKey('cep.size', $messages);

        $this->assertEquals('O nome é obrigatório.', $messages['name.required']);
    }

    public function test_partial_update_scenarios(): void
    {
        $existingEmployee = Employee::factory()->create();

        $data = ['name' => 'New Name'];
        $request = $this->createRequest($data, null, $existingEmployee);
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());

        $data = ['email' => 'newemail@example.com'];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());

        $data = ['position' => 'New Position'];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());

        $data = [
            'name' => 'New Name',
            'email' => 'newemail@example.com',
            'position' => 'New Position',
        ];
        $validator = Validator::make($data, $request->rules());
        $this->assertFalse($validator->fails());
    }
}
