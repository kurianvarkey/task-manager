<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\SignupDTO;
use App\Exceptions\GeneralException;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

final class AuthService
{
    /**
     * Create a new user.
     */
    public function createUser(SignupDTO $dto): ?User
    {
        return (new User)->create($dto->toArray());
    }

    /**
     * Logs in a user with the provided email and password.
     */
    public function login(string $email, ?string $password = null): ?User
    {
        // Find the user with the provided email
        $user = User::email($email)->first();

        if ($user === null) {
            throw new GeneralException('User not found.', Response::HTTP_NOT_FOUND);
        }

        if ($password !== null && ! Hash::check($password, $user->password)) {
            throw new GeneralException('Login failed.', Response::HTTP_UNAUTHORIZED);
        }

        return $user;
    }
}
