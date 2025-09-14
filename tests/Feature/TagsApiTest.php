<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Feature\Traits\Common;
use Tests\TestCase;

final class TagsApiTest extends TestCase
{
    use Common, RefreshDatabase;

    /**
     * Endpoint to test.
     */
    private string $endPoint = '/api/tags';

    /**
     * Test the tag can be created.
     */
    public function test_tag_can_be_created(): void
    {
        // call create api with validation error for empty input
        $this->postWithHeader($this->endPoint, [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', 'failed')
                ->has('errors', 1, fn (AssertableJson $json) => $json->where('type', 'validation')
                    ->where('key', 'name')
                    ->where('message', 'Name is required')
                    ->etc()
                )
            );

        // call create api with validation error for empty name and wrong color hexa code
        $this->postWithHeader($this->endPoint, ['name' => 'Test', 'color' => 'test'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', 'failed')
                ->has('errors', 1, fn (AssertableJson $json) => $json->where('type', 'validation')
                    ->where('key', 'color')
                    ->where('message', 'The color field must be a valid hexadecimal color.')
                    ->etc()
                )
            );

        // call create api successfully and check response assertions
        $this->postWithHeader($this->endPoint, ['name' => 'Test'])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson(
                fn (AssertableJson $json) => $json->where('data.id', 1)
                    ->where('data.name', 'Test')
                    ->etc()
            );

        // check database assertions
        $this->assertDatabaseHas('tags', ['name' => 'Test']);

        // try to create another tag with same name
        $this->postWithHeader($this->endPoint, ['name' => 'Test'])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', 'failed')
                ->has('errors', 1, fn (AssertableJson $json) => $json->where('type', 'validation')
                    ->where('key', 'name')
                    ->where('message', 'The name Test has already been taken.')
                    ->etc()
                )
            );
    }

    /**
     * Test the tags can be created.
     */
    public function test_tags_can_be_listed(): void
    {
        $count = 10;
        // check for empty
        $response = $this->getWithHeader($this->endPoint);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.meta.total', 0)
            ->assertJsonPath('data.results', []);

        // create 10 tags
        Tag::factory()->count($count)->create();

        $this->getWithHeader($this->endPoint)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonCount($count, 'data.results')
            ->assertJsonPath('data.meta.total', $count)
            ->assertJson(
                fn (AssertableJson $json) => $json->where('status', 'success')
                    ->has('data', fn (AssertableJson $json) => $json->whereAllType([
                        'meta' => 'array',
                        'results' => 'array',
                    ])->has(
                        'meta', fn (AssertableJson $json) => $json->where('total', $count)
                            ->where('per_page', TagService::DEFAULT_PAGINATION_LIMIT)
                            ->where('current_page', 1)
                            ->where('last_page', 1)
                            ->where('from', 1)
                            ->where('to', $count)
                            ->etc()
                    )
                    )
            );
    }

    /**
     * Test the tags can be created with filter.
     */
    public function test_tags_can_be_listed_with_filter(): void
    {
        // create tag
        Tag::factory()->count(2)
            ->sequence(
                ['name' => 'Test1'],
                ['name' => 'Test2']
            )
            ->create();

        // filter by name
        $filters = ['name' => 'Test1'];
        $this->getWithHeader($this->endPoint, $filters)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonCount(1, 'data.results')
            ->assertJsonPath('data.meta.total', 1);

        // add sort field and direction desc
        $filters = ['sort' => 'name', 'direction' => 'desc'];
        $response = $this->getWithHeader($this->endPoint, $filters);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.results.0.name', 'Test2');
    }

    /**
     * Test the tag can be show by id.
     */
    public function test_tag_can_be_show_by_id(): void
    {
        // create tag
        $tag = Tag::factory(['name' => 'Test'])->create();

        // show 404 if not found
        $this->getWithHeader($this->endPoint . '/222')
            ->assertStatus(Response::HTTP_NOT_FOUND);

        // show response if found by id
        $this->getWithHeader($this->endPoint . '/' . $tag->id)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.name', 'Test');
    }

    /**
     * Test the tag can be updated.
     */
    public function test_tag_can_be_updated(): void
    {
        // call to create the tag
        $response = $this->postWithHeader($this->endPoint, ['name' => 'Test']);
        $tag = $response->json()['data'] ?? [];
        if (empty($tag)) {
            $this->fail('Tag not created');
        }

        $tag = (object) $tag;
        $tag->name = 'Test-updated';
        $tag->colour = '#FF0000';

        // PUT to update the tag
        $this->putWithHeader($this->endPoint . '/' . $tag->id, (array) $tag)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.name', 'Test-updated');
    }

    /**
     * Test the tag can be deleted.
     */
    public function test_tag_can_be_deleted(): void
    {
        // call to create the tag
        $response = $this->postWithHeader($this->endPoint, ['name' => 'Test']);
        $tagId = $response->json()['data']['id'] ?? null;
        if (empty($tagId)) {
            $this->fail('Tag not created');
        }

        // DELETE the tag
        $this->deleteWithHeader($this->endPoint . '/' . $tagId)
            ->assertStatus(Response::HTTP_NO_CONTENT);

        // try to delete again should return 404
        $this->getWithHeader($this->endPoint . '/' . $tagId)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
