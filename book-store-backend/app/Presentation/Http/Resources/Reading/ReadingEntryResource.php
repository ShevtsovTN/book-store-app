<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resources\Reading;

use App\Domain\Reading\Entities\ReadingEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ReadingEntryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ReadingEntry $entry */
        $entry = $this->resource;

        return [
            'id'                  => $entry->id,
            'book_id'             => $entry->bookId,
            'status'              => $entry->status->value,
            'current_page'        => $entry->currentPage,
            'total_pages'         => $entry->totalPages,
            'progress_percentage' => $entry->progressPercentage(),
            'started_at'          => $entry->startedAt?->format(\DateTimeInterface::ATOM),
            'finished_at'         => $entry->finishedAt?->format(\DateTimeInterface::ATOM),
        ];
    }
}
