<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingChat extends Model
{
    public $timestamps = false;

    protected $fillable = ['meeting_code', 'sender', 'message', 'sent_at'];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            if (! $model->sent_at) {
                $model->sent_at = now();
            }
        });
    }
}
