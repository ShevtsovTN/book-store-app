<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Catalog\Interfaces\BookCoverStorageInterface;
use App\Application\Catalog\UseCases\GetPopularBooks\GetPopularBooksCommand;
use App\Application\Catalog\UseCases\GetPopularBooks\GetPopularBooksHandler;
use App\Presentation\Http\Requests\Catalog\PopularBooksRequest;
use App\Presentation\Http\Resources\Catalog\BookCollectionResource;
use Illuminate\Http\JsonResponse;

final class PopularBooksController extends Controller
{
    public function __construct(
        private readonly GetPopularBooksHandler $handler,
        private readonly BookCoverStorageInterface $storage,
    ) {}

    public function __invoke(PopularBooksRequest $request): JsonResponse
    {
        $result = $this->handler->handle(
            GetPopularBooksCommand::fromArray($request->validated())
        );

        return new JsonResponse(
            new BookCollectionResource($result->collection)
                ->withStorage($this->storage)
                ->toArray($request)
        );
    }
}
