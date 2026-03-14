<?php

use App\Domain\Catalog\Exceptions\BookNotFoundException;
use App\Domain\Catalog\Exceptions\TagNotFoundException;
use App\Presentation\Console\Commands\Search\ConfigureSearchIndexCommand;
use App\Presentation\Console\Commands\Search\ReindexBooksCommand;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withCommands([
        ConfigureSearchIndexCommand::class,
        ReindexBooksCommand::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReportDuplicates();
        $exceptions->render(function (Throwable $e) {
            return match (true) {
                $e instanceof BookNotFoundException,
                $e instanceof TagNotFoundException => response()->json([
                    'message' => $e->getMessage(),
                ], Response::HTTP_NOT_FOUND),
                default => null,
            };
        });
    })->create();
