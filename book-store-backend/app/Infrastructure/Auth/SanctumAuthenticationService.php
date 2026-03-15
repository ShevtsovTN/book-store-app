<?php

declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Identity\Enums\RoleEnum;
use App\Domain\Identity\Interfaces\AuthenticationServiceInterface;
use App\Domain\Identity\ValueObjects\AuthToken;
use App\Infrastructure\Persistence\Models\UserModel;
use App\Domain\Identity\Entities\User;
use App\Domain\Identity\ValueObjects\UserId;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

final readonly class SanctumAuthenticationService implements AuthenticationServiceInterface
{
    public function __construct(
        private Request $request,
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

    public function revokeCurrentToken(UserId $userId): void
    {
        $bearerToken = $this->request->bearerToken();

        if ($bearerToken === null) {
            return;
        }

        PersonalAccessToken::findToken($bearerToken)?->delete();
    }
}
