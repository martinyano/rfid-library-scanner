<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookCopy extends Model
{
    use HasFactory;

    public const STATUS_AVAILABLE = 'available';

    public const STATUS_BORROWED = 'borrowed';

    public const STATUS_LOST = 'lost';

    public const STATUS_DAMAGED = 'damaged';

    protected $fillable = [
        'book_id',
        'rfid_uid',
        'status',
    ];

    protected function rfidUid(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => filled($value)
                ? strtoupper(preg_replace('/\s+/', '', trim($value)))
                : null,
        );
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function getStatusLabelAttribute(): string
    {
        return ucfirst($this->status);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'badge-soft-success',
            self::STATUS_BORROWED => 'badge-soft-info',
            self::STATUS_LOST => 'badge-soft-danger',
            self::STATUS_DAMAGED => 'badge-soft-warning',
            default => 'badge-soft-secondary',
        };
    }
}
