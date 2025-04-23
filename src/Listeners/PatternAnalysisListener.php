<?php

namespace Savrock\Siop\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Savrock\Siop\Events\PatternAnalysisEvent;
use Savrock\Siop\Siop;

class PatternAnalysisListener implements ShouldQueue
{
    public $tries = 1;
    public $queue = 'security_events';
    public $logChannel = 'siop';

    private $historyLength = 20;

    private $cacheKeys = [
        'requests' => "security:patterns:all_requests",
        'RPM' => "security:stats:rpm",
        'TBRVariance' => "security:stats:tbr_variance",
    ];

    private $thresholds = [
        'RPM_V' => 0.1,
        'TBR_V' => 100,
    ];

    private array $history;

    public function handle(PatternAnalysisEvent $event)
    {
        $this->history = $this->getUnanalyzedRequests();
        if (!empty($this->history)) {
            $this->calculateStatisticalData();
            $this->analyze();
        }

    }

    private function getUnanalyzedRequests(): array
    {
        return Cache::pull($this->cacheKeys['requests'], []);
    }

    private function calculateStatisticalData()
    {

        $groupedByIp = collect($this->history)->groupBy('ip')->toArray();

        foreach ($groupedByIp as $ip => $ip_requests) {
            $this->trackRequestCount($ip, $ip_requests);
            $this->trackTimeBetweenRequests($ip, $ip_requests);
        }
    }

    private function trackRequestCount(string $ip, array $ip_requests)
    {
        $currentRPM = count($ip_requests) / config('siop.pattern_analysis_cooldown'); //Requests per minute
        $rpmHistory = $this->updateHistory($ip, $currentRPM, 'RPM');

        if (count($rpmHistory) >= 5) {
            $this->detectRequestSpike($ip, $rpmHistory, $currentRPM);
        }
    }

    private function detectRequestSpike(string $ip, array $rpmHistory, int $currentRPM)
    {
        $historicalAvg = array_sum($rpmHistory) / count($rpmHistory);
        $thresholdMultiplier = 3;
        $spikeThreshold = $historicalAvg * $thresholdMultiplier;

        if ($currentRPM > $spikeThreshold || $currentRPM >= 100) {
            $this->punish($ip, "RPM spike", ['avg_rpm' => round($historicalAvg, 2), 'detected_rpm' => $currentRPM]);
        }
    }


    private function trackTimeBetweenRequests(string $ip, array $ip_requests)
    {
        $timestamps = array_column($ip_requests, 'timestamp');
        $timeDiffs = [];
        for ($i = 1; $i < count($timestamps); $i++) {
            $time = ($timestamps[$i] - $timestamps[$i - 1]);

            //Normalize
            $time = $time * 10;
            $time = round($time, 2);
            $timeDiffs[] = $time;
        }

        if (count($timeDiffs) > 5) {
            $this->updateVariance($ip, $timeDiffs, 'TBRVariance');
        }
    }

    private function updateHistory(string $ip, $newData, string $key): array
    {
        $history = Cache::get($this->cacheKeys[$key], []);
        $history[$ip] = array_slice(array_merge($history[$ip] ?? [], (array)$newData), -$this->historyLength);
        Cache::put($this->cacheKeys[$key], $history);
        return $history[$ip];
    }

    private function updateVariance(string $ip, array $data, string $varianceKey)
    {
        $variances = Cache::get($this->cacheKeys[$varianceKey], []);

        $variance = $this->calculateVariance($data);
        if ($variance === null) {
            return;
        }

        $variances[$ip] = $variance;

        Cache::put($this->cacheKeys[$varianceKey], $variances);

    }

    private function calculateVariance(array $numbers): float|null
    {
        $count = count($numbers);
        if ($count < 2) return null;
        $mean = array_sum($numbers) / $count;
        $sumSquaredDiffs = array_sum(array_map(fn($num) => pow($num - $mean, 2), $numbers));
        return round($sumSquaredDiffs / ($count - 1), 2);
    }

    private function analyze()
    {

        $RPM = Cache::get($this->cacheKeys['RPM'], []);
        $TBRVariance = Cache::get($this->cacheKeys['TBRVariance'], []);

        foreach (array_keys($RPM) as $ip) {
            $TBR_V_ip = $TBRVariance[$ip] ?? null;
            $RPM_ip = $RPM[$ip] ?? null;

            if ($TBR_V_ip !== null && $TBR_V_ip < $this->thresholds['TBR_V']) {
                $this->punish($ip, 'Small time variance between requests', ['time_between_requests_variance' => $TBR_V_ip]);
            }
        }
    }

    private function punish(string $ip, string $reason, array $meta = [])
    {
        $meta['IP'] = $ip;
        Siop::dispatchSecurityEvent($reason, $meta, 'behaviour');
        $this->flush($ip);
    }

    private function flush(string $ip)
    {
        foreach ($this->cacheKeys as $key) {
            $data = Cache::get($key, []);
            if (isset($data[$ip])) {
                unset($data[$ip]);
                Cache::put($key, $data);
            }
        }
    }
}
