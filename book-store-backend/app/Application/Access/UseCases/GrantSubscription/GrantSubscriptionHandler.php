<?php

declare(strict_types=1);

namespace App\Application\Access\UseCases\GrantSubscription;

use App\Domain\Access\Entities\UserSubscription;
use App\Domain\Access\Enums\SubscriptionStatusEnum;
use App\Domain\Access\Interfaces\UserSubscriptionRepositoryInterface;
use DateTimeImmutable;

final readonly class GrantSubscriptionHandler
{
    public function __construct(
        private UserSubscriptionRepositoryInterface $subscriptions,
    ) {}

    public function handle(GrantSubscriptionCommand $command): void
    {
        $existing = $this->subscriptions->findActiveByUser($command->userId);

        if (null !== $existing) {
            return;
        }

        $this->subscriptions->save(
            new UserSubscription(
                id: null,
                userId: $command->userId,
                status: SubscriptionStatusEnum::ACTIVE,
                startedAt: new DateTimeImmutable(),
                expiresAt: $command->expiresAt,
                stripeSubscriptionId: $command->stripeSubscriptionId,
            ),
        );
    }
}
