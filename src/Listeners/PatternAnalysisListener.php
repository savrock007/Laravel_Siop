<?php

namespace Savrock\Siop\Listeners;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Savrock\Siop\Events\PatternAnalysisEvent;
use Savrock\Siop\Models\PatternRule;
use Savrock\Siop\Siop;

class PatternAnalysisListener implements ShouldQueue
{
    public $tries = 1;
    public $queue = 'security_events';


    public function handle(PatternAnalysisEvent $event)
    {
        $lastTimestamp = Cache::get('siop_last_analysis_time', 0);
        $history = $this->getUnanalyzedRequests($lastTimestamp);
        Cache::put('siop_last_analysis_time', now()->timestamp);

        if (empty($history)) {
            return;
        }


        $groupedByIp = $this->groupRequestsByIp($history);


        $rules = Cache::remember('siop_pattern_rules', 600, fn() => PatternRule::all());


        foreach ($groupedByIp as $ip => $requests) {
            foreach ($rules as $rule) {
                $this->matchesPattern($rule, $requests, $ip);
            }
        }

    }

    private function matchesPattern(PatternRule $rule, array $requests, $ip): bool
    {
        $targetRequests = array_filter($requests, fn($req) => $req['route'] === $rule->route);


        if (empty($targetRequests)) {
            return false;
        }


        // Check if any request matches the previous route within the timeframe
        foreach ($targetRequests as $targetRequest) {
            $target_time = Carbon::parse($targetRequest['timestamp'])->subSeconds($rule->time_frame)->timestamp;
            foreach ($requests as $prevRequest) {
                $real_time = $prevRequest['timestamp'];
                if ($prevRequest['route'] === $rule->previous_route && $real_time <= $target_time) {
                    return true;
                }
            }
        }

        Siop::dispatchSecurityEvent(
            "Pattern mismatch detected",
            ["IP" => $ip, 'rule' => $rule->id, 'reason' => "No request had been made to {$rule->previous_route} within {$rule->time_frame} seconds, before accessing {$rule->route}"],
            'pattern-mismatch'

        );

//        // Block the IP if action is 'block'
//        if ($rule->action === 'block') {
//            Siop::blockIP($ip);
//        }

        return false;

    }


    private function getUnanalyzedRequests(int $lastTimestamp): array
    {
        $cacheKey = "security:patterns:all_requests";

        if (config('database.redis.client')) {
            $history = Redis::lrange($cacheKey, 0, -1);
            return array_filter(
                array_map(fn($entry) => json_decode($entry, true), $history),
                fn($entry) => $entry['timestamp'] > $lastTimestamp
            );
        }

        return array_filter(
            Cache::get($cacheKey, []),
            fn($entry) => $entry['timestamp'] > $lastTimestamp
        );
    }

    private function groupRequestsByIp(array $history): array
    {
        $grouped = [];
        foreach ($history as $entry) {
            $grouped[$entry['ip']][] = $entry;
        }
        return $grouped;
    }
}
