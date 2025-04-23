<?php

namespace Savrock\Siop\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Session\TokenMismatchException;
use Closure;
use Savrock\Siop\Siop;

class SiopVerifyCsrfToken extends Middleware
{
    public function handle($request, Closure $next)
    {
        if (
            $this->isReading($request) ||
            $this->runningUnitTests() ||
            $this->inExceptArray($request) ||
            $this->tokensMatch($request)
        ) {
            return tap($next($request), function ($response) use ($request) {
                if ($this->shouldAddXsrfTokenCookie()) {
                    $this->addCookieToResponse($request, $response);
                }
            });
        }

        Siop::dispatchSecurityEvent('CSRF token mismatch', [],'CSRF','medium');

        throw new TokenMismatchException('CSRF token mismatch.');
    }
}
