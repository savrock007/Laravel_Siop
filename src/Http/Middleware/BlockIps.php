<?php

namespace Savrock\Siop\Http\Middleware;


use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Savrock\Siop\Models\Ip;

class BlockIps
{
    public function handle(Request $request, Closure $next)
    {

        if (!config('siop.enable_ip_block')) {
            return $next($request);
        }


        $request_ip_hash = hash('sha256', $request->ip());
        $ip = Ip::where('ip_hash', $request_ip_hash)->where('expires_at', '>', Carbon::now())->first();

        if (!$ip) {
            return $next($request);
        } else {
            abort(403, 'Blocked');
        }


    }
}
