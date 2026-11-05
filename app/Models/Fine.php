<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrowing_id',
        'user_id',
        'type',
        'amount',
        'days_overdue',
        'status',
        'paid_at',
        'notes',
        'handled_by_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'days_overdue' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(Borrowing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_id');
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', 'unpaid');
    }

    public function getTypeLabelAttribute(): string
    {
        return match ($this->type) {
            'overdue' => 'Overdue',
            'lost' => 'Lost book',
            'damaged' => 'Damaged book',
            default => ucfirst($this->type),
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'unpaid' => 'badge-soft-danger',
            'paid' => 'badge-soft-success',
            'waived' => 'badge-soft-secondary',
            default => 'badge-soft-secondary',
        };
    }
}
