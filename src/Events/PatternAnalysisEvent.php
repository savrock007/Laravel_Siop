<?php

namespace Savrock\Siop\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PatternAnalysisEvent
{
    use Dispatchable, SerializesModels;
}
