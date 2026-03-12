<?php

declare(strict_types=1);

namespace App\Application\Catalog\UseCases\UpdateBook;

use App\Application\Catalog\Interfaces\BookSearchIndexInterface;
use App\Domain\Catalog\Entities\Book;
use App\Domain\Catalog\Enums\AccessTypeEnum;
use App\Domain\Catalog\Exceptions\BookNotFoundException;
use App\Domain\Catalog\Interfaces\BookRepositoryInterface;
use App\Domain\Shared\ValueObjects\Currency;
use App\Domain\Shared\ValueObjects\Money;

final readonly class UpdateBookHandler
{
    public function __construct(
        private BookRepositoryInterface  $books,
        private BookSearchIndexInterface $searchIndex,
    ) {}

    public function handle(UpdateBookCommand $command): UpdateBookResult
    {
        $book = $this->books->findById($command->id);

        if (!$book) {
            throw new BookNotFoundException($command->id);
        }

        $updated = new Book(
            id: $book->id,
            title: $command->title,
            slug: $book->slug,
            description: $command->description,
            isbn: $command->isbn,
            language: $command->language,
            publisher: $command->publisher,
            publishedYear: $command->publishedYear,
            edition: $book->edition,
            pagesCount: $book->pagesCount,
            coverPath: $book->coverPath,
            filePath: $book->filePath,
            accessType: $command->accessType,
            price: new Money($command->price, new Currency($command->currency)),
            status: $book->status,
            publishedAt: $book->publishedAt,
        );

        $saved = $this->books->save($updated);

        if ($saved->isPublished()) {
            $this->searchIndex->index($saved);
        }

        return new UpdateBookResult(book: $saved);
    }
}
