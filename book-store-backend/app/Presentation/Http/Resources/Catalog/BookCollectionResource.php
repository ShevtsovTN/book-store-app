<?php

namespace App\Presentation\Http\Resources\Catalog;

use App\Application\Catalog\Interfaces\BookCoverStorageInterface;
use App\Domain\Catalog\Entities\Book;
use App\Domain\Catalog\ValueObjects\BookCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class BookCollectionResource extends JsonResource
{
    private BookCoverStorageInterface $storage;

    public function withStorage(BookCoverStorageInterface $storage): self
    {
        $this->storage = $storage;

        return $this;
    }

    public function toArray(Request $request): array
    {
        /** @var BookCollection $collection */
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
