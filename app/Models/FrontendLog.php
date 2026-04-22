<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrontendLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'meeting_code',
        'user_id',
        'action',
        'metadata',
    ];

    protected $casts = [
        'metadata'   => 'array',
        'created_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = now();
        });
    }
}
