<?php

namespace Savrock\Siop;

use Illuminate\Support\Facades\Auth;

class MetaGenerator
{
    public static function generateMetadata()
    {

        $request = $request ?? request();

        return [
            'IP' => $request->ip(),
            'User' => request()->user()?->id ?? null,
            'Route' => $request->uri()->path() ?? 'unknown',
            'Method' => $request->method(),
//            'Headers' => $request->headers->all()
        ];
    }
}
