<?php

namespace Savrock\Siop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Savrock\Siop\Events\PatternAnalysisEvent;

class PatternAnalysis
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('siop.enable_pattern_analysis')) {
            return $next($request);
        }

        $ip = $request->ip();
        $route = $request->path();

        $this->storeRequestHistory($ip, $route);

        if ($this->shouldTriggerEvent()) {
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
        $cooldown = config('siop.pattern_analysis_cooldown', 1); // Default: 5 minutes

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
        $entry = json_encode([
            'ip' => $ip,
            'route' => $route,
            'timestamp' => now()->timestamp,
        ]) ?? [];

        if (config('database.redis.client')) {
            Redis::lpush($cacheKey, $entry);
            Redis::ltrim($cacheKey, 0, 4999); // Keep only the last 5000 requests
            Redis::expire($cacheKey, 600);
        } else {
            $history = Cache::get($cacheKey, []);
            $history[] = $entry;
            Cache::put($cacheKey, array_slice($history, -5000), now()->addMinutes(10));
        }
    }
}
