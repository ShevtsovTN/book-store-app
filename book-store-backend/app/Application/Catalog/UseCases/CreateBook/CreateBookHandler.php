<?php

declare(strict_types=1);

namespace App\Application\Catalog\UseCases\CreateBook;

use App\Application\Catalog\Interfaces\BookSearchIndexInterface;
use App\Application\Shared\Interfaces\SlugGeneratorInterface;
use App\Domain\Catalog\Entities\Book;
use App\Domain\Catalog\Enums\AccessTypeEnum;
use App\Domain\Catalog\Enums\BookStatusEnum;
use App\Domain\Catalog\Interfaces\BookRepositoryInterface;
use App\Domain\Shared\ValueObjects\Currency;
use App\Domain\Shared\ValueObjects\Money;

final readonly class CreateBookHandler
{
    public function __construct(
        private BookRepositoryInterface  $books,
        private SlugGeneratorInterface   $slugGenerator,
    ) {}

    public function handle(CreateBookCommand $command): CreateBookResult
    {
        $slug = $this->slugGenerator->generate($command->title);

        $book = new Book(
            id: null,
            title: $command->title,
            slug: $slug,
            description: $command->description,
            isbn: $command->isbn,
            language: $command->language,
            publisher: $command->publisher,
            publishedYear: $command->publishedYear,
            edition: 1,
            pagesCount: 0,
            coverPath: null,
            filePath: null,
            accessType: $command->accessType,
            price: new Money($command->price, new Currency($command->currency)),
            status: BookStatusEnum::DRAFT,
            publishedAt: null,
        );

        $saved = $this->books->save($book);

        return new CreateBookResult(book: $saved);
    }
}
