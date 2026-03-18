<?php

declare(strict_types=1);

namespace App\Domain\Reading\Entities;

use App\Domain\Reading\Enums\ReadingStatusEnum;
use DateTimeImmutable;

/**
 * @property int $id
 * @property int $user_id
 * @property int $book_id
 * @property ReadingStatusEnum $status
 * @property int $current_page
 * @property int|null $total_pages
 * @property DateTimeImmutable|null $started_at
 * @property DateTimeImmutable|null $finished_at
 */
final readonly class ReadingEntry
{
    public function __construct(
        public int                $userId,
        public int                $bookId,
        public ReadingStatusEnum  $status,
        public int                $currentPage,
        public ?int               $totalPages = null,
        public ?DateTimeImmutable $startedAt  = null,
        public ?DateTimeImmutable $finishedAt = null,
        public ?int               $id         = null,
    ) {}

    public function isFinished(): bool
    {
        return $this->status === ReadingStatusEnum::FINISHED;
    }

    public function isReading(): bool
    {
        return $this->status === ReadingStatusEnum::READING;
    }

    public function progressPercentage(): ?float
    {
        if ($this->totalPages === null || $this->totalPages === 0) {
            return null;
        }

        return round(($this->currentPage / $this->totalPages) * 100, 2);
    }

    public function startReading(int $totalPages): self
    {
        return new self(
            userId:      $this->userId,
            bookId:      $this->bookId,
            status:      ReadingStatusEnum::READING,
            currentPage: 0,
            totalPages:  $totalPages,
            startedAt:   new DateTimeImmutable(),
            finishedAt:  $this->finishedAt,
            id:          $this->id,
        );
    }

    public function updateProgress(int $currentPage): self
    {
        $isCompleted = $this->totalPages !== null && $currentPage >= $this->totalPages;

        return new self(
            userId:      $this->userId,
            bookId:      $this->bookId,
            status:      $isCompleted ? ReadingStatusEnum::FINISHED : $this->status,
            currentPage: $currentPage,
            totalPages:  $this->totalPages,
            startedAt:   $this->startedAt,
            finishedAt:  $isCompleted ? new DateTimeImmutable() : $this->finishedAt,
            id:          $this->id,
        );
    }

    public function drop(): self
    {
        return new self(
            userId:      $this->userId,
            bookId:      $this->bookId,
            status:      ReadingStatusEnum::DROPPED,
            currentPage: $this->currentPage,
            totalPages:  $this->totalPages,
            startedAt:   $this->startedAt,
            finishedAt:  $this->finishedAt,
            id:          $this->id,
        );
    }
}
