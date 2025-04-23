<?php

namespace Savrock\Siop;

use Carbon\Carbon;
use Exception;
use Savrock\Siop\Models\Ip;
use Savrock\Siop\Models\SecurityEvent;

class Siop
{

    /**
     * @param string $message
     * @param array $meta
     * @param string $category
     * @param string $severity
     * @return void
     */
    public static function dispatchSecurityEvent(string $message, array $meta = [], string $category = 'custom', string $severity = 'low'): void
    {
        /** @var MetaGenerator $metaGenerator */
        $metaGenerator = new (config("siop.meta_generator", MetaGenerator::class));
        $meta = array_replace_recursive($metaGenerator->generateMetadata(), $meta);


        SecurityEvent::create([
            'message' => $message,
            'category' => $category,
            'meta' => $meta,
            'severity' => $severity
        ]);

    }


    private static function calculateExpiresAt(string $input): Carbon
    {
        if ($input == '-1') {
            return Carbon::now()->addYears(100);
        }


        preg_match('/^(\d+)([smhdwy])$/', strtolower($input), $matches);

        [$full, $value, $unit] = $matches;
        $value = (int)$value;

        $now = Carbon::now();

        return match ($unit) {
            's' => $now->addSeconds($value),
            'm' => $now->addMinutes($value),
            'h' => $now->addHours($value),
            'd' => $now->addDays($value),
            'w' => $now->addWeeks($value),
            'y' => $now->addYears($value),
            default => throw new Exception("Unsupported time unit: $unit"),
        };
    }


    /**
     * @param string $ip
     * @param string|null $expires_at
     * @return void
     */
    public static function blockIP(string $ip, string $expires_at = null)
    {
        $ban_method = config('siop.blocking_method');

        if ($ban_method === 'fail2ban') {
            self::logBan($ip);
        }

        if ($expires_at == null) {
            if ($ban_method === 'fail2ban') {
                $expires_at = self::calculateExpiresAt(config('siop.fail2ban_ban_time'));
            } else {
                $expires_at = self::calculateExpiresAt(config('siop.block_time'));
            }

        }

        Ip::updateOrCreate([
            'ip_hash' => hash('sha256', $ip)
        ], [
            'ip' => $ip,
            'ban_method' => $ban_method,
            'expires_at' => $expires_at,
        ]);
    }

    /**
     * @param string $ip
     * @return void
     */
    public static function unblockIP(string $ip)
    {

        if (config('siop.blocking_method') === 'fail2ban') {
            self::logUnban($ip);
        }

        Ip::where('ip_hash', hash('sha256', $ip))?->delete();
    }

    protected static function logBan(string $ip): void
    {
        //TODO refactor pathing
        $logFile = config(storage_path('siop.fail2ban_log_path'), storage_path('logs/fail2ban.log'));
        $logMessage = sprintf("[%s] BAN_IP: %s", now()->toDateTimeString(), $ip);

        file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
    }


    protected static function logUnban(string $ip): void
    {
        //TODO refactor pathing
        $logFile = config(storage_path('siop.fail2ban_log_path'), storage_path('logs/fail2ban.log'));
        $logMessage = sprintf("[%s] UNBAN_IP: %s", now()->toDateTimeString(), $ip);

        file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

}
