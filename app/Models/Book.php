<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Book extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'author_id',
        'category_id',
        'isbn',
        'description',
        'cover_image',
        'published_year',
        'shelf_location',
        'total_copies',
        'available_copies',
    ];

    protected function casts(): array
    {
        return [
            'published_year' => 'integer',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function activeBorrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class)->where('status', 'borrowed');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function ($query) use ($term) {
            $query->where('title', 'like', "%{$term}%")
                ->orWhere('isbn', 'like', "%{$term}%")
                ->orWhereHas('author', fn ($author) => $author->where('name', 'like', "%{$term}%"))
                ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$term}%"));
        });
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_copies', '>', 0);
    }

    public function getCoverUrlAttribute(): ?string
    {
        return $this->cover_image ? Storage::disk('public')->url($this->cover_image) : null;
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->available_copies > 0;
    }

    public function getAvailabilityLabelAttribute(): string
    {
        if ($this->available_copies < 1) {
            return 'All copies borrowed';
        }

        return $this->available_copies.' of '.$this->total_copies.' available';
    }

    public function getAvailabilityBadgeAttribute(): string
    {
        return $this->available_copies > 0 ? 'badge-soft-success' : 'badge-soft-danger';
    }
}
