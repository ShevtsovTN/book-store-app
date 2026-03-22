<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Access\Entities\UserSubscription;
use App\Domain\Access\Interfaces\UserSubscriptionRepositoryInterface;
use App\Infrastructure\Persistence\Models\UserSubscriptionModel;

final class EloquentUserSubscriptionRepository implements UserSubscriptionRepositoryInterface
{
    public function findActiveByUser(int $userId): ?UserSubscription
    {
        $model = UserSubscriptionModel::query()
            ->forUser($userId)
            ->active()
            ->latest('started_at')
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function save(UserSubscription $subscription): UserSubscription
    {
        if (null === $subscription->id) {
            $model = UserSubscriptionModel::query()->create($this->toArray($subscription));
        } else {
            $model = UserSubscriptionModel::query()->findOrFail($subscription->id);
            $model->update($this->toArray($subscription));
        }

        return $this->toDomain($model);
    }

    private function toDomain(UserSubscriptionModel $model): UserSubscription
    {
        return new UserSubscription(
            id: $model->id,
            userId: $model->user_id,
            status: $model->status,
            startedAt: $model->started_at,
            expiresAt: $model->expires_at,
            stripeSubscriptionId: $model->stripe_subscription_id,
        );
    }

    private function toArray(UserSubscription $subscription): array
    {
        return [
            'user_id'                => $subscription->userId,
            'status'                 => $subscription->status->value,
            'stripe_subscription_id' => $subscription->stripeSubscriptionId,
            'started_at'             => $subscription->startedAt,
            'expires_at'             => $subscription->expiresAt,
        ];
    }
}
