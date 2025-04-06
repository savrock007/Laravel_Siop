<?php

namespace Savrock\Siop\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Savrock\Siop\Siop;

class SqlInjectionProtection extends TransformsRequest
{
    protected array $patterns = [
        '/\bUNION\b/i',
        '/\bSELECT\b.*\bFROM\b/i',
        '/\bINSERT\b.*\bINTO\b/i',
        '/\bUPDATE\b.*\bSET\b/i',
        '/\bDELETE\b.*\bFROM\b/i',
        '/\bDROP\b.*\bTABLE\b/i',
        '/--/',
        '/;.*$/',
        '/\bOR\b\s+1\s*=\s*1\b/i',
        '/\bAND\b\s+1\s*=\s*1\b/i',
        '/\bEXEC\b/i',
        '/\bSLEEP\s*\(/i',
    ];

    protected array $excludedKeys = ['cookie'];
    protected array $malicious = [];


    public function handle($request, Closure $next, $mode = 'clean')
    {

        $this->clean($request);


        if (empty($this->malicious)) {
            return $next($request);
        }

        Siop::dispatchSecurityEvent('SQL injection detected', ['malicious_input' => $this->malicious], 'xss', config('siop.xss_severity'));

        if ($mode === 'block') {
            Siop::blockIP($request->ip());
            abort(403, 'XSS detected and blocked.');
        }


        return $next($request);
    }

    public function transform($key, $value)
    {
        if ($value == null || $value == '') {
            return $value;
        }

        foreach ($this->patterns as $pattern) {
            $value = $this->processPattern($key, $value, $pattern);
        }

        return $value;
    }

    private function processPattern(string $key, string $value, $pattern): string
    {
        preg_match_all($pattern, $value, $matches);

        if (empty($matches[0])) {
            return $value;
        } else {
            $this->malicious[$key] = $value;//record in malicious array if matches found
        }

        //clean value by removing match
        $modified = $value;
        foreach ($matches[0] as $handler) {
            $modified = str_replace($handler, '', $modified);
        }

        return $modified;
    }

}
