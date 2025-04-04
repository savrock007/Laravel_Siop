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
        'RC' => "security:stats:rc",
        'TBRVariance' => "security:stats:tbr_variance",
    ];

    private $thresholds = [
        'RCVoV' => 0.1,
        'TBRVoV' => 100,
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
        $currentRC = count($ip_requests);
        $rcHistory = $this->updateHistory($ip, $currentRC, 'RC');

        if (count($rcHistory) >= 5) { // Ensure enough history for comparison
            $this->detectRequestSpike($ip, $rcHistory, $currentRC);
        }
    }

    private function detectRequestSpike(string $ip, array $rcHistory, int $currentRC)
    {
        $historicalAvg = array_sum($rcHistory) / count($rcHistory);
        $thresholdMultiplier = 2.; // Adjust sensitivity
        $spikeThreshold = $historicalAvg * $thresholdMultiplier;

        if ($currentRC > $spikeThreshold) {
            Log::channel($this->logChannel)->warning("🚨 SPIKE DETECTED for $ip: $currentRC requests (Avg: $historicalAvg)");
            $this->punish($ip);
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
        if ($variance == null) {
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
        Log::channel($this->logChannel)->info('---- ANALYSIS START ----');

        $RC = Cache::get($this->cacheKeys['RC'], []);
        $TBRVariance = Cache::get($this->cacheKeys['TBRVariance'], []);

        foreach (array_keys($TBRVariance) as $ip) {
            Log::channel($this->logChannel)->info("RC for $ip: " . json_encode($RC[$ip] ?? "No Data"));
            Log::channel($this->logChannel)->info("TBR_V for $ip: " . json_encode($TBRVariance[$ip]));


//            if (($RCVoV !== null && $RCVoV <= $this->thresholds['RCVoV']) ||
//                ($TBRVoV !== null && $TBRVoV <= $this->thresholds['TBRVoV'])) {
//                $this->punish($ip);
//            }
        }
        Log::channel($this->logChannel)->info('---- ANALYSIS END ----');
    }

    private function punish(string $ip)
    {
        Siop::dispatchSecurityEvent('Request spike', [], 'behaviour');
        Log::channel($this->logChannel)->info("PUNISH: $ip");
//        Siop::blockIP($ip);
    }
}
