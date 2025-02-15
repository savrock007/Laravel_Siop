<?php

namespace Savrock\Siop\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Savrock\Siop\Models\Ip;

class BlockIps
{
    public function handle(Request $request, Closure $next)
    {
        if (config('siop.blocking_method') != 'middleware') {
            return $next($request);
        }

        $request_ip_hash = hash('sha256', $request->ip());

        $cacheKey = "blocked_ip:{$request_ip_hash}";
        $isBlocked = Cache::remember($cacheKey, now()->addMinutes(3), function () use ($request_ip_hash) {
            return Ip::where('ip_hash', $request_ip_hash)
                ->where('expires_at', '>', Carbon::now())
                ->exists();
        });

        if ($isBlocked) {
            abort(403, 'Blocked');
        }

        return $next($request);
    }
}
