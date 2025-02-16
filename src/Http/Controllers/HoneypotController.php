<?php

namespace Savrock\Siop\Http\Controllers;


use App\Http\Controllers\Controller;
use Savrock\Siop\Siop;


class HoneypotController extends Controller
{
    public function handle()
    {
        Siop::dispatchSecurityEvent('Honeypot triggered', [], 'honeypot', config('siop.honeypot_severity'));
        Siop::blockIP(request()->ip());
    }
}
