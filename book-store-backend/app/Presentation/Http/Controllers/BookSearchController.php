<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use App\Application\Catalog\UseCases\SearchBooks\SearchBooksCommand;
use App\Application\Catalog\UseCases\SearchBooks\SearchBooksHandler;
use App\Presentation\Http\Resources\Catalog\BookSearchResource;
use App\Presentation\Http\Requests\Catalog\BookSearchRequest;
use Illuminate\Http\JsonResponse;

final class BookSearchController extends Controller
{
    public function __construct(
        private readonly SearchBooksHandler $handler,
    ) {}

    public function __invoke(BookSearchRequest $request): JsonResponse
    {
        $result = $this->handler->handle(
            SearchBooksCommand::fromArray($request->validated()),
        );

        return new JsonResponse(new BookSearchResource($result));
    }
}
