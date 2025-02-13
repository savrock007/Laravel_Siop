<?php

namespace Savrock\Siop\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Savrock\Siop\MetaGenerator;

class NewSecurityEvent
{
    use Dispatchable;


    public string $message;
    public array $meta;
    public string $category;
    public string $severity;

    /**
     * @param string $message
     * @param array $meta
     * @param string $category
     * @param $severity
     */
    public function __construct(string $message, array $meta, string $category = 'custom', $severity = 'low')
    {
        $this->message = $message;
        $this->meta = $meta;
        $this->category = $category;
        $this->severity = $severity;
    }


    // Required for code completion does not change functionality
    public static function dispatch(string $message, array $meta, string $category = 'custom', $severity = 'low')
    {
        return event(new self($message, $meta, $category, $severity));
    }

}
