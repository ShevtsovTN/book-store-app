<?php

namespace App\Presentation\Http\Controllers;

use App\Application\Catalog\Interfaces\BookCoverStorageInterface;
use App\Application\Catalog\UseCases\CreateBook\CreateBookCommand;
use App\Application\Catalog\UseCases\CreateBook\CreateBookHandler;
use App\Application\Catalog\UseCases\DeleteBook\DeleteBookCommand;
use App\Application\Catalog\UseCases\DeleteBook\DeleteBookHandler;
use App\Application\Catalog\UseCases\GetBook\GetBookCommand;
use App\Application\Catalog\UseCases\GetBook\GetBookHandler;
use App\Application\Catalog\UseCases\ListBooks\ListBooksCommand;
use App\Application\Catalog\UseCases\ListBooks\ListBooksHandler;
use App\Application\Catalog\UseCases\UpdateBook\UpdateBookCommand;
use App\Application\Catalog\UseCases\UpdateBook\UpdateBookHandler;
use App\Presentation\Http\Requests\Catalog\CreateBookRequest;
use App\Presentation\Http\Requests\Catalog\ListBooksRequest;
use App\Presentation\Http\Requests\Catalog\UpdateBookRequest;
use App\Presentation\Http\Resources\Catalog\BookCollectionResource;
use App\Presentation\Http\Resources\Catalog\BookResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class BookController extends Controller
{
    public function __construct(
        private readonly CreateBookHandler $createHandler,
        private readonly GetBookHandler    $getHandler,
        private readonly ListBooksHandler  $listHandler,
        private readonly UpdateBookHandler $updateHandler,
        private readonly DeleteBookHandler $deleteHandler,
        private readonly BookCoverStorageInterface $storage,
    ) {}

    public function index(ListBooksRequest $request): JsonResponse
    {
        $result = $this->listHandler->handle(
            ListBooksCommand::fromArray($request->validated())
        );

        return new JsonResponse(
            new BookCollectionResource($result->collection)
                ->withStorage($this->storage)
        );
    }

    public function show(int $id): JsonResponse
    {
        $result = $this->getHandler->handle(new GetBookCommand($id));

        return new JsonResponse(
            new BookResource($result->book)
                ->withStorage($this->storage)
                ->withFileLinks($result->fileLinks)
        );
    }

    public function store(CreateBookRequest $request): JsonResponse
    {
        $result = $this->createHandler->handle(
            CreateBookCommand::fromArray($request->validated())
        );

        return new JsonResponse(
            new BookResource($result->book)
                ->withStorage($this->storage),
            Response::HTTP_CREATED
        );
    }

    public function update(UpdateBookRequest $request, int $id): JsonResponse
    {
        $result = $this->updateHandler->handle(
            UpdateBookCommand::fromArray([
                'id' => $id,
                ...$request->validated(),
            ])
        );

        return new JsonResponse(
            new BookResource($result->book)
                ->withStorage($this->storage)
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->deleteHandler->handle(new DeleteBookCommand($id));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
