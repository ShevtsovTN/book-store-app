<?php

declare(strict_types=1);

namespace App\Application\Identity\UseCases\RegisterReader;

use App\Application\Identity\Interfaces\PasswordHasherInterface;
use App\Application\Identity\UseCases\AuthResult;
use App\Domain\Identity\Entities\User;
use App\Domain\Identity\Exceptions\ReaderAlreadyExistsException;
use App\Domain\Identity\Interfaces\AuthenticationServiceInterface;
use App\Domain\Identity\Interfaces\UserRepositoryInterface;
use App\Domain\Identity\ValueObjects\Email;
use App\Domain\Identity\ValueObjects\HashedPassword;

final readonly class RegisterReaderHandler
{
    public function __construct(
        private UserRepositoryInterface        $readers,
        private AuthenticationServiceInterface $auth,
        private PasswordHasherInterface        $hasher,
    ) {}

    public function handle(RegisterReaderCommand $command): AuthResult
    {
        $email = new Email($command->email);

        if ($this->readers->existsByEmail($email)) {
            throw ReaderAlreadyExistsException::withEmail($email->value);
        }

        $hashed  = $this->hasher->hash($command->plainPassword);
        $reader  = User::register(
            name:     $command->name,
            email:    $email,
            password: new HashedPassword($hashed),
        );

        $saved = $this->readers->save($reader);
        $token = $this->auth->issueToken($saved);

        return new AuthResult(user: $saved, token: $token);
    }
}
