<?php

namespace Savrock\Siop\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class Siop
 * @param string $message
 * @param array $meta additional metadata
 * @param string $category
 * @param string $severity
 * @return void
 * @method static void dispatchSecurityEvent(string $message, array $meta = [], string $category = 'custom', string $severity = 'low')
 */
class Siop extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'siop';
    }

    public static function getMetaGenerator(): string
    {
        return config('siop.meta_generator');
    }
}
