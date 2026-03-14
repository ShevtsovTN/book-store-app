<?php

declare(strict_types=1);

namespace Tests\Fakes;

use App\Application\Catalog\DTOs\BookSearchQuery;
use App\Application\Catalog\DTOs\BookSearchResult;
use App\Application\Catalog\Interfaces\BookSearchIndexInterface;
use App\Domain\Catalog\Entities\Book;
use PHPUnit\Framework\Assert;

final class FakeMeilisearchBookIndex implements BookSearchIndexInterface
{
    /** @var array<int, array> */
    private array $indexed = [];

    public function index(Book $book): void
    {
        $this->indexed[$book->id] = ['id' => $book->id, 'title' => $book->title];
    }

    public function delete(int $bookId): void
    {
        unset($this->indexed[$bookId]);
    }

    public function search(BookSearchQuery $query): BookSearchResult
    {
        $hits = array_filter(
            $this->indexed,
            static fn ($doc) => str_contains(strtolower($doc['title']), strtolower($query->query))
        );

        return new BookSearchResult(
            hits:             [],
            total:            count($hits),
            limit:            $query->limit,
            offset:           $query->offset,
            processingTimeMs: 0,
        );
    }

    public function bulkIndex(array $books): void
    {
        foreach ($books as $book) {
            $this->index($book);
        }
    }

    public function reindex(array $books): void
    {
        $this->indexed = [];
        $this->bulkIndex($books);
    }

    public function assertIndexed(int $bookId): void
    {
        Assert::assertArrayHasKey($bookId, $this->indexed);
    }

    public function assertNotIndexed(int $bookId): void
    {
        Assert::assertArrayNotHasKey($bookId, $this->indexed);
    }
}
