<?php

namespace Savrock\Siop\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Savrock\Siop\Events\NewSecurityEvent;
use Savrock\Siop\Models\SecurityEvent;
use Savrock\Siop\Notifier;

class SecurityEventListener implements ShouldQueue
{
    public $tries = 1;
    public $queue = 'default';

    private Notifier $notifier;

    public function __construct()
    {
        $this->notifier = new (config("siop.notifier", Notifier::class));
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


        //notify of category is configured to send notifications
        if (config('siop.notifications.' . $event->category, false)) {
            $this->notifier->notify($event);
        }
    }


}
