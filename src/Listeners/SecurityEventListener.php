<?php

namespace Savrock\Siop\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Savrock\Siop\Events\NewSecurityEvent;
use Savrock\Siop\Models\SecurityEvent;

class SecurityEventListener implements ShouldQueue
{
    public $tries = 1;
    public $queue = 'security_events';


    public function __construct()
    {
    }


    public function handle(NewSecurityEvent $event)
    {
        $securityEvent = new SecurityEvent([
            'message' => $event->message,
            'category' => $event->category,
            'meta' => $event->meta,
            'severity' => $event->severity
        ]);

        $securityEvent->save();

    }


}
