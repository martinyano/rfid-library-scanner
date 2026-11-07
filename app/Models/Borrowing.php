<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'book_id',
        'borrowed_at',
        'due_at',
        'returned_at',
        'status',
        'notes',
        'due_soon_notified_at',
        'overdue_notified_at',
        'handled_by_id',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'date',
            'due_at' => 'date',
            'returned_at' => 'date',
            'due_soon_notified_at' => 'datetime',
            'overdue_notified_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class)->withTrashed();
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'borrowed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'borrowed')->whereDate('due_at', '<', today());
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'borrowed' && $this->due_at && $this->due_at->lt(today());
    }

    public function getOverdueDaysAttribute(): int
    {
        if (! $this->due_at || ! $this->is_overdue) {
            return 0;
        }

        return max(0, (int) $this->due_at->startOfDay()->diffInDays(today(), false));
    }

    public function getDaysLeftAttribute(): int
    {
        if (! $this->due_at) {
            return 0;
        }

        return (int) today()->diffInDays($this->due_at->startOfDay(), false);
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->is_overdue) {
            return 'Overdue';
        }

        return ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        if ($this->is_overdue) {
            return 'badge-soft-danger';
        }

        return match ($this->status) {
            'borrowed' => 'badge-soft-info',
            'returned' => 'badge-soft-success',
            'lost' => 'badge-soft-danger',
            'damaged' => 'badge-soft-warning',
            default => 'badge-soft-secondary',
        };
    }
}
