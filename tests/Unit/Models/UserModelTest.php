<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user crud
     */
    public function test_user_crud()
    {
        // create
        $user = User::factory(
            ['name' => 'Jack']
        )->create();

        // check for user count
        $this->assertEquals(1, User::where('name', 'Jack')->count());

        // update
        $user->name = 'Jill';
        $user->save();

        // check for user count
        $this->assertEquals(1, User::where('name', 'Jill')->count());

        // delete
        $user->delete();
        // check for user count
        $this->assertEquals(0, User::where('name', 'Jill')->count());
    }

    /**
     * Test email unique and not null validation
     */
    public function test_check_unique_or_null_email()
    {
        $this->expectException(QueryException::class);

        // check for unique email
        User::factory()
            ->count(2)
            ->sequence(
                ['email' => 'jack@jack.com'],
                ['email' => 'jack@jack.com'],
            )
            ->create();

        // check for null email
        User::factory(
            ['email' => null]
        )->create();
    }

    /**
     * Test scopeEmail
     */
    public function test_scope_email()
    {
        User::factory()
            ->count(2)
            ->sequence(
                ['email' => 'jack@jack.com'],
                ['email' => 'jill@jill.com'],
            )
            ->create();

        // check for user scopeEmail
        $this->assertEquals(1, User::email('jack@jack.com')->count());
    }

    /**
     * Test scopeApiKey
     */
    public function test_scope_api_key()
    {
        User::factory(
            ['api_key' => '12345678']
        )->create();

        // check for user scopeApiKey
        $this->assertEquals(1, User::apiKey('12345678')->count());
    }
}
