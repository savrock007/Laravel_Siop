<?php

namespace Savrock\Siop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Savrock\Siop\Facades\Siop;

class SqlInjectionProtection
{
    public function handle(Request $request, Closure $next, $mode = 'clean')
    {

        return $next($request);
    }

}
