<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Access\Enums\SubscriptionStatusEnum;
use App\Domain\Catalog\Enums\AccessTypeEnum;
use App\Domain\Catalog\Enums\BookStatusEnum;
use App\Domain\Identity\Enums\RoleEnum;
use App\Infrastructure\Persistence\Models\BookModel;
use App\Infrastructure\Persistence\Models\UserModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Random\RandomException;

final class UserAccessSeeder extends Seeder
{
    /** Fraction of readers who have an active subscription */
    private const float SUBSCRIPTION_FRACTION = 0.3;

    /** Fraction of readers who have purchased at least one book */
    private const float PURCHASER_FRACTION = 0.5;

    /**
     * @throws RandomException
     */
    public function run(): void
    {
        /** @var Collection<UserModel> $readers */
        $readers = UserModel::query()
            ->where('role', RoleEnum::READER)
            ->get();

        $purchasableBooks = BookModel::query()
            ->where('status', BookStatusEnum::PUBLISHED)
            ->whereIn('access_type', [AccessTypeEnum::PURCHASE, AccessTypeEnum::SUBSCRIPTION])
            ->pluck('id')
            ->all();

        $subscriptionCount = 0;
        $accessCount       = 0;

        foreach ($readers as $reader) {
            if (random_int(1, 100) <= self::SUBSCRIPTION_FRACTION * 100) {
                $this->grantSubscription($reader->id);
                $subscriptionCount++;
            }

            // Grant individual book access
            if (!empty($purchasableBooks) && random_int(1, 100) <= self::PURCHASER_FRACTION * 100) {
                $bookIds   = array_rand(
                    array_flip($purchasableBooks),
                    min(random_int(1, 3), count($purchasableBooks)),
                );
                $bookIds = is_array($bookIds) ? $bookIds : [$bookIds];

                foreach ($bookIds as $bookId) {
                    $this->grantBookAccess($reader->id, $bookId);
                    $accessCount++;
                }
            }
        }

        $this->command->info("Subscriptions granted: {$subscriptionCount}");
        $this->command->info("Book access records:   {$accessCount}");
    }

    /**
     * @throws RandomException
     */
    private function grantSubscription(int $userId): void
    {
        $exists = DB::table('user_subscriptions')
            ->where('user_id', $userId)
            ->where('status', SubscriptionStatusEnum::ACTIVE->value)
            ->where('expires_at', '>', now())
            ->exists();

        if ($exists) {
            return;
        }

        $startedAt = now()->subDays(random_int(0, 30));

        DB::table('user_subscriptions')->insert([
            'user_id'                => $userId,
            'status'                 => SubscriptionStatusEnum::ACTIVE->value,
            'stripe_subscription_id' => 'sub_seed_' . uniqid('', true),
            'started_at'             => $startedAt,
            'expires_at'             => $startedAt->copy()->addMonth(),
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);
    }

    /**
     * @throws RandomException
     */
    private function grantBookAccess(int $userId, int $bookId): void
    {
        DB::table('user_book_access')->upsert(
            [
                'user_id'                  => $userId,
                'book_id'                  => $bookId,
                'stripe_payment_intent_id' => 'pi_seed_' . uniqid('', true),
                'granted_at'               => now()->subDays(random_int(1, 60)),
                'created_at'               => now(),
                'updated_at'               => now(),
            ],
            ['user_id', 'book_id'],
            ['granted_at'],
        );
    }
}
