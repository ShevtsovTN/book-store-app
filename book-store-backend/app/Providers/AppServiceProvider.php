<?php

namespace App\Providers;

use App\Application\Catalog\Interfaces\BookCoverStorageInterface;
use App\Application\Catalog\Interfaces\BookFileParserInterface;
use App\Application\Catalog\Interfaces\BookFileStorageInterface;
use App\Application\Catalog\Interfaces\BookSearchIndexInterface;
use App\Application\Shared\Interfaces\EventDispatcherInterface;
use App\Application\Shared\Interfaces\SlugGeneratorInterface;
use App\Domain\Catalog\Interfaces\BookRepositoryInterface;
use App\Domain\Catalog\Interfaces\BookTagRepositoryInterface;
use App\Domain\Catalog\Interfaces\TagRepositoryInterface;
use App\Domain\Reading\Interfaces\BookChapterRepositoryInterface;
use App\Domain\Reading\Interfaces\BookPageRepositoryInterface;
use App\Infrastructure\Parser\BookFileParserRouter;
use App\Infrastructure\Persistence\Repositories\EloquentBookChapterRepository;
use App\Infrastructure\Persistence\Repositories\EloquentBookPageRepository;
use App\Infrastructure\Persistence\Repositories\EloquentBookRepository;
use App\Infrastructure\Persistence\Repositories\EloquentBookTagRepository;
use App\Infrastructure\Persistence\Repositories\EloquentTagRepository;
use App\Infrastructure\Queue\LaravelEventDispatcher;
use App\Infrastructure\Search\MeilisearchBookIndex;
use App\Infrastructure\Slugger\LaravelSlugGenerator;
use App\Infrastructure\Storage\S3BookCoverStorage;
use App\Infrastructure\Storage\S3BookFileStorage;
use Illuminate\Support\ServiceProvider;
use Meilisearch\Client;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Client::class, function () {
            return new Client(
                url:    config('services.meilisearch.host'),
                apiKey: config('services.meilisearch.key'),
            );
        });

        $this->app->bind(BookRepositoryInterface::class, EloquentBookRepository::class);
        $this->app->bind(BookFileParserInterface::class, BookFileParserRouter::class);
        $this->app->bind(SlugGeneratorInterface::class, LaravelSlugGenerator::class);
        $this->app->bind(EventDispatcherInterface::class, LaravelEventDispatcher::class);
        $this->app->bind(BookSearchIndexInterface::class, MeilisearchBookIndex::class);
        $this->app->bind(BookCoverStorageInterface::class, S3BookCoverStorage::class);
        $this->app->bind(BookFileStorageInterface::class, S3BookFileStorage::class);
        $this->app->bind(BookChapterRepositoryInterface::class, EloquentBookChapterRepository::class);
        $this->app->bind(BookPageRepositoryInterface::class, EloquentBookPageRepository::class);
        $this->app->bind(TagRepositoryInterface::class,     EloquentTagRepository::class);
        $this->app->bind(BookTagRepositoryInterface::class,  EloquentBookTagRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
