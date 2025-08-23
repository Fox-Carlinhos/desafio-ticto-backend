<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\Address;
use App\Models\TimeRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_be_created_with_valid_data(): void
    {
        $user = User::factory()->employee()->create();
        $manager = User::factory()->admin()->create();

        $employeeData = [
            'user_id' => $user->id,
            'full_name' => 'João Silva Santos',
            'cpf' => '12345678901',
            'position' => 'Desenvolvedor Backend',
            'birth_date' => '1990-05-15',
            'manager_id' => $manager->id,
        ];

        $employee = Employee::create($employeeData);

        $this->assertInstanceOf(Employee::class, $employee);
        $this->assertEquals('João Silva Santos', $employee->full_name);
        $this->assertEquals('12345678901', $employee->cpf);
        $this->assertEquals('Desenvolvedor Backend', $employee->position);
        $this->assertEquals($manager->id, $employee->manager_id);
    }

    public function test_birth_date_is_cast_to_carbon_date(): void
    {
        $employee = Employee::factory()->create(['birth_date' => '1990-05-15']);

        $this->assertInstanceOf(Carbon::class, $employee->birth_date);
        $this->assertEquals('1990-05-15', $employee->birth_date->format('Y-m-d'));
    }

    public function test_age_attribute_calculates_correctly(): void
    {
        $birthDate = Carbon::now()->subYears(30)->format('Y-m-d');
        $employee = Employee::factory()->create(['birth_date' => $birthDate]);

        $this->assertEquals(30, $employee->age);
    }

    public function test_formatted_cpf_attribute_formats_correctly(): void
    {
        $employee = Employee::factory()->create(['cpf' => '12345678901']);

        $this->assertEquals('123.456.789-01', $employee->formatted_cpf);
    }

    public function test_employee_belongs_to_user(): void
    {
        $user = User::factory()->employee()->create();
        $employee = Employee::factory()->for($user)->create();

        $this->assertInstanceOf(User::class, $employee->user);
        $this->assertEquals($user->id, $employee->user->id);
    }

    public function test_employee_belongs_to_manager(): void
    {
        $manager = User::factory()->admin()->create();
        $employee = Employee::factory()->withManager($manager)->create();

        $this->assertInstanceOf(User::class, $employee->manager);
        $this->assertEquals($manager->id, $employee->manager->id);
    }

    public function test_employee_has_one_address(): void
    {
        $employee = Employee::factory()->create();
        $address = Address::factory()->for($employee)->create();

        $this->assertInstanceOf(Address::class, $employee->address);
        $this->assertEquals($address->id, $employee->address->id);
    }

    public function test_employee_has_many_time_records(): void
    {
        $employee = Employee::factory()->create();
        $timeRecord1 = TimeRecord::factory()->for($employee)->create();
        $timeRecord2 = TimeRecord::factory()->for($employee)->create();

        $this->assertCount(2, $employee->timeRecords);
        $this->assertTrue($employee->timeRecords->contains($timeRecord1));
        $this->assertTrue($employee->timeRecords->contains($timeRecord2));
    }

    public function test_by_manager_scope_filters_employees_by_manager(): void
    {
        $manager1 = User::factory()->admin()->create();
        $manager2 = User::factory()->admin()->create();

        $employee1 = Employee::factory()->withManager($manager1)->create();
        $employee2 = Employee::factory()->withManager($manager1)->create();
        $employee3 = Employee::factory()->withManager($manager2)->create();

        $manager1Employees = Employee::byManager($manager1->id)->get();

        $this->assertCount(2, $manager1Employees);
        $this->assertTrue($manager1Employees->contains($employee1));
        $this->assertTrue($manager1Employees->contains($employee2));
        $this->assertFalse($manager1Employees->contains($employee3));
    }

    public function test_is_valid_cpf_validates_correct_cpf(): void
    {
        $validCpf = '11144477735';
        $invalidCpf = '12345678901';
        $shortCpf = '123456789';
        $repeatedCpf = '11111111111';

        $this->assertTrue(Employee::isValidCpf($validCpf));
        $this->assertFalse(Employee::isValidCpf($invalidCpf));
        $this->assertFalse(Employee::isValidCpf($shortCpf));
        $this->assertFalse(Employee::isValidCpf($repeatedCpf));
    }

    public function test_is_valid_cpf_handles_formatted_cpf(): void
    {
        $formattedCpf = '111.444.777-35';

        $this->assertTrue(Employee::isValidCpf($formattedCpf));
    }

    public function test_fillable_attributes_are_correct(): void
    {
        $employee = new Employee();
        $expectedFillable = [
            'user_id',
            'full_name',
            'cpf',
            'position',
            'birth_date',
            'manager_id',
        ];

        $this->assertEquals($expectedFillable, $employee->getFillable());
    }

    public function test_employee_factory_generates_valid_cpf(): void
    {
        $employee = Employee::factory()->create();

        $this->assertTrue(Employee::isValidCpf($employee->cpf));
    }

    public function test_employee_factory_with_manager_sets_manager(): void
    {
        $manager = User::factory()->admin()->create();
        $employee = Employee::factory()->withManager($manager)->create();

        $this->assertEquals($manager->id, $employee->manager_id);
    }

    public function test_employee_factory_position_method_sets_position(): void
    {
        $position = 'Analista de Sistemas';
        $employee = Employee::factory()->position($position)->create();

        $this->assertEquals($position, $employee->position);
    }

    public function test_employee_factory_senior_creates_older_employee(): void
    {
        $employee = Employee::factory()->senior()->create();
        $age = $employee->age;

        $this->assertGreaterThanOrEqual(35, $age);
        $this->assertLessThanOrEqual(55, $age);
    }

    public function test_employee_factory_junior_creates_younger_employee(): void
    {
        $employee = Employee::factory()->junior()->create();
        $age = $employee->age;

        $this->assertGreaterThanOrEqual(18, $age);
        $this->assertLessThanOrEqual(28, $age);
    }

    public function test_cpf_validation_rejects_known_invalid_patterns(): void
    {
        $invalidCpfs = [
            '00000000000',
            '11111111111',
            '22222222222',
            '33333333333',
            '44444444444',
            '55555555555',
            '66666666666',
            '77777777777',
            '88888888888',
            '99999999999',
        ];

        foreach ($invalidCpfs as $cpf) {
            $this->assertFalse(Employee::isValidCpf($cpf), "CPF {$cpf} should be invalid");
        }
    }
}
