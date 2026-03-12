<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Entities;

use App\Domain\Catalog\Enums\AccessTypeEnum;
use App\Domain\Catalog\Enums\BookStatusEnum;
use App\Domain\Shared\ValueObjects\Money;
use DateTimeImmutable;

final readonly class Book
{
    public function __construct(
        public ?int               $id = null,
        public string             $title,
        public string             $slug,
        public ?string            $description = null,
        public ?string            $isbn = null,
        public string             $language,
        public ?string            $publisher = null,
        public ?int               $publishedYear = null,
        public int                $edition,
        public int                $pagesCount,
        public ?string            $coverPath = null,
        public ?string            $filePath = null,
        public AccessTypeEnum     $accessType,
        public Money              $price,
        public BookStatusEnum     $status,
        public ?DateTimeImmutable $publishedAt = null,
    ) {
    }

    public function isPublished(): bool
    {
        return $this->status === BookStatusEnum::PUBLISHED
            && $this->publishedAt <= new DateTimeImmutable();
    }

    public function isFree(): bool
    {
        return $this->accessType === AccessTypeEnum::FREE
            || $this->price->amount === 0;
    }

    public function publish(): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            slug: $this->slug,
            description: $this->description,
            isbn: $this->isbn,
            language: $this->language,
            publisher: $this->publisher,
            publishedYear: $this->publishedYear,
            edition: $this->edition,
            pagesCount: $this->pagesCount,
            coverPath: $this->coverPath,
            filePath: $this->filePath,
            accessType: $this->accessType,
            price: $this->price,
            status: BookStatusEnum::PUBLISHED,
            publishedAt: new DateTimeImmutable(),
        );
    }

    public function withPagesCount(int $pagesCount): self
    {
        return new self(
            id:            $this->id,
            title:         $this->title,
            slug:          $this->slug,
            description:   $this->description,
            isbn:          $this->isbn,
            language:      $this->language,
            publisher:     $this->publisher,
            publishedYear: $this->publishedYear,
            edition:       $this->edition,
            pagesCount:    $pagesCount,
            coverPath:     $this->coverPath,
            accessType:    $this->accessType,
            price:         $this->price,
            status:        $this->status,
            publishedAt:   $this->publishedAt,
        );
    }

    public function withFilePath(string $filePath): self
    {
        return new self(
            id:            $this->id,
            title:         $this->title,
            slug:          $this->slug,
            description:   $this->description,
            isbn:          $this->isbn,
            language:      $this->language,
            publisher:     $this->publisher,
            publishedYear: $this->publishedYear,
            edition:       $this->edition,
            pagesCount:    $this->pagesCount,
            coverPath:     $this->coverPath,
            filePath:      $filePath,
            accessType:    $this->accessType,
            price:         $this->price,
            status:        $this->status,
            publishedAt:   $this->publishedAt,
        );
    }
}
