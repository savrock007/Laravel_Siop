<?php

namespace Savrock\Siop\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Savrock\Siop\Events\PatternAnalysisEvent;

class PatternAnalysisListener implements ShouldQueue
{
    public $tries = 1;
    public $queue = 'security_events';
    public $logChannel = 'siop';

    private $historyLength = 20;

    private $cacheKeys = [
        'requests' => "security:patterns:all_requests",
        'RC' => "security:stats:rc",
        'RCVariance' => "security:stats:rc_variance",
        'TBR' => "security:stats:tbr",
        'TBRVariance' => "security:stats:tbr_variance",
    ];

    private $thresholds = [
        'RCVoV' => 0.1,
        'TBRVoV' => 100,
    ];

    public function handle(PatternAnalysisEvent $event)
    {
        $history = $this->getUnanalyzedRequests();
        if (!empty($history)) {
            $this->calculateStatisticalData($history);
            $this->analyze();
        }
    }

    private function getUnanalyzedRequests(): array
    {
        $history = Cache::pull($this->cacheKeys['requests'], []);
        return array_map(fn($entry) => json_decode($entry, true), $history);
    }

    private function calculateStatisticalData(array $history)
    {
        $groupedByIp = collect($history)->groupBy('ip')->toArray();

        foreach ($groupedByIp as $ip => $ip_requests) {
            $this->trackRequestCount($ip, $ip_requests);
            $this->trackTimeBetweenRequests($ip, $ip_requests);
        }
    }

    private function trackRequestCount(string $ip, array $ip_requests)
    {
        $requestCounts = $this->updateHistory($ip, count($ip_requests), 'RC');
        if (count($requestCounts) >= 2) {
            $this->updateVarianceHistory($ip, $requestCounts, 'RCVariance');
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
            $storedDiffs = $this->updateHistory($ip, $timeDiffs, 'TBR');
            $this->updateVarianceHistory($ip, $timeDiffs, 'TBRVariance');
        }
    }

    private function updateHistory(string $ip, $newData, string $key): array
    {
        $history = Cache::get($this->cacheKeys[$key], []);
        $history[$ip] = array_slice(array_merge($history[$ip] ?? [], (array)$newData), -$this->historyLength);
        Cache::put($this->cacheKeys[$key], $history);
        return $history[$ip];
    }

    private function updateVarianceHistory(string $ip, array $data, string $varianceKey)
    {
        $variances = Cache::get($this->cacheKeys[$varianceKey], []);


        $variance = $this->calculateVariance($data);
        if ($variance == null) {
            return;
        }

        $variances[$ip] = array_merge($variances[$ip] ?? [], [$variance]);
        $variances[$ip] = array_slice($variances[$ip], -$this->historyLength);

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
        $TBR = Cache::get($this->cacheKeys['TBR'], []);
        $RCVariance = Cache::get($this->cacheKeys['RCVariance'], []);
        $TBRVariance = Cache::get($this->cacheKeys['TBRVariance'], []);

        foreach (array_keys($RCVariance) as $ip) {
            $RCVoV = isset($RCVariance[$ip]) ? $this->calculateVariance($RCVariance[$ip]) : null;
            $TBRVoV = isset($TBRVariance[$ip]) ? $this->calculateVariance($TBRVariance[$ip]) : null;

            Log::channel($this->logChannel)->info("RC for $ip: " . json_encode($RC[$ip] ?? "No Data"));
            Log::channel($this->logChannel)->info("TBR for $ip: " . json_encode($TBR[$ip] ?? "No Data"));

            Log::channel($this->logChannel)->info("RC_V for $ip: " . json_encode($RCVariance[$ip]));
            Log::channel($this->logChannel)->info("TBR_V for $ip: " . json_encode($TBRVariance[$ip]));

            Log::channel($this->logChannel)->info("RCVoV for $ip: " . ($RCVoV ?? "No Data yet"));
            Log::channel($this->logChannel)->info("TBRVoV for $ip: " . ($TBRVoV ?? "No Data yet"));
            Log::channel($this->logChannel)->info('---- NEXT IP ----');


            if (($RCVoV !== null && $RCVoV <= $this->thresholds['RCVoV']) ||
                ($TBRVoV !== null && $TBRVoV <= $this->thresholds['TBRVoV'])) {
                $this->punish($ip);
            }
        }
        Log::channel($this->logChannel)->info('---- ANALYSIS END ----');
    }

    private function punish(string $ip)
    {
        Log::channel($this->logChannel)->info("PUNISH: $ip");
//        Siop::blockIP($ip);
    }
}
