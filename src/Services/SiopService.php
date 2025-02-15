<?php

namespace Savrock\Siop\Services;

use Savrock\Siop\Events\NewSecurityEvent;
use Savrock\Siop\MetaGenerator;
use Savrock\Siop\Models\Ip;

class SiopService
{
    /**
     * @param string $message
     * @param array $meta
     * @param string $category
     * @param string $severity
     * @return void
     */
    public function dispatchSecurityEvent(string $message, array $meta = [], string $category = 'custom', string $severity = 'low'): void
    {
        /** @var MetaGenerator $metaGenerator */
        $metaGenerator = new (config("siop.meta_generator", MetaGenerator::class));
        $meta = array_merge($meta, $metaGenerator->generateMetadata());

        event(new NewSecurityEvent($message, $meta, $category, $severity));
    }


    /**
     * @param string $ip
     * @param string|null $expires_at
     * @return void
     */
    public static function blockIP(string $ip, string $expires_at = null)
    {
        if (config('siop.blocking_method') === 'fail2ban') {
            self::logForFail2Ban($ip);
            return;
        }

        if ($expires_at == null) {
            $expires_at = now()->add(config('siop.block_time'), config('siop.block_time_unit'));
        }

        Ip::updateOrCreate([
            'ip_hash' => hash('sha256', $ip)
        ], [
            'ip' => $ip,
            'expires_at' => $expires_at,
        ]);
    }

    /**
     * @param string $ip
     * @return void
     */
    public static function unblockIP(string $ip)
    {
        Ip::where('ip_hash', hash('sha256', $ip))?->delete();
    }

    protected static function logForFail2Ban(string $ip): void
    {
        $logFile = config('siop.fail2ban_log_path', storage_path('logs/fail2ban.log'));
        $logMessage = sprintf("[%s] Blocked IP: %s", now()->toDateTimeString(), $ip);

        file_put_contents($logFile, $logMessage . PHP_EOL, FILE_APPEND | LOCK_EX);
    }

}
