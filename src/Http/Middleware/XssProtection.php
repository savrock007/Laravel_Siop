<?php

namespace Savrock\Siop\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\TransformsRequest;
use Savrock\Siop\Models\SiopSettings;
use Savrock\Siop\Siop;


class XssProtection extends TransformsRequest
{

    protected array $excludedKeys = ['cookie'];
    private $mode;

    private $malicious = [];

    public function handle($request, Closure $next, $mode = 'clean')
    {
        $this->mode = $mode;

        $this->clean($request);

        if (empty($this->malicious)) {
            return $next($request);
        }

        $additional_meta = ['malicious_input' => $this->malicious];
        Siop::dispatchSecurityEvent('XSS detected', $additional_meta, 'xss', config('siop.xss_severity'));


        if ($this->mode === 'block') {
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

        $htmlPurifier = new \HTMLPurifier();
        $clean = $htmlPurifier->purify($value);

        if ($clean === $value) {
            return $clean;
        }

        $this->malicious[] = [$key => $value];

        return $clean;


    }


}
