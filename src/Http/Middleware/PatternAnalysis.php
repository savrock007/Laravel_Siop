<?php

namespace Savrock\Siop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Savrock\Siop\Events\PatternAnalysisEvent;
use Savrock\Siop\Models\Ip;

class PatternAnalysis
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('siop.enable_pattern_analysis')) {
            return $next($request);
        }

        $ip = $request->ip();

        //START DEBUG ONLY
        $ip = $request->header('X-Forwarded-For') ?? $request->ip();
        if (Ip::where('ip_hash', hash('sha256', $ip))->exists()) {
            return response('Unauthorized', 403);
        }
        // END DEBUG ONLY

        $route = $request->path();

        $this->storeRequestHistory($ip, $route);

        if ($this->shouldTriggerEvent()) {
//            dd('trigger');
            event(new PatternAnalysisEvent());
        }

        return $next($request);
    }

    /**
     * Determines if enough time has passed since the last event dispatch.
     */
    private function shouldTriggerEvent(): bool
    {
        $cacheKey = "security:pattern_analysis:last_event";
        $cooldown = config('siop.pattern_analysis_cooldown');

        if (Cache::has($cacheKey)) {
            return false;
        }

        Cache::put($cacheKey, true, now()->addMinutes($cooldown));
        return true;
    }

    /**
     * Stores request history using Redis (if available) or Laravel cache.
     */
    private function storeRequestHistory(string $ip, string $route): void
    {
        $cacheKey = "security:patterns:all_requests";
        $entry = [
            'ip' => $ip,
            'user' => \Auth::user()?->id,
            'route' => $route,
            'timestamp' => microtime(true),
        ] ?? [];


        $history = Cache::get($cacheKey, []);
        $history[] = $entry;
        Cache::put($cacheKey, array_slice($history, -5000), now()->addMinutes(config('siop.pattern_analysis_cooldown') + 1));
    }
}
