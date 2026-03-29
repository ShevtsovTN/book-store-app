<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Access\Enums\SubscriptionStatusEnum;
use App\Domain\Shared\Enums\RoleEnum;
use App\Domain\User\Entities\Reader;
use App\Domain\User\Enums\ReaderFilterEnum;
use App\Domain\User\Interfaces\ReaderRepositoryInterface;
use App\Domain\User\ValueObjects\ReaderCollection;
use App\Domain\User\ValueObjects\ReaderFilter;
use App\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class EloquentReaderRepository implements ReaderRepositoryInterface
{

    public function findAll(ReaderFilter $filter): ReaderCollection
    {
        $query = UserModel::query()
            ->where('role', RoleEnum::READER)
            ->withCount([
                'subscriptions as subscriptions_count' => function ($q) {
                    $q->where('status', SubscriptionStatusEnum::ACTIVE);
                },
                'books as books_count'
            ]);

        if ($filter->filter) {
            $query = $this->applyFilter($query, $filter->filter);
        }

        if ($filter->search) {
            $query = $query
                ->where('name', 'like', "%{$filter->search}%")
                ->orWhere('email', 'like', "%{$filter->search}%");
        }

        $paginator = $query->paginate(
            perPage: $filter->perPage,
            page: $filter->page,
        );

        return new ReaderCollection(
            items: array_map(
                fn(UserModel $model) => $this->toDomain($model),
                $paginator->items(),
            ),
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
        );
    }

    private function toDomain(UserModel $model): Reader
    {
        return new Reader(
            id: $model->id,
            name: $model->name,
            email: $model->email,
            hasActiveSubscription: $model->subscriptions_count > 0,
            hasBooks: $model->books_count > 0,
            created_at: $model->created_at,
        );
    }

    private function applyFilter($query, ReaderFilterEnum $filterType): Builder
    {
        return match(true) {
            $filterType->isSubscriptionFilter() => $this->applySubscriptionFilter($query, $filterType),
            $filterType->isBooksFilter() => $this->applyBooksFilter($query, $filterType),
            default => throw new InvalidArgumentException("Unknown filter type: {$filterType->value}", Response::HTTP_BAD_REQUEST),
        };
    }

    private function applySubscriptionFilter(Builder $query, ReaderFilterEnum $type): Builder
    {
        if ($type === ReaderFilterEnum::SUBSCRIBED) {
            return $query->whereHas('subscriptions', function ($q) {
                $q->where('status', SubscriptionStatusEnum::ACTIVE);
            });
        }

        return $query->whereDoesntHave('subscriptions', function ($q) {
            $q->where('status', SubscriptionStatusEnum::ACTIVE);
        });
    }

    private function applyBooksFilter(Builder $query, ReaderFilterEnum $type): Builder
    {
        if ($type === ReaderFilterEnum::HAS_BOOKS) {
            return $query->whereHas('books');
        }

        return $query->whereDoesntHave('books');
    }

    public function findOne(int $userId): Reader
    {
        /** @var UserModel $reader */
        $reader = UserModel::query()
            ->where('id', $userId)
            ->where('role', RoleEnum::READER)->withCount([
                'subscriptions as subscriptions_count' => function ($q) {
                    $q->where('status', SubscriptionStatusEnum::ACTIVE);
                },
                'books as books_count'
            ])
            ->first();
        return $this->toDomain($reader);
    }
}
