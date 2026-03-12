<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class BookChapterModel extends Model
{
    protected $table = 'book_chapters';

    protected $fillable = [
        'book_id',
        'volume_id',
        'number',
        'title',
        'slug',
        'reading_time_minutes',
        'is_published',
    ];

    protected $casts = [
        'number'               => 'integer',
        'reading_time_minutes' => 'integer',
        'is_published'         => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────

    public function book(): BelongsTo
    {
        return $this->belongsTo(BookModel::class, 'book_id');
    }

    public function volume(): BelongsTo
    {
        return $this->belongsTo(BookVolumeModel::class, 'volume_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(BookPageModel::class, 'chapter_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeByBook(Builder $query, int $bookId): Builder
    {
        return $query->where('book_id', $bookId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('number');
    }
}
