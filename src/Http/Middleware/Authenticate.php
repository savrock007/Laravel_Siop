<?php

namespace Savrock\Siop\Http\Middleware;

use Illuminate\Support\Facades\Gate;
use Savrock\Siop\Siop;

class Authenticate
{
    /**
     * Handle the incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return \Illuminate\Http\Response|null
     */
    public function handle($request, $next)
    {
        if (app()->environment('local')) {
            return $next($request);
        }

        if (!Gate::check('viewSiop', [$request->user()])) {
            Siop::dispatchSecurityEvent('Attempt to access Siop panel', [], 'Access Control');
            abort(403, 'Unauthorized');
        }


        return $next($request);
    }
}
