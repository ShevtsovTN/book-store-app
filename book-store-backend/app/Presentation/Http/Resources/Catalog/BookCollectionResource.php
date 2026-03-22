<?php

namespace App\Presentation\Http\Resources\Catalog;

use App\Application\Catalog\Interfaces\BookCoverStorageInterface;
use App\Domain\Catalog\Entities\Book;
use App\Domain\Catalog\ValueObjects\BookCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property BookCollection $resource
 * @property int $total
 * @property int $perPage
 * @property int $currentPage
 * @property int $totalPages
 */
final class BookCollectionResource extends JsonResource
{
    private BookCoverStorageInterface $storage;

    public function withStorage(BookCoverStorageInterface $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    /**
     * @return array{
     *     data: array<int, array{
     *         id: int,
     *         title: string,
     *         slug: string,
     *         description: string|null,
     *         isbn: string|null,
     *         language: string,
     *         publisher: string|null,
     *         published_year: int|null,
     *         pages_count: int,
     *         cover_url: string|null,
     *         file_links: array<int, array{mime_type: string, url: string, label: string}>,
     *         access_type: string,
     *         price: array{currency: string, amount: int, formatted: string},
     *         status: string,
     *         is_free: bool,
     *         published_at: string|null
     *     }>,
     *     meta: array{
     *         total: int,
     *         per_page: int,
     *         current_page: int,
     *         total_pages: int
     *     }
     * }
     */
    public function toArray(Request $request): array
    {
        $collection = $this->resource;

        return [
            'data' => array_map(
                fn(Book $book) => new BookResource($book)
                    ->withStorage($this->storage)
                    ->toArray($request),
                $collection->items,
            ),
            'meta' => [
                'total'        => $collection->total,
                'per_page'     => $collection->perPage,
                'current_page' => $collection->currentPage,
                'total_pages'  => $collection->totalPages(),
            ],
        ];
    }
}
