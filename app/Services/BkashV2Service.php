<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BkashV2Service
{
    private const TOKEN_CACHE_KEY = 'bkash_token';
    private const REFRESH_TOKEN_CACHE_KEY = 'bkash_refresh_token';

    public function createPayment(array $payload): array
    {
        $credentials = $this->getCredentials();
        $token = $this->getToken();

        $requestPayload = [
            'mode' => '0011',
            'payerReference' => $this->resolvePayerReference((string) ($payload['payer_reference'] ?? '')),
            'callbackURL' => (string) $payload['callback_url'],
            'amount' => number_format((float) $payload['amount'], 2, '.', ''),
            'currency' => 'BDT',
            'intent' => 'sale',
            'merchantInvoiceNumber' => (string) $payload['merchant_invoice_number'],
        ];

        $response = $this->withToken($token, $credentials['app_key'])
            ->post('/tokenized-checkout/payment/create', $requestPayload);

        $data = $response->json() ?? [];
        $this->assertApiSuccess($response, $data, 'Failed to create bKash payment');

        return $data;
    }

    public function executePayment(string $paymentId): array
    {
        $credentials = $this->getCredentials();
        $token = $this->getToken();

        $response = $this->withToken($token, $credentials['app_key'])
            ->post('/tokenized-checkout/payment/execute', [
                'paymentId' => $paymentId,
            ]);

        $data = $response->json() ?? [];
        $this->assertApiSuccess($response, $data, 'Payment execution failed');

        return $data;
    }

    private function getToken(): string
    {
        $cachedToken = Cache::get(self::TOKEN_CACHE_KEY);

        if (is_string($cachedToken) && $cachedToken !== '') {
            return $cachedToken;
        }

        $refreshToken = Cache::get(self::REFRESH_TOKEN_CACHE_KEY);

        if (is_string($refreshToken) && $refreshToken !== '') {
            try {
                return $this->refreshAndCacheToken($refreshToken);
            } catch (\Throwable) {
            }
        }

        return $this->fetchAndCacheToken();
    }

    private function fetchAndCacheToken(): string
    {
        $credentials = $this->getCredentials();

        $response = $this->baseClient()
            ->withHeaders([
                'username' => $credentials['username'],
                'password' => $credentials['password'],
            ])
            ->post('/tokenized-checkout/auth/grant-token', [
                'app_key' => $credentials['app_key'],
                'app_secret' => $credentials['app_secret'],
            ]);

        return $this->cacheTokenFromResponse($response, 'Failed to get bKash token');
    }

    private function refreshAndCacheToken(string $refreshToken): string
    {
        $credentials = $this->getCredentials();

        $response = $this->baseClient()
            ->withHeaders([
                'username' => $credentials['username'],
                'password' => $credentials['password'],
            ])
            ->post('/tokenized-checkout/auth/refresh-token', [
                'app_key' => $credentials['app_key'],
                'app_secret' => $credentials['app_secret'],
                'refresh_token' => $refreshToken,
            ]);

        return $this->cacheTokenFromResponse($response, 'Failed to refresh bKash token');
    }

    private function cacheTokenFromResponse(Response $response, string $errorPrefix): string
    {
        $data = $response->json() ?? [];

        if (! $response->successful()) {
            throw new \RuntimeException($errorPrefix.': '.$this->extractApiErrorMessage($data, $response->status()));
        }

        $token = $data['id_token'] ?? $data['token'] ?? $data['access_token'] ?? null;

        if (! is_string($token) || $token === '') {
            throw new \RuntimeException('Token not found in bKash response');
        }

        $expiresIn = (int) ($data['expires_in'] ?? 3600);
        $ttl = max(1, $expiresIn - 60);
        Cache::put(self::TOKEN_CACHE_KEY, $token, now()->addSeconds($ttl));

        if (! empty($data['refresh_token'])) {
            Cache::put(self::REFRESH_TOKEN_CACHE_KEY, $data['refresh_token'], now()->addDays(29));
        }

        return $token;
    }

    private function getCredentials(): array
    {
        $credentials = [
            'username' => (string) config('bkash.credentials.username'),
            'password' => (string) config('bkash.credentials.password'),
            'app_key' => (string) config('bkash.credentials.app_key'),
            'app_secret' => (string) config('bkash.credentials.app_secret'),
            'base_url' => $this->resolveBaseUrl(),
        ];

        foreach (['username', 'password', 'app_key', 'app_secret', 'base_url'] as $key) {
            if ($credentials[$key] === '') {
                throw new \RuntimeException("Missing bKash configuration: {$key}");
            }
        }

        return $credentials;
    }

    private function resolveBaseUrl(): string
    {
        $isSandbox = (bool) config('bkash.sandbox', true);
        $raw = $isSandbox
            ? (string) config('bkash.sandbox_base_url', 'https://tokenized.sandbox.bka.sh/v2')
            : (string) config('bkash.live_base_url', 'https://tokenized.pay.bka.sh/v2');

        return rtrim($raw, '/');
    }

    private function baseClient()
    {
        return Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->baseUrl($this->resolveBaseUrl())->timeout(30);
    }

    private function withToken(string $token, string $appKey)
    {
        return $this->baseClient()->withHeaders([
            'Authorization' => $token,
            'X-APP-Key' => $appKey,
        ]);
    }

    private function assertApiSuccess(Response $response, array $data, string $prefix): void
    {
        if (! $response->successful()) {
            throw new \RuntimeException($prefix.': '.$this->extractApiErrorMessage($data, $response->status()));
        }

        $statusCode = (string) ($data['statusCode'] ?? '');
        if ($statusCode !== '' && $statusCode !== '0000') {
            throw new \RuntimeException($prefix.': '.$this->extractApiErrorMessage($data, $response->status()));
        }

        if (! empty($data['externalCode']) && (string) $data['externalCode'] !== '0000') {
            throw new \RuntimeException($prefix.': '.$this->extractApiErrorMessage($data, $response->status()));
        }
    }

    private function extractApiErrorMessage(array $data, int $status): string
    {
        return (string) ($data['errorMessageEn'] ?? $data['statusMessage'] ?? $data['message'] ?? ('HTTP '.$status));
    }

    private function resolvePayerReference(string $raw): string
    {
        $trimmed = trim($raw);
        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        if (strlen($digits) >= 10) {
            return $digits;
        }

        if ($trimmed !== '') {
            return $trimmed;
        }

        return '01700000000';
    }
}
