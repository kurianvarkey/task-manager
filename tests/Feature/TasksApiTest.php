<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
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
     * Test the task validations
     */
    public function test_check_validations(): void
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
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', 'failed')
                ->has('errors', 8)
                ->has('errors.0', fn (AssertableJson $json) => $json->where('key', 'title')->etc())
                ->has('errors.1', fn (AssertableJson $json) => $json->where('key', 'status')->etc())
                ->has('errors.2', fn (AssertableJson $json) => $json->where('key', 'priority')->etc())
                ->has('errors.3', fn (AssertableJson $json) => $json->where('key', 'due_date')->etc())
                ->has('errors.4', fn (AssertableJson $json) => $json->where('key', 'assigned_to')->etc())
                ->has('errors.5', fn (AssertableJson $json) => $json->where('key', 'assigned_to.id')->etc())
                ->has('errors.6', fn (AssertableJson $json) => $json->where('key', 'metadata')->etc())
                ->has('errors.7', fn (AssertableJson $json) => $json->where('key', 'tags')->etc())
            );

        // call create api with validation error for non existing user
        $this->postWithHeader($this->endPoint, [
            'title' => 'Test Task',
            'assigned_to' => ['id' => 100],
        ])->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', 'failed')
                ->has('errors', 1)
                ->has('errors.0', fn (AssertableJson $json) => $json->where('key', 'assigned_to.id')
                    ->where('message', fn ($value) => str_contains($value, 'does not exist'))
                    ->etc()
                )

            );
    }

    /**
     * Test the task can be created
     */
    public function test_task_can_be_created(): void
    {
        // create user
        $user = User::factory()->create();
        Tag::factory()->count(2)->create();

        $now = now();

        // call create api successfully and check response assertions
        $response = $this->postWithHeader($this->endPoint, [
            'title' => 'Test Task',
            'description' => 'Test Task description',
            'due_date' => $now->addDays(2)->format('Y-m-d'),
            'assigned_to' => ['id' => $user->id],
            'metadata' => ['start_date' => $now->format('Y-m-d')],
            'tags' => array_map(fn (array $tag) => ['id' => $tag['id']], Tag::all()->toArray()),
        ])->assertStatus(Response::HTTP_CREATED)
            ->assertJson(
                fn (AssertableJson $json) => $json->where('data.id', 1)
                    ->where('data.title', 'Test Task')
                    ->where('data.description', 'Test Task description')
                    ->where('data.status', TaskStatus::Pending->toString())
                    ->where('data.priority', TaskPriority::Medium->toString())
                    ->where('data.assigned_to.id', $user->id)
                    ->has('data.tags', 2)
                    ->whereType('data.metadata', 'array')
                    ->whereType('data.due_date', 'string')
                    ->etc()
            );

        $task = $response->json()['data'] ?? [];
        if (empty($task)) {
            $this->fail('Task not created');
        }

        // check database assertions
        $this->assertDatabaseHas('tasks', ['title' => 'Test Task']);
        $this->assertDatabaseHas('task_tags', ['task_id' => $task['id']]);
    }

    /**
     * Test the task can be show by id.
     */
    public function test_task_can_be_show_by_id(): void
    {
        // create tag
        Tag::factory()->count(5)->create();
        // create task
        $task = Task::factory(['title' => 'Test Task'])->create();

        // show 404 if not found
        $this->getWithHeader($this->endPoint . '/222')
            ->assertStatus(Response::HTTP_NOT_FOUND);

        // show response if found by id
        $this->getWithHeader($this->endPoint . '/' . $task->id)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.title', 'Test Task');
    }

    /**
     * Test the task can be updated.
     */
    public function test_task_can_be_updated(): void
    {
        // call to create the tag
        $response = $this->postWithHeader($this->endPoint, ['title' => 'Test Task']);
        $task = $response->json()['data'] ?? [];
        if (empty($task)) {
            $this->fail('Task not created');
        }

        $task = (object) $task;
        $task->title = 'Task-updated';
       
        // PUT to update the tag
        $this->putWithHeader($this->endPoint . '/' . $task->id, (array) $task)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.title', 'Task-updated');
    }

    /**
     * Test the task can be deleted.
     */
    public function test_task_can_be_deleted(): void
    {
        // call to create the task
        $response = $this->postWithHeader($this->endPoint, ['title' => 'Test Task']);
        $taskId = $response->json()['data']['id'] ?? null;
        if (empty($taskId)) {
            $this->fail('Task not created');
        }

        // DELETE the task
        $this->deleteWithHeader($this->endPoint . '/' . $taskId)
            ->assertStatus(Response::HTTP_NO_CONTENT);

        // try to delete again should return 404
        $this->getWithHeader($this->endPoint . '/' . $taskId)
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
