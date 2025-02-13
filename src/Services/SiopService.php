<?php

namespace Savrock\Siop\Services;

use Savrock\Siop\Events\NewSecurityEvent;
use Savrock\Siop\MetaGenerator;

class SiopService
{
    /**
     * @param string $message
     * @param array $meta
     * @param string $category
     * @param string $severity
     * @return void
     */
    public function dispatchSecurityEvent(string $message, array $meta = [], string $category = 'custom', string $severity = 'low'): void
    {
        /** @var MetaGenerator $metaGenerator */
        $metaGenerator = new (config("siop.meta_generator", MetaGenerator::class));
        $meta = array_merge($meta, $metaGenerator->generateMetadata());

        event(new NewSecurityEvent($message, $meta, $category, $severity));
    }

}
