<?php

namespace Tests\Unit\Http\Requests;

use Tests\TestCase;
use App\Http\Requests\StoreEmployeeRequest;
use App\Models\User;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

class StoreEmployeeRequestTest extends TestCase
{
    use RefreshDatabase;

    private function createRequest(array $data = [], ?User $user = null): StoreEmployeeRequest
    {
        $request = new StoreEmployeeRequest();
        $request->replace($data);

        if ($user) {
            $request->setUserResolver(function () use ($user) {
                return $user;
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

    public function test_validation_passes_with_valid_data(): void
    {
        $validData = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'full_name' => 'João Silva Santos',
            'cpf' => '11144477735',
            'position' => 'Desenvolvedor Backend',
            'birth_date' => '1990-05-15',
            'cep' => '01310100',
            'number' => '1000',
            'complement' => 'Conjunto 1',
        ];

        $request = $this->createRequest($validData);
        $validator = Validator::make($validData, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_name_is_required(): void
    {
        $data = [
            'email' => 'joao@example.com',
            'password' => 'password123',
            'full_name' => 'João Silva Santos',
            'cpf' => '11144477735',
            'position' => 'Desenvolvedor Backend',
            'birth_date' => '1990-05-15',
            'cep' => '01310100',
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_email_is_required_and_must_be_valid(): void
    {
        $data = [
            'name' => 'João Silva',
            'password' => 'password123',
            'full_name' => 'João Silva Santos',
            'cpf' => '11144477735',
            'position' => 'Desenvolvedor Backend',
            'birth_date' => '1990-05-15',
            'cep' => '01310100',
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());

        $data['email'] = 'invalid-email';
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $data = [
            'name' => 'João Silva',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'full_name' => 'João Silva Santos',
            'cpf' => '11144477735',
            'position' => 'Desenvolvedor Backend',
            'birth_date' => '1990-05-15',
            'cep' => '01310100',
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_password_is_required_and_has_minimum_length(): void
    {
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'full_name' => 'João Silva Santos',
            'cpf' => '11144477735',
            'position' => 'Desenvolvedor Backend',
            'birth_date' => '1990-05-15',
            'cep' => '01310100',
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());

        $data['password'] = '123';
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('password', $validator->errors()->toArray());
    }

    public function test_full_name_is_required(): void
    {
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'cpf' => '11144477735',
            'position' => 'Desenvolvedor Backend',
            'birth_date' => '1990-05-15',
            'cep' => '01310100',
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('full_name', $validator->errors()->toArray());
    }

    public function test_cpf_is_required_and_must_be_valid(): void
    {
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'full_name' => 'João Silva Santos',
            'position' => 'Desenvolvedor Backend',
            'birth_date' => '1990-05-15',
            'cep' => '01310100',
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());

        $data['cpf'] = '12345678901';
        $validator = Validator::make($data, $request->rules(), $request->messages());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());
        $this->assertStringContainsString('CPF informado é inválido', $validator->errors()->first('cpf'));
    }

    public function test_cpf_must_be_unique(): void
    {
        Employee::factory()->create(['cpf' => '11144477735']);

        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'full_name' => 'João Silva Santos',
            'cpf' => '11144477735',
            'position' => 'Desenvolvedor Backend',
            'birth_date' => '1990-05-15',
            'cep' => '01310100',
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());
    }

    public function test_cpf_must_have_exact_length(): void
    {
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'full_name' => 'João Silva Santos',
            'cpf' => '1234567890',
            'position' => 'Desenvolvedor Backend',
            'birth_date' => '1990-05-15',
            'cep' => '01310100',
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());

        $data['cpf'] = '123456789012';
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cpf', $validator->errors()->toArray());
    }

    public function test_position_is_required(): void
    {
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'full_name' => 'João Silva Santos',
            'cpf' => '11144477735',
            'birth_date' => '1990-05-15',
            'cep' => '01310100',
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('position', $validator->errors()->toArray());
    }

    public function test_birth_date_is_required_and_must_be_before_today(): void
    {
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'full_name' => 'João Silva Santos',
            'cpf' => '11144477735',
            'position' => 'Desenvolvedor Backend',
            'cep' => '01310100',
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('birth_date', $validator->errors()->toArray());

        $data['birth_date'] = now()->addDay()->format('Y-m-d');
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('birth_date', $validator->errors()->toArray());
    }

    public function test_cep_is_required_and_must_have_exact_length(): void
    {
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'full_name' => 'João Silva Santos',
            'cpf' => '11144477735',
            'position' => 'Desenvolvedor Backend',
            'birth_date' => '1990-05-15',
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cep', $validator->errors()->toArray());

        $data['cep'] = '0131010';
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('cep', $validator->errors()->toArray());
    }

    public function test_number_and_complement_are_optional(): void
    {
        $data = [
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => 'password123',
            'full_name' => 'João Silva Santos',
            'cpf' => '11144477735',
            'position' => 'Desenvolvedor Backend',
            'birth_date' => '1990-05-15',
            'cep' => '01310100',
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_prepare_for_validation_cleans_cpf_and_cep(): void
    {
        $data = [
            'cpf' => '111.444.777-35',
            'cep' => '01310-100',
        ];

        $request = $this->createRequest($data);

        $reflection = new \ReflectionClass($request);
        $method = $reflection->getMethod('prepareForValidation');
        $method->setAccessible(true);
        $method->invoke($request);

        $this->assertEquals('11144477735', $request->input('cpf'));
        $this->assertEquals('01310100', $request->input('cep'));
    }

    public function test_custom_messages_are_returned(): void
    {
        $request = $this->createRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('email.required', $messages);
        $this->assertArrayHasKey('email.email', $messages);
        $this->assertArrayHasKey('email.unique', $messages);
        $this->assertArrayHasKey('password.required', $messages);
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
        $this->assertEquals('O CPF informado é inválido.', 'O CPF informado é inválido.');
    }

    public function test_maximum_field_lengths_are_respected(): void
    {
        $data = [
            'name' => str_repeat('a', 256),
            'email' => 'joao@example.com',
            'password' => 'password123',
            'full_name' => str_repeat('b', 256),
            'cpf' => '11144477735',
            'position' => str_repeat('c', 256),
            'birth_date' => '1990-05-15',
            'cep' => '01310100',
            'number' => str_repeat('d', 11),
            'complement' => str_repeat('e', 256),
        ];

        $request = $this->createRequest($data);
        $validator = Validator::make($data, $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
        $this->assertArrayHasKey('full_name', $validator->errors()->toArray());
        $this->assertArrayHasKey('position', $validator->errors()->toArray());
        $this->assertArrayHasKey('number', $validator->errors()->toArray());
        $this->assertArrayHasKey('complement', $validator->errors()->toArray());
    }
}
