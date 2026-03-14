<?php

namespace App\Infrastructure\Search;

use App\Application\Catalog\DTOs\BookSearchHit;
use App\Application\Catalog\DTOs\BookSearchQuery;
use App\Application\Catalog\DTOs\BookSearchResult;
use App\Application\Catalog\Interfaces\BookSearchIndexInterface;
use App\Domain\Catalog\Entities\Book;
use Meilisearch\Client;
use Meilisearch\Exceptions\ApiException;
use RuntimeException;

final class MeilisearchBookIndex implements BookSearchIndexInterface
{
    private const string INDEX_NAME = 'books';

    public function __construct(
        private readonly Client $client,
        private readonly MeilisearchTaskAwaiter $awaiter,
    ) {}

    public function index(Book $book): void
    {
        $this->client
            ->index(self::INDEX_NAME)
            ->addDocuments([$this->toDocument($book)]);
    }

    public function delete(int $bookId): void
    {
        try {
            $this->client
                ->index(self::INDEX_NAME)
                ->deleteDocument($bookId);
        } catch (ApiException $e) {
            // document_not_found — не ошибка, идемпотентная операция
            if ($e->errorCode !== 'document_not_found') {
                throw new RuntimeException(
                    "Failed to delete book #{$bookId} from search index: {$e->getMessage()}",
                    previous: $e,
                );
            }
        }
    }

    public function search(BookSearchQuery $query): BookSearchResult
    {
        $result = $this->client
            ->index(self::INDEX_NAME)
            ->search($query->query, $this->buildSearchParams($query));

        return new BookSearchResult(
            hits:             array_map(
                fn (array $hit) => $this->toHit($hit),
                $result->getHits(),
            ),
            total:            $result->getEstimatedTotalHits() ?? 0,
            limit:            $result->getLimit()             ?? $query->limit,
            offset:           $result->getOffset()            ?? $query->offset,
            processingTimeMs: $result->getProcessingTimeMs()  ?? 0,
        );
    }

    public function bulkIndex(array $books): void
    {
        if (empty($books)) {
            return;
        }

        // MeiliSearch рекомендует батчи по 1000 документов.
        // Большие батчи: меньше HTTP-запросов, но больше памяти и время одной задачи.
        $taskUids = [];

        foreach (array_chunk($books, 1000) as $batch) {
            $task = $this->client
                ->index(self::INDEX_NAME)
                ->addDocuments(
                    array_map(fn (Book $b) => $this->toDocument($b), $batch)
                );

            // Собираем taskUid — ждём все после отправки всех батчей,
            // а не после каждого (параллельная обработка на стороне MeiliSearch).
            $taskUids[] = $task['taskUid'];
        }

        // При bulkIndex важно дождаться завершения — вызывающий код
        // (команда reindex) ожидает, что данные будут готовы.
        $this->awaiter->waitAll($taskUids);
    }

    public function reindex(array $books): void
    {
        // Этот метод — синхронная замена всего индекса.
        // В production используй MeilisearchIndexConfigurator::reindexWithSwap().
        // Здесь допустимо только в dev/staging или при initial setup.
        $task = $this->client
            ->index(self::INDEX_NAME)
            ->deleteAllDocuments();

        // Ждём удаления перед добавлением — иначе новые документы
        // могут быть удалены вместе со старыми.
        $this->awaiter->wait($task['taskUid']);

        if (!empty($books)) {
            $this->bulkIndex($books);
        }
    }

    private function buildSearchParams(BookSearchQuery $query): array
    {
        $params = [
            'limit'                 => $query->limit,
            'offset'                => $query->offset,
            // Highlighting: MeiliSearch оборачивает совпадения в <em> теги.
            // Полезно для UI — фронтенд рендерит жирный текст без дополнительной логики.
            'attributesToHighlight' => ['title', 'description'],
            'highlightPreTag'       => '<mark>',
            'highlightPostTag'      => '</mark>',
            // Ranking score показывает релевантность от 0.0 до 1.0.
            // Можно использовать для сортировки на клиенте или порогового отсечения.
            'showRankingScore'      => true,
        ];

        $filters = $this->buildFilters($query);
        if (!empty($filters)) {
            // Несколько фильтров — AND-условие.
            // Для OR используй: [['status = published', 'status = archived']]
            $params['filter'] = $filters;
        }

        return $params;
    }

    private function buildFilters(BookSearchQuery $query): array
    {
        $filters = [];

        // Фильтры — строки вида "attribute = value" или "attribute IN [v1, v2]".
        // ВАЖНО: атрибуты должны быть объявлены в filterableAttributes индекса.
        if ($query->status !== null) {
            $filters[] = "status = '{$query->status->value}'";
        }

        if ($query->accessType !== null) {
            $filters[] = "access_type = '{$query->accessType->value}'";
        }

        if ($query->language !== null) {
            // Значения с пробелами/спецсимволами берём в кавычки
            $filters[] = "language = '{$query->language}'";
        }

        return $filters;
    }

    private function toDocument(Book $book): array
    {
        // Документ — это projection сущности для поискового движка.
        // Правила:
        // 1. Всегда включай 'id' — это primary key документа в MeiliSearch.
        // 2. Включай только searchable + filterable + sortable поля.
        // 3. Enum → string через ->value, иначе MeiliSearch получит объект.
        // 4. null-поля включаем явно — иначе при update они не обнулятся.
        return [
            'id'          => $book->id,
            'title'       => $book->title,
            'slug'        => $book->slug,
            'description' => $book->description ?? '',
            'isbn'        => $book->isbn ?? '',
            'language'    => $book->language,
            'access_type' => $book->accessType->value,
            'status'      => $book->status->value,
        ];
    }

    private function toHit(array $hit): BookSearchHit
    {
        // _rankingScore — float от 0.0 до 1.0, доступен только при showRankingScore=true.
        // _formatted — массив с highlighted-версиями полей.
        return new BookSearchHit(
            bookId:           $hit['id'],
            title:            $hit['_formatted']['title']       ?? $hit['title'],
            slug:             $hit['slug'],
            description:      $hit['_formatted']['description'] ?? $hit['description'] ?? null,
            accessType:       $hit['access_type'],
            status:           $hit['status'],
            rankingScore:     $hit['_rankingScore'] ?? 0.0,
        );
    }
}
