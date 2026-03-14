<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Reading\UseCases\GetBookPage\GetBookPageCommand;
use App\Application\Reading\UseCases\GetBookPage\GetBookPageHandler;
use App\Presentation\Http\Requests\Reading\GetBookPageRequest;
use App\Presentation\Http\Resources\Reading\BookPageResource;
use Illuminate\Http\JsonResponse;

final class BookPageController extends Controller
{
    public function __construct(
        private readonly GetBookPageHandler $handler,
    ) {}

    public function __invoke(GetBookPageRequest $request, int $bookId, int $pageId): JsonResponse
    {
        $result = $this->handler->handle(
            new GetBookPageCommand(
                bookId: $bookId,
                pageId: $pageId,
                userId: $request->user()->id,
            )
        );

        return new JsonResponse(new BookPageResource($result));
    }
}
