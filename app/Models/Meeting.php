<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Meeting extends Model
{
    protected $fillable = [
        'host_id',
        'meeting_code',
        'host_peer_id',
        'status',
    ];

    public function chats(): HasMany
    {
        return $this->hasMany(MeetingChat::class, 'meeting_code', 'meeting_code')
                    ->orderBy('sent_at');
    }

    public function frontendLogs(): HasMany
    {
        return $this->hasMany(FrontendLog::class, 'meeting_code', 'meeting_code')
                    ->orderBy('created_at');
    }

    public function recordings(): HasMany
    {
        return $this->hasMany(MeetingRecording::class, 'meeting_code', 'meeting_code')
                    ->orderBy('recorded_at');
    }
}
