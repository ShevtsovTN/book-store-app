<?php

declare(strict_types=1);

namespace App\Application\Identity\UseCases\LoginAdmin;

use App\Application\Identity\Interfaces\PasswordHasherInterface;
use App\Application\Identity\UseCases\AuthResult;
use App\Domain\Identity\Enums\RoleEnum;
use App\Domain\Identity\Exceptions\InvalidCredentialsException;
use App\Domain\Identity\Interfaces\AuthenticationServiceInterface;
use App\Domain\Identity\Interfaces\UserRepositoryInterface;
use App\Domain\Identity\ValueObjects\Email;

final readonly class LoginAdminHandler
{
    public function __construct(
        private UserRepositoryInterface        $readers,
        private AuthenticationServiceInterface $auth,
        private PasswordHasherInterface        $hasher,
    ) {}

    public function handle(LoginAdminCommand $command): AuthResult
    {
        $email  = new Email($command->email);
        $reader = $this->readers->findByEmail($email);

        if ($reader === null
            || $reader->getRole() !== RoleEnum::ADMIN
            || !$this->hasher->verify($command->plainPassword, $reader->getPassword()->value)
        ) {
            throw InvalidCredentialsException::create();
        }

        $token = $this->auth->issueToken($reader);

        return new AuthResult(user: $reader, token: $token);
    }
}
