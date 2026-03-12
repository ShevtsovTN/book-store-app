<?php

namespace App\Application\Catalog\UseCases\UploadBookCover;

use App\Application\Catalog\Interfaces\BookCoverStorageInterface;
use App\Domain\Catalog\Entities\Book;
use App\Domain\Catalog\Exceptions\BookNotFoundException;
use App\Domain\Catalog\Interfaces\BookRepositoryInterface;

final readonly class UploadBookCoverHandler
{
    public function __construct(
        private BookRepositoryInterface   $books,
        private BookCoverStorageInterface $storage,
    ) {}

    public function handle(UploadBookCoverCommand $command): UploadBookCoverResult
    {
        $book = $this->books->findById($command->bookId);

        if (!$book) {
            throw new BookNotFoundException($command->bookId);
        }

        if ($book->coverPath) {
            $this->storage->delete($book->coverPath);
        }

        $path = $this->storage->upload(
            bookId:    $command->bookId,
            tempPath:  $command->tempPath,
            filename:  $command->filename,
        );

        $updated = new Book(
            id:            $book->id,
            title:         $book->title,
            slug:          $book->slug,
            description:   $book->description,
            isbn:          $book->isbn,
            language:      $book->language,
            publisher:     $book->publisher,
            publishedYear: $book->publishedYear,
            edition:       $book->edition,
            pagesCount:    $book->pagesCount,
            coverPath:     $path,
            accessType:    $book->accessType,
            price:         $book->price,
            status:        $book->status,
            publishedAt:   $book->publishedAt,
        );

        $saved = $this->books->save($updated);

        return new UploadBookCoverResult(book: $saved);
    }
}
