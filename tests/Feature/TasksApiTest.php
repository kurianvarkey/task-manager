<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\Feature\Traits\Common;
use Tests\TestCase;

final class TasksApiTest extends TestCase
{
    use Common, RefreshDatabase;

    /**
     * Endpoint to test.
     */
    private string $endPoint = '/api/tasks';

    /**
     * Test the task can be created.
     */
    public function test_task_can_be_created(): void
    {
        // call create api with validation error for empty input
        $this->postWithHeader($this->endPoint, [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', 'failed')
                ->has('errors', 1, fn (AssertableJson $json) => $json->where('type', 'validation')
                    ->where('key', 'title')
                    ->etc()
                )
            );

        // call create api with validation error for empty input
        $this->postWithHeader($this->endPoint, [
            'title' => 'Test',
            'description' => 'Test',
            'status' => 'test',
            'priority' => 'test',
            'due_date' => 'test',
            'assigned_to' => 'test',
            'metadata' => 'test',
            'tags' => 'test',
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json->has('errors', 7)
                ->has('errors.0', fn (AssertableJson $json) => $json->where('key', 'title')->etc())
                ->has('errors.1', fn (AssertableJson $json) => $json->where('key', 'status')->etc())
                ->has('errors.2', fn (AssertableJson $json) => $json->where('key', 'priority')->etc())
                ->has('errors.3', fn (AssertableJson $json) => $json->where('key', 'due_date')->etc())
                ->has('errors.4', fn (AssertableJson $json) => $json->where('key', 'assigned_to')->etc())
                ->has('errors.5', fn (AssertableJson $json) => $json->where('key', 'metadata')->etc())
                ->has('errors.6', fn (AssertableJson $json) => $json->where('key', 'tags')->etc())
            );
    }
}
