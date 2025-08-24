<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CepService
{
    private Client $httpClient;
    private array $providers;

    public function __construct()
    {
        $this->httpClient = new Client([
            'timeout' => 10,
            'verify' => false,
        ]);

        $this->providers = [
            'viacep' => 'https://viacep.com.br/ws/{cep}/json/',
            'cep_aberto' => 'https://www.cepaberto.com/api/v3/cep?cep={cep}',
        ];
    }

    /**
     *  Search for CEP information with fallback between providers
     */
    public function getAddressByCep(string $cep): ?array
    {
        $cep = $this->formatCep($cep);

        if (!$this->isValidCep($cep)) {
            return null;
        }

        $cacheKey = "cep:{$cep}";

        return Cache::remember($cacheKey, 3600, function () use ($cep) {
            return $this->fetchFromProviders($cep);
        });
    }

    /**
     * Search for CEP in multiple providers with fallback
     */
    private function fetchFromProviders(string $cep): ?array
    {
        foreach ($this->providers as $provider => $url) {
            try {
                $address = $this->fetchFromProvider($provider, $url, $cep);
                if ($address) {
                    Log::info('CEP API call successful', [
                        'cep' => $cep,
                        'provider' => $provider,
                        'city' => $address['city'] ?? 'unknown',
                        'state' => $address['state'] ?? 'unknown',
                        'timestamp' => now()->toISOString(),
                    ]);
                    return $address;
                }
            } catch (RequestException $e) {
                Log::warning('CEP API call failed', [
                    'cep' => $cep,
                    'provider' => $provider,
                    'error' => $e->getMessage(),
                    'status_code' => $e->getCode(),
                    'timestamp' => now()->toISOString(),
                ]);
                continue;
            }
        }

        Log::error("CEP {$cep} não encontrado em nenhum provedor");
        return null;
    }

    /**
     * Search for CEP in a specific provider
     */
    private function fetchFromProvider(string $provider, string $url, string $cep): ?array
    {
        $requestUrl = str_replace('{cep}', $cep, $url);

        $response = $this->httpClient->get($requestUrl);

        if ($response->getStatusCode() !== 200) {
            return null;
        }

        $data = json_decode($response->getBody()->getContents(), true);

        return $this->normalizeAddress($provider, $data);
    }

    /**
     * Normalize answer from different providers
     */
    private function normalizeAddress(string $provider, array $data): ?array
    {
        switch ($provider) {
            case 'viacep':
                if (isset($data['erro'])) {
                    return null;
                }

                return [
                    'cep' => $this->formatCep($data['cep'] ?? ''),
                    'street' => $data['logradouro'] ?? '',
                    'neighborhood' => $data['bairro'] ?? '',
                    'city' => $data['localidade'] ?? '',
                    'state' => $data['uf'] ?? '',
                ];

            case 'cep_aberto':
                if (!isset($data['status']) || $data['status'] !== 200) {
                    return null;
                }

                return [
                    'cep' => $this->formatCep($data['cep'] ?? ''),
                    'street' => $data['address'] ?? '',
                    'neighborhood' => $data['district'] ?? '',
                    'city' => $data['city']['name'] ?? '',
                    'state' => $data['state']['abbr'] ?? '',
                ];

            default:
                return null;
        }
    }

    /**
     * Format CEP removing special characters
     */
    public function formatCep(string $cep): string
    {
        return preg_replace('/[^0-9]/', '', $cep);
    }

    /**
     * Validate CEP format
     */
    public function isValidCep(string $cep): bool
    {
        $cep = $this->formatCep($cep);
        return strlen($cep) === 8 && is_numeric($cep);
    }

    /**
     * Format CEP for display
     */
    public function formatCepForDisplay(string $cep): string
    {
        $cep = $this->formatCep($cep);
        return substr($cep, 0, 5) . '-' . substr($cep, 5, 3);
    }
}
