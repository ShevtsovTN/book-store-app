<?php

use App\Presentation\Http\Controllers\BookController;
use App\Presentation\Http\Controllers\BookCoverController;
use App\Presentation\Http\Controllers\BookFileController;
use App\Presentation\Http\Controllers\BookSearchController;
use App\Presentation\Http\Controllers\BookTagController;
use App\Presentation\Http\Controllers\TagController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('books/search', BookSearchController::class)->name('books.search');

    Route::apiResource('books', BookController::class);
    Route::post('books/{id}/cover', BookCoverController::class)->name('books.cover');
    Route::post('books/{id}/book-file', BookFileController::class)->name('books.file');

    Route::post  ('books/{id}/tags',        [BookTagController::class, 'sync'])->name('books.tags.sync');
    Route::post  ('books/{id}/tags/{tagId}', [BookTagController::class, 'attach'])->name('books.tags.attach');
    Route::delete('books/{id}/tags/{tagId}', [BookTagController::class, 'detach'])->name('books.tags.detach');
    Route::get('tags', [TagController::class, 'index'])->name('tags.index');
});
