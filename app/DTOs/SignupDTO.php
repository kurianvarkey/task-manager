<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\Role;

class SignupDTO
{
    /**
     * Construct a new SignupDTO instance.
     */
    public function __construct(
        public string $email,
        public string $name,
        public string $password,
        public string|Role|null $role = null
    ) {}

    /**
     * Create a new SignupDTO instance from an array of values.
     */
    public static function fromArray(array $data): self
    {
        $role = $data['role'] ?? null;
        if ($role && is_string($role)) {
            $role = Role::fromString($role);
        }

        return new self(
            email: $data['email'],
            name: $data['name'],
            password: $data['password'] ?? null,
            role: $role
        );
    }

    /**
     * Convert the object to its array representation.
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
