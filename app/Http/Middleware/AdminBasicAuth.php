<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminBasicAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedUser = (string) env('ADMIN_BASIC_USER', '');
        $expectedPass = (string) env('ADMIN_BASIC_PASS', '');

        if ($expectedUser === '' || $expectedPass === '') {
            return response('Admin credentials are not configured.', 500);
        }

        [$providedUser, $providedPass] = $this->extractCredentials($request);

        $valid = $providedUser !== null
            && $providedPass !== null
            && hash_equals($expectedUser, $providedUser)
            && hash_equals($expectedPass, $providedPass);

        if (! $valid) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="Donation Admin"',
            ]);
        }

        return $next($request);
    }

    private function extractCredentials(Request $request): array
    {
        $user = $request->server('PHP_AUTH_USER');
        $pass = $request->server('PHP_AUTH_PW');

        if (is_string($user) && is_string($pass)) {
            return [$user, $pass];
        }

        $header = (string) ($request->server('HTTP_AUTHORIZATION') ?? $request->header('Authorization', ''));

        if (! str_starts_with($header, 'Basic ')) {
            return [null, null];
        }

        $decoded = base64_decode(substr($header, 6), true);

        if (! is_string($decoded) || ! str_contains($decoded, ':')) {
            return [null, null];
        }

        [$user, $pass] = explode(':', $decoded, 2);

        return [$user, $pass];
    }
}
