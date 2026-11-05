<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rfid_uid',
        'entry_at',
        'exit_at',
        'source',
        'ip_address',
        'user_agent',
        'closed_automatically',
    ];

    protected function casts(): array
    {
        return [
            'entry_at' => 'datetime',
            'exit_at' => 'datetime',
            'closed_automatically' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOpen($query)
    {
        return $query->whereNull('exit_at');
    }

    public function getIsOpenAttribute(): bool
    {
        return $this->exit_at === null;
    }

    public function getDurationLabelAttribute(): string
    {
        if (! $this->entry_at) {
            return '-';
        }

        $end = $this->exit_at ?? now();
        $minutes = (int) $this->entry_at->diffInMinutes($end, false);

        if ($minutes < 1) {
            return 'Less than a minute';
        }

        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;

        if ($hours < 1) {
            return $remaining.' min';
        }

        return $hours.' hr'.($hours > 1 ? 's' : '').($remaining > 0 ? ' '.$remaining.' min' : '');
    }
}
