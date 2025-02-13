<?php

namespace Savrock\Siop\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Savrock\Siop\Facades\Siop;

class SqlInjectionProtection
{
    public function handle(Request $request, Closure $next, $mode = 'block', $report = true)
    {
        $input = $this->flattenInput($request);

        foreach ($input as $key => $value) {
            if ($this->containsSqlInjection($value)) {
                if ($report) {
                    Siop::dispatchSecurityEvent('SQL Injection detected', [], 'sql_injection', config('siop.sql_injection_severity'));
                }

                if ($mode === 'block') {
                    abort(403, 'SQL Injection detected and blocked.');
                } elseif ($mode === 'clean') {
                    $request->merge([$key => $this->sanitizeSqlInput($value)]);
                }
            }
        }

        return $next($request);
    }

    private function flattenInput(Request $request): array
    {
        $input = $request->all();
        $headers = array_map(fn($h) => is_array($h) ? implode(', ', $h) : $h, $request->headers->all());

        return array_merge($input, $headers);
    }

    private function containsSqlInjection(string $input): bool
    {
        if (!is_string($input) || trim($input) === '') {
            return false;
        }

        $decodedInput = urldecode($input);
        $decodedInput = html_entity_decode($decodedInput, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $decodedInput = strtolower($decodedInput);

        // Common SQL injection patterns
        $sql_patterns = [
            "/(\bunion\b.*\bselect\b)/is",         // UNION SELECT
            "/(\bselect\b.*\bfrom\b)/is",          // SELECT FROM
            "/(\bwhere\b.*\b(=|like|or)\b)/is",    // WHERE = OR LIKE
            "/(\bdrop\b.*\b(table|database)\b)/is", // DROP TABLE/DATABASE
            "/(\binsert\b.*\binto\b)/is",          // INSERT INTO
            "/(\bupdate\b.*\bset\b)/is",           // UPDATE SET
            "/(\bdelete\b.*\bfrom\b)/is",          // DELETE FROM
            "/(--|#|\/\*|\*\/)/",                  // SQL comments
            "/(sleep\(\d+\)|benchmark\(\d+,\d+\))/i", // Time-based injection
            "/('.*?=.*?')|(\b1=1\b)/i",            // Always-true conditions
        ];

        foreach ($sql_patterns as $pattern) {
            if (preg_match($pattern, $decodedInput)) {
                return true;
            }
        }

        return false;
    }

    private function sanitizeSqlInput(string $input): string
    {
        return preg_replace("/(--|#|\/\*|\*\/)/", '', $input); // Remove SQL comments
    }
}
