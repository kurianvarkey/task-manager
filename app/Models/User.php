<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Role;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'api_key',
        'password',
        'role',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $attributes = [
        'role' => Role::User,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Generate API key
        static::creating(function (User $user) {
            $user->api_key = $user->api_key ?? $user->generateApiKey();
        });
    }

    /**
     * Generate API key
     */
    private function generateApiKey(): string
    {
        return str()->uuid()->toString() . '-' . time();
    }

    /**
     * Scope a query to only include user with a given email.
     */
    #[Scope]
    protected function email(Builder $query, string $email): void
    {
        $query->where('email', $email);
    }

    /**
     * Scope a query to only include user with a given api key.
     */
    #[Scope]
    protected function apiKey(Builder $query, string $apiKey): void
    {
        $query->where('api_key', $apiKey);
    }

    /**
     * Scope a query to only include tag with a given id.
     */
    #[Scope]
    protected function id(Builder $query, int $id): void
    {
        $query->where('id', $id);
    }
}
