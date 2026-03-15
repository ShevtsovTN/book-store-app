<?php

declare(strict_types=1);

namespace App\Domain\Identity\Interfaces;

use App\Domain\Identity\Entities\User;
use App\Domain\Identity\ValueObjects\AuthToken;
use App\Domain\Identity\ValueObjects\UserId;

interface AuthenticationServiceInterface
{
    public function issueToken(User $reader): AuthToken;
    public function revokeToken(UserId $readerId): void;
}
