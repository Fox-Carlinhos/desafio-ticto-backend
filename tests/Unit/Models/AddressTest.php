<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Employee;
use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    public function test_address_can_be_created_with_valid_data(): void
    {
        $employee = Employee::factory()->create();

        $addressData = [
            'employee_id' => $employee->id,
            'cep' => '01310100',
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => 'Conjunto 1',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
        ];

        $address = Address::create($addressData);

        $this->assertInstanceOf(Address::class, $address);
        $this->assertEquals($employee->id, $address->employee_id);
        $this->assertEquals('01310100', $address->cep);
        $this->assertEquals('Avenida Paulista', $address->street);
        $this->assertEquals('1000', $address->number);
        $this->assertEquals('Conjunto 1', $address->complement);
        $this->assertEquals('Bela Vista', $address->neighborhood);
        $this->assertEquals('São Paulo', $address->city);
        $this->assertEquals('SP', $address->state);
    }

    public function test_address_belongs_to_employee(): void
    {
        $employee = Employee::factory()->create();
        $address = Address::factory()->for($employee)->create();

        $this->assertInstanceOf(Employee::class, $address->employee);
        $this->assertEquals($employee->id, $address->employee->id);
    }

    public function test_formatted_cep_attribute_formats_correctly(): void
    {
        $address = Address::factory()->create(['cep' => '01310100']);

        $this->assertEquals('01310-100', $address->formatted_cep);
    }

    public function test_full_address_attribute_combines_all_fields(): void
    {
        $address = Address::factory()->create([
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => 'Conjunto 1',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'cep' => '01310100',
        ]);

        $expectedAddress = 'Avenida Paulista, 1000, Conjunto 1 - Bela Vista, São Paulo - SP CEP: 01310-100';
        $this->assertEquals($expectedAddress, $address->full_address);
    }

    public function test_full_address_attribute_handles_missing_number(): void
    {
        $address = Address::factory()->create([
            'street' => 'Avenida Paulista',
            'number' => null,
            'complement' => null,
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'cep' => '01310100',
        ]);

        $expectedAddress = 'Avenida Paulista - Bela Vista, São Paulo - SP CEP: 01310-100';
        $this->assertEquals($expectedAddress, $address->full_address);
    }

    public function test_full_address_attribute_handles_missing_complement(): void
    {
        $address = Address::factory()->create([
            'street' => 'Avenida Paulista',
            'number' => '1000',
            'complement' => null,
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP',
            'cep' => '01310100',
        ]);

        $expectedAddress = 'Avenida Paulista, 1000 - Bela Vista, São Paulo - SP CEP: 01310-100';
        $this->assertEquals($expectedAddress, $address->full_address);
    }

    public function test_by_cep_scope_filters_by_cep(): void
    {
        $address1 = Address::factory()->create(['cep' => '01310100']);
        $address2 = Address::factory()->create(['cep' => '01310100']);
        $address3 = Address::factory()->create(['cep' => '04567890']);

        $filteredAddresses = Address::byCep('01310100')->get();

        $this->assertCount(2, $filteredAddresses);
        $this->assertTrue($filteredAddresses->contains($address1));
        $this->assertTrue($filteredAddresses->contains($address2));
        $this->assertFalse($filteredAddresses->contains($address3));
    }

    public function test_by_cep_scope_handles_formatted_cep(): void
    {
        $address = Address::factory()->create(['cep' => '01310100']);

        $filteredAddresses = Address::byCep('01310-100')->get();

        $this->assertCount(1, $filteredAddresses);
        $this->assertTrue($filteredAddresses->contains($address));
    }

    public function test_by_city_scope_filters_by_city(): void
    {
        $address1 = Address::factory()->create(['city' => 'São Paulo']);
        $address2 = Address::factory()->create(['city' => 'São Paulo']);
        $address3 = Address::factory()->create(['city' => 'Rio de Janeiro']);

        $filteredAddresses = Address::byCity('São Paulo')->get();

        $this->assertCount(2, $filteredAddresses);
        $this->assertTrue($filteredAddresses->contains($address1));
        $this->assertTrue($filteredAddresses->contains($address2));
        $this->assertFalse($filteredAddresses->contains($address3));
    }

    public function test_by_city_scope_performs_partial_match(): void
    {
        $address1 = Address::factory()->create(['city' => 'São Paulo']);
        $address2 = Address::factory()->create(['city' => 'Rio de Janeiro']);

        $filteredAddresses = Address::byCity('Paulo')->get();

        $this->assertCount(1, $filteredAddresses);
        $this->assertTrue($filteredAddresses->contains($address1));
        $this->assertFalse($filteredAddresses->contains($address2));
    }

    public function test_by_state_scope_filters_by_state(): void
    {
        $address1 = Address::factory()->create(['state' => 'SP']);
        $address2 = Address::factory()->create(['state' => 'SP']);
        $address3 = Address::factory()->create(['state' => 'RJ']);

        $filteredAddresses = Address::byState('SP')->get();

        $this->assertCount(2, $filteredAddresses);
        $this->assertTrue($filteredAddresses->contains($address1));
        $this->assertTrue($filteredAddresses->contains($address2));
        $this->assertFalse($filteredAddresses->contains($address3));
    }

    public function test_fillable_attributes_are_correct(): void
    {
        $address = new Address();
        $expectedFillable = [
            'employee_id',
            'cep',
            'street',
            'number',
            'complement',
            'neighborhood',
            'city',
            'state',
        ];

        $this->assertEquals($expectedFillable, $address->getFillable());
    }

    public function test_address_factory_creates_valid_address(): void
    {
        $address = Address::factory()->create();

        $this->assertNotNull($address->employee_id);
        $this->assertNotNull($address->cep);
        $this->assertNotNull($address->street);
        $this->assertNotNull($address->neighborhood);
        $this->assertNotNull($address->city);
        $this->assertNotNull($address->state);
        $this->assertInstanceOf(Employee::class, $address->employee);
    }

    public function test_scopes_can_be_chained(): void
    {
        $address1 = Address::factory()->create(['city' => 'São Paulo', 'state' => 'SP']);
        $address2 = Address::factory()->create(['city' => 'Campinas', 'state' => 'SP']);
        $address3 = Address::factory()->create(['city' => 'São Paulo', 'state' => 'RJ']);

        $filteredAddresses = Address::byState('SP')->byCity('São Paulo')->get();

        $this->assertCount(1, $filteredAddresses);
        $this->assertTrue($filteredAddresses->contains($address1));
        $this->assertFalse($filteredAddresses->contains($address2));
        $this->assertFalse($filteredAddresses->contains($address3));
    }

    public function test_cep_formatting_handles_different_input_formats(): void
    {
        $address1 = Address::factory()->create(['cep' => '01310100']);
        $address2 = Address::factory()->create(['cep' => '01310-100']);

        $this->assertEquals('01310-100', $address1->formatted_cep);
        $this->assertEquals('01310-100', $address2->formatted_cep);
    }
}
