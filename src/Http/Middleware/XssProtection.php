<?php

namespace Savrock\Siop\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Savrock\Siop\Models\SiopSettings;
use Savrock\Siop\Siop;


class XssProtection extends TransformsRequest
{

    protected array $excludedKeys = ['cookie'];

    private array $malicious = [];


    public function handle($request, Closure $next, $mode = 'clean')
    {

        $this->clean($request);

        if (empty($this->malicious)) {
            return $next($request);
        }

        Siop::dispatchSecurityEvent('XSS detected', ['malicious_input' => $this->malicious], 'xss', config('siop.xss_severity'));


        if ($mode === 'block') {
            Siop::blockIP($request->ip());
            abort(403, 'XSS detected and blocked.');
        }
//        abort(400, 'XSS detected and blocked.');


        return $next($request);

    }


    public function transform($key, $value)
    {
        if ($value == null || $value == '') {
            return $value;
        }

        $patterns = [
            '/\bon\w+\b(?=\s*=)/i',
            '/<\s*script\b[^>]*>.*?<\s*\/script\s*>/is',
        ];

        foreach ($patterns as $pattern) {
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
            $this->malicious[] = [$key, $value];//record in malicious array if matches found
        }

        //clean value by inserting random space in event handler
        $modified = $value;
        foreach ($matches[0] as $handler) {
            $modified = str_replace($handler, $this->insertSpace($handler), $modified);
        }


        return $modified;
    }


    private function insertSpace(string $str): string
    {
        return strlen($str) > 3 ? substr_replace($str, ' ', 3, 0) : $str;
    }



}
