<?php

namespace App\Infrastructure\Search;

use App\Application\Catalog\DTOs\BookSearchHit;
use App\Application\Catalog\DTOs\BookSearchQuery;
use App\Application\Catalog\DTOs\BookSearchResult;
use App\Application\Catalog\Interfaces\BookSearchIndexInterface;
use App\Domain\Catalog\Entities\Book;
use Meilisearch\Client;

final class MeilisearchBookIndex implements BookSearchIndexInterface
{
    private const string INDEX_NAME = 'books';

    public function __construct(
        private readonly Client $client,
    ) {}

    public function index(Book $book): void
    {
        $this->client
            ->index(self::INDEX_NAME)
            ->addDocuments([$this->toDocument($book)]);
    }

    public function delete(int $bookId): void
    {
        $this->client
            ->index(self::INDEX_NAME)
            ->deleteDocument($bookId);
    }

    public function search(BookSearchQuery $query): BookSearchResult
    {
        $filters = $this->buildFilters($query);

        $result = $this->client
            ->index(self::INDEX_NAME)
            ->search($query->query, [
                'limit'                  => $query->limit,
                'offset'                 => $query->offset,
                'filter'                 => $filters ?: null,
                'showRankingScore'       => true,
                'attributesToHighlight'  => ['title', 'description'],
            ]);

        return new BookSearchResult(
            hits:             array_map(
                fn (array $hit) => $this->toHit($hit),
                $result->getHits(),
            ),
            total:            $result->getEstimatedTotalHits(),
            limit:            $result->getLimit(),
            offset:           $result->getOffset(),
            processingTimeMs: $result->getProcessingTimeMs(),
        );
    }

    public function bulkIndex(array $books): void
    {
        $documents = array_map(
            fn (Book $book) => $this->toDocument($book),
            $books,
        );

        $this->client
            ->index(self::INDEX_NAME)
            ->addDocuments($documents);
    }

    public function reindex(array $books): void
    {
        $this->client
            ->index(self::INDEX_NAME)
            ->deleteAllDocuments();

        if (!empty($books)) {
            $this->bulkIndex($books);
        }
    }

    private function buildFilters(BookSearchQuery $query): array
    {
        $filters = [];

        if ($query->status) {
            $filters[] = "status = {$query->status->value}";
        }

        if ($query->accessType) {
            $filters[] = "access_type = {$query->accessType->value}";
        }

        if ($query->language) {
            $filters[] = "language = {$query->language}";
        }

        return $filters;
    }

    private function toDocument(Book $book): array
    {
        return [
            'id'          => $book->id,
            'title'       => $book->title,
            'slug'        => $book->slug,
            'description' => $book->description,
            'language'    => $book->language,
            'access_type' => $book->accessType->value,
            'status'      => $book->status->value,
        ];
    }

    private function toHit(array $hit): BookSearchHit
    {
        return new BookSearchHit(
            bookId:       $hit['id'],
            title:        $hit['title'],
            slug:         $hit['slug'],
            description:  $hit['description'] ?? null,
            accessType:   $hit['access_type'],
            status:       $hit['status'],
            rankingScore: $hit['_rankingScore'] ?? 0.0,
        );
    }
}
