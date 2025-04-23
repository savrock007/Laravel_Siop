<?php

namespace Savrock\Siop\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Savrock\Siop\Models\SiopSettings;
use Savrock\Siop\Siop;


class XssProtection extends TransformsRequest
{
    protected array $patterns = [
        '/\bon\w+\b(?=\s*=)/i',
        '/<\s*script\b[^>]*>.*?<\s*\/script\s*>/is',
        '/\bjavascript:\S+/i'
    ];

    protected array $excludedKeys = ['cookie'];
    private array $malicious = [];


    public function handle($request, Closure $next, $mode = 'clean')
    {
        $start = microtime(true);

        $this->clean($request);

        if (empty($this->malicious)) {
            return $next($request);
        }

        Siop::dispatchSecurityEvent('XSS detected', ['malicious_input' => $this->malicious], 'xss', config('siop.xss_severity'));


        if ($mode === 'block') {
            Siop::blockIP($request->ip());
            abort(403, 'XSS detected and blocked.');
        }

        $end = microtime(true);
        $response = $next($request);
        $response->headers->set('X-Xss-Middleware-Time', ($end-$start)* 1000);
        return $response;

    }


    public function transform($key, $value)
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        foreach ($this->patterns as $pattern) {
            $value = $this->processPattern($key, $value, $pattern);
        }
        return $value;

    }


    private function processPattern(string $key, string $value, $pattern): string
    {
        if (!preg_match($pattern, $value)) {
            return $value;
        }

        $this->malicious[$key] = $value;

        return preg_replace_callback($pattern, function ($matches) {
            return $this->insertSpace($matches[0]);
        }, $value);
    }



    private function insertSpace(string $str): string
    {
        return substr($str, 0, 3) . ' ' . substr($str, 3);
    }



}
