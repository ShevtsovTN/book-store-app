<?php

namespace App\Providers;

use App\Application\Catalog\Interfaces\BookCoverStorageInterface;
use App\Application\Catalog\Interfaces\BookFileParserInterface;
use App\Application\Catalog\Interfaces\BookFileStorageInterface;
use App\Application\Catalog\Interfaces\BookSearchIndexInterface;
use App\Application\Identity\Interfaces\PasswordHasherInterface;
use App\Application\Shared\Interfaces\EventDispatcherInterface;
use App\Application\Shared\Interfaces\SlugGeneratorInterface;
use App\Domain\Catalog\Interfaces\BookPopularityRepositoryInterface;
use App\Domain\Catalog\Interfaces\BookRepositoryInterface;
use App\Domain\Catalog\Interfaces\BookTagRepositoryInterface;
use App\Domain\Catalog\Interfaces\TagRepositoryInterface;
use App\Domain\Identity\Interfaces\AuthenticationServiceInterface;
use App\Domain\Identity\Interfaces\UserRepositoryInterface;
use App\Domain\Reading\Interfaces\BookChapterRepositoryInterface;
use App\Domain\Reading\Interfaces\BookPageRepositoryInterface;
use App\Domain\Reading\Interfaces\ReadingProgressCacheRepositoryInterface;
use App\Domain\Reading\Interfaces\ReadingSessionRepositoryInterface;
use App\Domain\Reading\Interfaces\UserReadingProgressRepositoryInterface;
use App\Infrastructure\Auth\LaravelPasswordHasher;
use App\Infrastructure\Auth\SanctumAuthenticationService;
use App\Infrastructure\Cache\RedisReadingProgressCacheRepository;
use App\Infrastructure\Parser\BookFileParserRouter;
use App\Infrastructure\Persistence\Repositories\EloquentBookChapterRepository;
use App\Infrastructure\Persistence\Repositories\EloquentBookPageRepository;
use App\Infrastructure\Persistence\Repositories\EloquentBookPopularityRepository;
use App\Infrastructure\Persistence\Repositories\EloquentBookRepository;
use App\Infrastructure\Persistence\Repositories\EloquentBookTagRepository;
use App\Infrastructure\Persistence\Repositories\EloquentReadingSessionRepository;
use App\Infrastructure\Persistence\Repositories\EloquentTagRepository;
use App\Infrastructure\Persistence\Repositories\EloquentUserReadingProgressRepository;
use App\Infrastructure\Queue\LaravelEventDispatcher;
use App\Infrastructure\Search\MeilisearchBookIndex;
use App\Infrastructure\Search\MeilisearchIndexConfigurator;
use App\Infrastructure\Search\MeilisearchTaskAwaiter;
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

        $this->app->singleton(
            MeilisearchTaskAwaiter::class,
            fn() => new MeilisearchTaskAwaiter(
                client: app(Client::class),
                timeoutMs: (int)config('services.meilisearch.timeout_ms', 5000),
                intervalMs: (int)config('services.meilisearch.interval_ms', 50),
            )
        );
        $this->app->singleton(MeilisearchIndexConfigurator::class);

        $this->app->bind(
            UserReadingProgressRepositoryInterface::class,
            EloquentUserReadingProgressRepository::class,
        );

        $this->app->bind(
            ReadingSessionRepositoryInterface::class,
            EloquentReadingSessionRepository::class,
        );

        $this->app->bind(
            ReadingProgressCacheRepositoryInterface::class,
            RedisReadingProgressCacheRepository::class,
        );

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
        $this->app->bind(BookPopularityRepositoryInterface::class, EloquentBookPopularityRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
