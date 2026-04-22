<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'host_id',
        'meeting_code',
        'host_peer_id',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];
}
