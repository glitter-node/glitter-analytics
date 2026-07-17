<?php

namespace Modules\Glitter\Analytics\Http\Middleware;

use App\Helpers\ResponseHelper;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSameOrigin
{
    public function handle(Request $request, Closure $next): Response
    {
        $origin = $request->header('Origin');
        if (is_string($origin) && $origin !== '' && $this->normalize($origin) !== strtolower($request->getSchemeAndHttpHost())) {
            return ResponseHelper::validationError(
                ['origin' => [__('glitter-analytics::validation.pageview.origin')]],
                'messages.validation_failed',
                [],
                'glitter-analytics',
            );
        }

        return $next($request);
    }

    private function normalize(string $origin): ?string
    {
        $parts = parse_url($origin);
        if (! is_array($parts) || ! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        return strtolower($parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : ''));
    }
}
