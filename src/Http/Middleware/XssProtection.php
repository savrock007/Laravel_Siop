<?php

namespace Savrock\Siop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Savrock\Siop\Facades\Siop;

class XssProtection
{
    protected array $excludedKeys = ['cookie'];

    public function handle(Request $request, Closure $next, $mode = 'clean', $report = true)
    {
        $headers = [];
        foreach ($request->headers->all() as $key => $value) {
            $headers[$key] = is_array($value) ? implode(', ', $value) : $value;
        }

        $input = array_merge($request->all(), $headers);


        foreach ($input as $key => $value) {

            if (in_array($key, $this->excludedKeys, true) || !$value) {
                continue;
            }


            $htmlPurifier = new \HTMLPurifier();
            $clean = $htmlPurifier->purify($value);
            
            if ($clean != $value) {
                if ($report) {
                    $additional_meta = ['malicious_input' => [$key => $value]];

                    Siop::dispatchSecurityEvent('XSS detected', $additional_meta, 'xss', config('siop.xss_severity'));
                }

                if ($mode === 'block') {
                    abort(403, 'XSS detected and blocked.');
                } elseif ($mode === 'clean') {
                    $request->merge([$key => $clean]);
                }
            }
        }

        return $next($request);
    }

    private function containsXss($input): bool
    {
        if (!is_string($input) || trim($input) === '') {
            return false; // Ignore empty and non-string inputs
        }

        // Decode URL-encoded characters to catch encoded attacks
        $decodedInput = urldecode($input);
        $decodedInput = html_entity_decode($decodedInput, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Convert to lowercase for case-insensitive matching
        $decodedInput = strtolower($decodedInput);

        // More refined XSS patterns
        $xss_patterns = [
            '*script*',
            '*onerror*',
            '/<script\b[^>]*>(.*?)<\/script>/is',  // <script>...</script>
            '/on[a-z]+\s*=\s*["\'].*?["\']/is',   // onX attributes (onmouseover, onclick, etc.)
            '/javascript\s*:/is',                  // javascript: in href or src
            '/vbscript\s*:/is',                     // vbscript:
            '/data\s*:/is',                         // data: URI
            '/expression\s*\(/is',                  // expression()
            '/(document|window)\.\w+/is',           // document.cookie, window.location, etc.
            '/eval\s*\(/is',                        // eval()
            '/settimeout\s*\(/is',                  // setTimeout()
            '/setinterval\s*\(/is',                 // setInterval()
            '/innerhtml\s*=/is',                    // innerHTML manipulation
            '/<iframe\b[^>]*>/is',                  // <iframe>
            '/<object\b[^>]*>/is',                  // <object>
            '/<embed\b[^>]*>/is',                   // <embed>
            '/<form\b[^>]*>/is',                    // <form>
            '/<meta\b[^>]*>/is',                    // <meta>
            '/<link\b[^>]*>/is',                    // <link>
            '/<style\b[^>]*>/is',                   // <style>
            '/<svg\b[^>]*>/is',                     // <svg>
            '/<base\b[^>]*>/is',                    // <base>
        ];

        // Check against known XSS patterns
        foreach ($xss_patterns as $pattern) {
            if (preg_match($pattern, $decodedInput)) {
                return true;
            }
        }

        // Detect encoded XSS attempts (%3Cscript%3E, etc.)
        if (preg_match('/%3Cscript%3E|%3C|%3E|%22|%27|%3D|%2F|%28|%29/i', $input)) {
            return true;
        }

        return false;
    }


}
