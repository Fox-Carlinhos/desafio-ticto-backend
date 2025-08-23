<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Address;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $cities = [
            ['city' => 'São Paulo', 'state' => 'SP', 'neighborhoods' => ['Vila Madalena', 'Itaim Bibi', 'Pinheiros', 'Moema']],
            ['city' => 'Campinas', 'state' => 'SP', 'neighborhoods' => ['Cambuí', 'Centro', 'Barão Geraldo', 'Botafogo']],
            ['city' => 'Rio de Janeiro', 'state' => 'RJ', 'neighborhoods' => ['Copacabana', 'Ipanema', 'Leblon', 'Barra da Tijuca']],
            ['city' => 'Belo Horizonte', 'state' => 'MG', 'neighborhoods' => ['Savassi', 'Centro', 'Funcionários', 'Lourdes']],
            ['city' => 'Brasília', 'state' => 'DF', 'neighborhoods' => ['Asa Norte', 'Asa Sul', 'Lago Norte', 'Águas Claras']],
        ];

        $selectedCity = $this->faker->randomElement($cities);
        $neighborhood = $this->faker->randomElement($selectedCity['neighborhoods']);

        return [
            'cep' => $this->generateValidCep($selectedCity['state']),
            'street' => $this->faker->streetName(),
            'number' => $this->faker->buildingNumber(),
            'complement' => $this->faker->optional(0.3)->randomElement([
                'Apto ' . $this->faker->numberBetween(1, 200),
                'Casa ' . $this->faker->randomLetter(),
                'Bloco ' . $this->faker->randomElement(['A', 'B', 'C', 'D']),
                'Sala ' . $this->faker->numberBetween(1, 50),
            ]),
            'neighborhood' => $neighborhood,
            'city' => $selectedCity['city'],
            'state' => $selectedCity['state'],
        ];
    }

    /**
     * Generate a valid CEP based on state.
     */
    private function generateValidCep(string $state): string
    {
        $cepRanges = [
            'SP' => ['01000', '19999'],
            'RJ' => ['20000', '28999'],
            'MG' => ['30000', '39999'],
            'DF' => ['70000', '72999'],
        ];

        $range = $cepRanges[$state] ?? ['01000', '99999'];
        $cep = str_pad(
            $this->faker->numberBetween((int)$range[0], (int)$range[1]),
            5,
            '0',
            STR_PAD_LEFT
        );

        return $cep . str_pad($this->faker->numberBetween(0, 999), 3, '0', STR_PAD_LEFT);
    }

    /**
     * Create address for specific city.
     */
    public function city(string $cityName, string $state): static
    {
        $neighborhoods = [
            'São Paulo' => ['Vila Madalena', 'Itaim Bibi', 'Pinheiros', 'Moema'],
            'Campinas' => ['Cambuí', 'Centro', 'Barão Geraldo', 'Botafogo'],
            'Rio de Janeiro' => ['Copacabana', 'Ipanema', 'Leblon', 'Barra da Tijuca'],
            'Belo Horizonte' => ['Savassi', 'Centro', 'Funcionários', 'Lourdes'],
            'Brasília' => ['Asa Norte', 'Asa Sul', 'Lago Norte', 'Águas Claras'],
        ];

        $cityNeighborhoods = $neighborhoods[$cityName] ?? ['Centro'];

        return $this->state(fn (array $attributes) => [
            'city' => $cityName,
            'state' => $state,
            'neighborhood' => $this->faker->randomElement($cityNeighborhoods),
            'cep' => $this->generateValidCep($state),
        ]);
    }

    /**
     * Create address in Campinas
     */
    public function campinas(): static
    {
        return $this->city('Campinas', 'SP');
    }

    /**
     * Create commercial address.
     */
    public function commercial(): static
    {
        return $this->state(fn (array $attributes) => [
            'street' => 'Rua ' . $this->faker->randomElement([
                'dos Expedicionários',
                'Barão de Jaguara',
                'Francisco Glicério',
                'Coronel Quirino',
                'José Paulino'
            ]),
            'complement' => 'Sala ' . $this->faker->numberBetween(101, 1520),
        ]);
    }
}
