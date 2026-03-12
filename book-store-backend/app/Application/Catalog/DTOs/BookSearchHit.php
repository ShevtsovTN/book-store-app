<?php

declare(strict_types=1);

namespace App\Application\Catalog\DTOs;

final readonly class BookSearchHit
{
    public function __construct(
        public int     $bookId,
        public string  $title,
        public string  $slug,
        public ?string $description,
        public string  $accessType,
        public string  $status,
        public float   $rankingScore,
    ) {}
}
