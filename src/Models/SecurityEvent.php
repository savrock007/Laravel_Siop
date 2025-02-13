<?php

namespace Savrock\Siop\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SecurityEvent extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'meta' => 'encrypted:array'
    ];

}
