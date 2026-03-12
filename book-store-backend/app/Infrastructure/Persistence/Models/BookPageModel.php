<?php

namespace App\Infrastructure\Persistence\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BookPageModel extends Model
{
    protected $table = 'book_pages';

    protected $fillable = [
        'chapter_id',
        'number',
        'global_number',
        'content',
        'content_format',
        'word_count',
    ];

    protected $casts = [
        'number'        => 'integer',
        'global_number' => 'integer',
        'word_count'    => 'integer',
    ];

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(BookChapterModel::class, 'chapter_id');
    }

//    public function annotations(): HasMany
//    {
//        return $this->hasMany(AnnotationModel::class, 'page_id');
//    }
//
//    public function bookmarks(): HasMany
//    {
//        return $this->hasMany(BookmarkModel::class, 'page_id');
//    }

    public function scopeByBook(Builder $query, int $bookId): Builder
    {
        return $query->whereHas(
            'chapter',
            fn (BelongsTo $q) => $q->where('book_id', $bookId)
        );
    }

    public function scopeByGlobalNumber(Builder $query, int $globalNumber): Builder
    {
        return $query->where('global_number', $globalNumber);
    }
}
