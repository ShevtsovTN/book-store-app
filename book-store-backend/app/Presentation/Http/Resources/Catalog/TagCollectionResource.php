<?php

namespace App\Presentation\Http\Resources\Catalog;

use App\Domain\Catalog\Entities\Tag;
use App\Domain\Catalog\ValueObjects\BookCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class TagCollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var BookCollection $collection */
        $collection = $this->resource;

        return [
            'data' => array_map(
                static fn(Tag $book) => new TagResource($book)->toArray($request),
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
