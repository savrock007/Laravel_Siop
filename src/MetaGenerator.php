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
            'User' => Auth::user()?->id ?? null,
            'Route' => $request->route()->uri() ?? 'unknown',
            'Method' => $request->method(),
//            'Headers' => $request->headers->all()
        ];
    }
}
