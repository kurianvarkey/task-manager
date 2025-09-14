<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Tag;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TagModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test tag can be created
     */
    public function test_tag_can_be_created()
    {
        // create
        $tag = Tag::factory(
            ['name' => 'Test']
        )->create();

        // check for tag count
        $this->assertEquals(1, Tag::count());

        // update
        $tag->name = 'Test1';
        $tag->save();

        // check for tag count
        $this->assertEquals(1, Tag::where('name', 'Test1')->count());

        // delete
        $tag->delete();
        // check for tag count
        $this->assertEquals(0, Tag::count());
    }

    /**
     * Test name unique and not null validation
     */
    public function test_check_unique_or_null_name()
    {
        $this->expectException(QueryException::class);

        Tag::factory()
            ->count(2)
            ->sequence(
                ['name' => 'Test'],
                ['name' => 'Test'],
            )
            ->create();

        // check for null name
        Tag::factory(
            ['name' => null]
        )->create();
    }

    /**
     * Test scopeName
     */
    public function test_scope_name()
    {
        Tag::factory(
            ['name' => 'Test']
        )
            ->create();

        // check for user scopeName
        $this->assertEquals(1, Tag::name('Test')->count());
    }

    /**
     * Test scopeId
     */
    public function test_scope_id()
    {
        $tag = Tag::factory()->create();

        // check for user scopeId
        $this->assertEquals(1, Tag::id($tag->id)->count());
    }
}
