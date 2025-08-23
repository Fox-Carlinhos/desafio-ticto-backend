<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CepService;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Mockery;

class CepServiceTest extends TestCase
{
    private CepService $cepService;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_get_address_by_cep_returns_null_for_invalid_cep(): void
    {
        $cepService = new CepService();

        $result = $cepService->getAddressByCep('123');
        $this->assertNull($result);

        $result = $cepService->getAddressByCep('1234567890');
        $this->assertNull($result);

        $result = $cepService->getAddressByCep('abc12345');
        $this->assertNull($result);
    }

    public function test_get_address_by_cep_formats_cep_before_validation(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'cep' => '01310-100',
                'logradouro' => 'Avenida Paulista',
                'bairro' => 'Bela Vista',
                'localidade' => 'São Paulo',
                'uf' => 'SP'
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cepService = $this->createCepServiceWithMockedClient($client);

        $result = $cepService->getAddressByCep('01310-100');

        $this->assertNotNull($result);
        $this->assertEquals('01310100', $result['cep']);
    }

    public function test_get_address_by_cep_uses_cache(): void
    {
        $cepData = [
            'cep' => '01310100',
            'street' => 'Avenida Paulista',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP'
        ];

        Cache::put('cep:01310100', $cepData, 3600);

        $cepService = new CepService();
        $result = $cepService->getAddressByCep('01310100');

        $this->assertEquals($cepData, $result);
    }

    public function test_viacep_provider_returns_normalized_data(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'cep' => '01310-100',
                'logradouro' => 'Avenida Paulista',
                'bairro' => 'Bela Vista',
                'localidade' => 'São Paulo',
                'uf' => 'SP'
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cepService = $this->createCepServiceWithMockedClient($client);
        $result = $cepService->getAddressByCep('01310100');

        $expectedResult = [
            'cep' => '01310100',
            'street' => 'Avenida Paulista',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP'
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function test_viacep_provider_handles_error_response(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['erro' => true])),
            new RequestException('Connection timeout', new Request('GET', 'test'))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cepService = $this->createCepServiceWithMockedClient($client);
        $result = $cepService->getAddressByCep('01310100');

        $this->assertNull($result);
    }

    public function test_fallback_to_second_provider_when_first_fails(): void
    {
        $mock = new MockHandler([
            new RequestException('Connection timeout', new Request('GET', 'test')),
            new Response(200, [], json_encode([
                'status' => 200,
                'cep' => '01310100',
                'address' => 'Avenida Paulista',
                'district' => 'Bela Vista',
                'city' => ['name' => 'São Paulo'],
                'state' => ['abbr' => 'SP']
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cepService = $this->createCepServiceWithMockedClient($client);
        $result = $cepService->getAddressByCep('01310100');

        $expectedResult = [
            'cep' => '01310100',
            'street' => 'Avenida Paulista',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP'
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function test_returns_null_when_all_providers_fail(): void
    {
        $mock = new MockHandler([
            new RequestException('Connection timeout', new Request('GET', 'test')),
            new RequestException('Connection timeout', new Request('GET', 'test'))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cepService = $this->createCepServiceWithMockedClient($client);
        $result = $cepService->getAddressByCep('01310100');

        $this->assertNull($result);
    }

    public function test_cep_aberto_provider_returns_normalized_data(): void
    {
        $mock = new MockHandler([
            new RequestException('Connection timeout', new Request('GET', 'test')),
            new Response(200, [], json_encode([
                'status' => 200,
                'cep' => '01310100',
                'address' => 'Avenida Paulista',
                'district' => 'Bela Vista',
                'city' => ['name' => 'São Paulo'],
                'state' => ['abbr' => 'SP']
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cepService = $this->createCepServiceWithMockedClient($client);
        $result = $cepService->getAddressByCep('01310100');

        $expectedResult = [
            'cep' => '01310100',
            'street' => 'Avenida Paulista',
            'neighborhood' => 'Bela Vista',
            'city' => 'São Paulo',
            'state' => 'SP'
        ];

        $this->assertEquals($expectedResult, $result);
    }

    public function test_cep_aberto_provider_handles_error_response(): void
    {
        $mock = new MockHandler([
            new RequestException('Connection timeout', new Request('GET', 'test')),
            new Response(200, [], json_encode(['status' => 400]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cepService = $this->createCepServiceWithMockedClient($client);
        $result = $cepService->getAddressByCep('01310100');

        $this->assertNull($result);
    }

    public function test_format_cep_removes_non_numeric_characters(): void
    {
        $cepService = new CepService();

        $this->assertEquals('01310100', $cepService->formatCep('01310-100'));
        $this->assertEquals('01310100', $cepService->formatCep('01.310-100'));
        $this->assertEquals('01310100', $cepService->formatCep('01 310 100'));
        $this->assertEquals('01310100', $cepService->formatCep('abc01310100def'));
    }

    public function test_is_valid_cep_validates_correctly(): void
    {
        $cepService = new CepService();

        $this->assertTrue($cepService->isValidCep('01310100'));
        $this->assertTrue($cepService->isValidCep('01310-100'));
        $this->assertFalse($cepService->isValidCep('0131010'));
        $this->assertFalse($cepService->isValidCep('013101000'));
        $this->assertFalse($cepService->isValidCep('abcdefgh'));
        $this->assertFalse($cepService->isValidCep(''));
    }

    public function test_format_cep_for_display_adds_hyphen(): void
    {
        $cepService = new CepService();

        $this->assertEquals('01310-100', $cepService->formatCepForDisplay('01310100'));
        $this->assertEquals('01310-100', $cepService->formatCepForDisplay('01310-100'));
        $this->assertEquals('01310-100', $cepService->formatCepForDisplay('01.310-100'));
    }

    public function test_successful_request_is_logged(): void
    {
        Log::spy();

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'cep' => '01310-100',
                'logradouro' => 'Avenida Paulista',
                'bairro' => 'Bela Vista',
                'localidade' => 'São Paulo',
                'uf' => 'SP'
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cepService = $this->createCepServiceWithMockedClient($client);
        $cepService->getAddressByCep('01310100');

        Log::shouldHaveReceived('info')
            ->once()
            ->with('CEP API call successful', Mockery::type('array'));
    }

    public function test_failed_request_is_logged(): void
    {
        Log::spy();

        $mock = new MockHandler([
            new RequestException('Connection timeout', new Request('GET', 'test')),
            new RequestException('Connection timeout', new Request('GET', 'test'))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cepService = $this->createCepServiceWithMockedClient($client);
        $cepService->getAddressByCep('01310100');

        Log::shouldHaveReceived('warning')
            ->twice()
            ->with('CEP API call failed', Mockery::type('array'));

        Log::shouldHaveReceived('error')
            ->once()
            ->with('CEP 01310100 não encontrado em nenhum provedor');
    }

    public function test_cache_stores_result_for_future_requests(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([
                'cep' => '01310-100',
                'logradouro' => 'Avenida Paulista',
                'bairro' => 'Bela Vista',
                'localidade' => 'São Paulo',
                'uf' => 'SP'
            ]))
        ]);

        $handlerStack = HandlerStack::create($mock);
        $client = new Client(['handler' => $handlerStack]);

        $cepService = $this->createCepServiceWithMockedClient($client);

        $result1 = $cepService->getAddressByCep('01310100');

        $result2 = $cepService->getAddressByCep('01310100');

        $this->assertEquals($result1, $result2);
        $this->assertTrue(Cache::has('cep:01310100'));
    }

    private function createCepServiceWithMockedClient(Client $client): CepService
    {
        $cepService = new CepService();

        $reflection = new \ReflectionClass($cepService);
        $httpClientProperty = $reflection->getProperty('httpClient');
        $httpClientProperty->setAccessible(true);
        $httpClientProperty->setValue($cepService, $client);

        return $cepService;
    }
}
