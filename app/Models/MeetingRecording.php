<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MeetingRecording extends Model
{
    public $timestamps = false;

    protected $fillable = ['meeting_code', 'speaker', 'file_path', 'file_size', 'recorded_at'];

    protected function casts(): array
    {
        return [
            'recorded_at' => 'datetime',
            'file_size'   => 'integer',
        ];
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (! $model->recorded_at) {
                $model->recorded_at = now();
            }
        });
        // Delete the actual audio file when the record is deleted
        static::deleting(function ($model) {
            Storage::disk('public')->delete($model->file_path);
        });
    }

    /** Full public URL to stream/download the audio */
    public function getFileUrlAttribute(): string
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $storage */
        $storage = Storage::disk('public');
        return $storage->url($this->file_path);
    }

    /** Human-readable file size (e.g. "1.4 MB") */
    public function getFileSizeLabelAttribute(): string
    {
        if (! $this->file_size) return '—';
        if ($this->file_size >= 1_048_576) return round($this->file_size / 1_048_576, 1) . ' MB';
        if ($this->file_size >= 1_024)     return round($this->file_size / 1_024)         . ' KB';
        return $this->file_size . ' B';
    }
}
