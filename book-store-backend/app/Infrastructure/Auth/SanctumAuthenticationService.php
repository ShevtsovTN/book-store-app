<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Identity\Enums\RoleEnum;
use App\Domain\Identity\Interfaces\AuthenticationServiceInterface;
use App\Domain\Identity\ValueObjects\AuthToken;
use App\Infrastructure\Persistence\Models\UserModel;
use App\Domain\Identity\Entities\User;
use App\Domain\Identity\ValueObjects\UserId;
use Illuminate\Contracts\Auth\Guard;

final readonly class SanctumAuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        private Guard $guard,
    ) {}

    public function issueToken(User $user): AuthToken
    {
        /** @var UserModel $model */
        $model = UserModel::query()->findOrFail($user->getId()->value);

        $tokenName = match ($user->getRole()) {
            RoleEnum::ADMIN  => 'admin-token',
            RoleEnum::READER => 'reader-token',
        };

        return new AuthToken(
            $model->createToken($tokenName)->plainTextToken
        );
    }

    public function revokeToken(UserId $userId): void
    {
        /** @var UserModel|null $model */
        $model = $this->guard->user();

        if ($model === null || $model->id !== $userId->value) {
            return;
        }

        $model->currentAccessToken()->delete();
    }
}
