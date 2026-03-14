<?php

namespace App\Application\Reading\UseCases\SaveReadingProgress;

use App\Domain\Reading\Entities\UserReadingProgress;
use App\Domain\Reading\Interfaces\ReadingProgressCacheRepositoryInterface;
use App\Domain\Reading\Interfaces\UserReadingProgressRepositoryInterface;
use App\Domain\Reading\ValueObjects\ReadingPosition;

final readonly class SaveReadingProgressHandler
{
    public function __construct(
        private UserReadingProgressRepositoryInterface $progressRepository,
        private ReadingProgressCacheRepositoryInterface $cache,
    ) {}

    public function handle(SaveReadingProgressCommand $command): SaveReadingProgressResult
    {
        $position = new ReadingPosition(
            bookId:           $command->bookId,
            chapterId:        $command->chapterId,
            pageId:           $command->pageId,
            globalPageNumber: $command->globalPageNumber,
            scrollPosition:   $command->scrollPosition,
        );

        $progress = $this->progressRepository
            ->findByUserAndBook($command->userId, $command->bookId)
            ?? UserReadingProgress::initiate($command->userId, $command->bookId, $command->totalPages);

        $updated = $progress->withPosition($position, $command->totalPages);

        $this->progressRepository->save($updated);
        $this->cache->set($command->userId, $position);

        return new SaveReadingProgressResult(
            completionPercentage: $updated->completionPercentage,
            isFinished:           $updated->isFinished,
        );
    }
}
