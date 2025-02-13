<?php

namespace Savrock\Siop\Models;

use Illuminate\Database\Eloquent\Model;

class Ip extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'ip' => 'encrypted',
            'meta' => 'encrypted:array'
        ];
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($blockedIp) {
            $blockedIp->ip_hash = hash('sha256', $blockedIp->ip);
        });
    }


}
