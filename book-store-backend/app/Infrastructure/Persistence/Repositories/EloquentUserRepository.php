<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Domain\Identity\Entities\User;
use App\Domain\Identity\Interfaces\UserRepositoryInterface;
use App\Domain\Identity\ValueObjects\Email;
use App\Domain\Identity\ValueObjects\HashedPassword;
use App\Domain\Identity\ValueObjects\UserId;
use App\Infrastructure\Persistence\Models\UserModel;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function save(User $reader): User
    {
        if ($reader->getId() === null) {
            $model = UserModel::query()->create($this->toArray($reader));
        } else {
            $model = UserModel::query()->findOrFail($reader->getId()->value);
            $model->update($this->toArray($reader));
        }

        return $this->toDomain($model);
    }

    public function findByEmail(Email $email): ?User
    {
        $model = UserModel::query()
            ->where('email', $email->value)
            ->first();

        return $model ? $this->toDomain($model) : null;
    }

    public function existsByEmail(Email $email): bool
    {
        return UserModel::query()
            ->where('email', $email->value)
            ->exists();
    }

    private function toArray(User $reader): array
    {
        return [
            'name'     => $reader->getName(),
            'email'    => $reader->getEmail()->value,
            'password' => $reader->getPassword()->value,
            'role'     => $reader->getRole()->value,
        ];
    }

    private function toDomain(UserModel $model): User
    {
        return new User(
            id:       new UserId($model->id),
            name:     $model->name,
            email:    new Email($model->email),
            password: new HashedPassword($model->password),
            role:     $model->role,
        );
    }
}
