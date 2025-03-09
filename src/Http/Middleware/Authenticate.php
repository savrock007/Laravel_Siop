<?php

namespace Savrock\Siop\Http\Middleware;

use Savrock\Siop\Siop;

class Authenticate
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response|null
     */
    public function handle($request, $next)
    {
        if (! Siop::check($request)) {
            Siop::dispatchSecurityEvent('Attempt to access Siop panel',[],'Access Control');
            abort(403,'Unauthorized');
        }

        return $next($request);
    }
}
