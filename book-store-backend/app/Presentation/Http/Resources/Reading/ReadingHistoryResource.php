<?php

declare(strict_types=1);

namespace App\Presentation\Http\Resources\Reading;

use App\Application\Reading\UseCases\GetReadingHistory\GetReadingHistoryResult;
use App\Application\Reading\UseCases\GetReadingHistory\ReadingHistoryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ReadingHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var GetReadingHistoryResult $result */
        $result = $this->resource;

        return [
            'data' => array_map(
                static fn (ReadingHistoryItem $item) => [
                    'session_id'       => $item->sessionId,
                    'book_id'          => $item->bookId,
                    'pages_read'       => $item->pagesRead,
                    'duration_seconds' => $item->durationSeconds,
                    'completion'       => $item->completion,
                    'started_at'       => $item->startedAt->format(\DateTimeInterface::ATOM),
                    'ended_at'         => $item->endedAt?->format(\DateTimeInterface::ATOM),
                ],
                $result->items,
            ),
            'meta' => [
                'total_sessions'          => count($result->items),
                'total_pages_read'        => $result->totalPagesRead(),
                'total_duration_seconds'  => $result->totalDurationSeconds(),
            ],
        ];
    }
}
