<?php

namespace App\Infrastructure\Queue;

use App\Application\Catalog\Events\BookFileUploaded;
use App\Application\Catalog\Jobs\ParseBookFileJob;
use App\Application\Shared\Interfaces\EventDispatcherInterface;

final class LaravelEventDispatcher implements EventDispatcherInterface
{
    public function dispatch(object $event): void
    {
        match (true) {
            $event instanceof BookFileUploaded => dispatch(
                new ParseBookFileJob(
                    bookId: $event->bookId,
                    filePath: $event->filePath,
                    mimeType: $event->mimeType,
                )
            ),
            default => event($event),
        };
    }
}
