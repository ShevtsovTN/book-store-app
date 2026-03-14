<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resources\Catalog;

use App\Application\Catalog\DTOs\BookSearchResult;

final readonly class BookSearchResource
{
    private function __construct(
        public array $hits,
        public int   $total,
        public int   $limit,
        public int   $offset,
        public int   $processingTimeMs,
    ) {}

    public static function fromResult(BookSearchResult $result): self
    {
        return new self(
            hits:             array_map(
                static fn ($hit) => [
                    'id'            => $hit->bookId,
                    'title'         => $hit->title,
                    'slug'          => $hit->slug,
                    'description'   => $hit->description,
                    'access_type'   => $hit->accessType,
                    'status'        => $hit->status,
                    'ranking_score' => $hit->rankingScore,
                ],
                $result->hits,
            ),
            total:            $result->total,
            limit:            $result->limit,
            offset:           $result->offset,
            processingTimeMs: $result->processingTimeMs,
        );
    }
}
