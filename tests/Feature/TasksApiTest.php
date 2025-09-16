<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Tag;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskService;
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
        ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', 'failed')
                ->has('errors', 9)
                ->has('errors.0', fn (AssertableJson $json) => $json->where('key', 'title')->etc())
                ->has('errors.1', fn (AssertableJson $json) => $json->where('key', 'status')->etc())
                ->has('errors.2', fn (AssertableJson $json) => $json->where('key', 'priority')->etc())
                ->has('errors.3', fn (AssertableJson $json) => $json->where('key', 'due_date')->etc())
                ->has('errors.4', fn (AssertableJson $json) => $json->where('key', 'due_date')->etc())
                ->has('errors.5', fn (AssertableJson $json) => $json->where('key', 'assigned_to')->etc())
                ->has('errors.6', fn (AssertableJson $json) => $json->where('key', 'assigned_to.id')->etc())
                ->has('errors.7', fn (AssertableJson $json) => $json->where('key', 'metadata')->etc())
                ->has('errors.8', fn (AssertableJson $json) => $json->where('key', 'tags')->etc())
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
                    ->where('data.status', TaskStatus::Pending->value)
                    ->where('data.priority', TaskPriority::Medium->value)
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
        // call to create the task
        $response = $this->postWithHeader($this->endPoint, ['title' => 'Test Task']);
        $task = $response->json()['data'] ?? [];
        if (empty($task)) {
            $this->fail('Task not created');
        }

        $task = (object) $task;
        $task->title = 'Task-updated';

        // PUT to update the task
        $this->putWithHeader($this->endPoint . '/' . $task->id, (array) $task)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.title', 'Task-updated');

        // PUT to update the task
        // this time we expect a optimistic lock exception
        $task->title = 'Task-updated-new';
        $this->putWithHeader($this->endPoint . '/' . $task->id, (array) $task)
            ->assertStatus(Response::HTTP_CONFLICT)
            ->assertJson(fn (AssertableJson $json) => $json->where('status', 'failed')
                ->has('errors', 1)
                ->has('errors.0', fn (AssertableJson $json) => $json->where('type', 'system')
                    ->where('message', fn ($value) => str_contains($value, 'updated by someone'))
                    ->etc()
                )

            );
    }

    /**
     * Test the tag can be patched.
     */
    public function test_task_can_be_patched(): void
    {
        // call to create the task
        $response = $this->postWithHeader($this->endPoint, ['title' => 'Test Task']);
        $task = $response->json()['data'] ?? [];
        if (empty($task)) {
            $this->fail('Task not created');
        }

        $task = (object) $task;

        // Patch to update the tag partially
        $dueDate = now()->addDays(2);
        $this->patchWithHeader($this->endPoint . '/' . $task->id, ['due_date' => $dueDate->format('Y-m-d')])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.due_date', $dueDate->format('Y-m-d'));
    }

    /**
     * Test the tag can be patched.
     */
    public function test_task_can_toggle_status(): void
    {
        // call to create the task
        $response = $this->postWithHeader($this->endPoint, ['title' => 'Test Task'])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('data.status', TaskStatus::Pending->value);

        $task = $response->json()['data'] ?? [];
        if (empty($task)) {
            $this->fail('Task not created');
        }

        $task = (object) $task;

        $nextStatuses = [
            TaskStatus::InProgress,
            TaskStatus::Completed,
            TaskStatus::Pending,
        ];

        foreach ($nextStatuses as $status) {
            $this->patchWithHeader($this->endPoint . '/' . $task->id . '/toggle-status')
                ->assertStatus(Response::HTTP_OK)
                ->assertJsonPath('data.status', $status->value);
        }
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

    /**
     * Test the task can be deleted and restored.
     */
    public function test_task_can_be_restored(): void
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

        // try to restore the deleted task
        $this->patchWithHeader($this->endPoint . '/' . $taskId . '/restore')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.id', $taskId);
    }

    /**
     * Test the tasks can be created with filter.
     */
    public function test_tasks_can_be_listed(): void
    {
        $count = 10;
        // check for empty
        $response = $this->getWithHeader($this->endPoint);

        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.meta.total', 0)
            ->assertJsonPath('data.results', []);

        // create 10 tasks
        Task::factory()->count($count)->create();

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
                            ->where('per_page', TaskService::DEFAULT_PAGINATION_LIMIT)
                            ->where('current_page', 1)
                            ->where('last_page', 1)
                            ->where('from', 1)
                            ->where('to', $count)
                            ->etc()
                    ))
            );
    }

    /**
     * Test the tasks can be listed with status filter.
     */
    public function test_tasks_can_be_listed_with_status_filter(): void
    {
        // create tasks
        Task::factory()->count(6)
            ->sequence(
                ['title' => 'Task 1', 'status' => TaskStatus::Completed],
                ['title' => 'Task 2', 'status' => TaskStatus::Completed],
                ['title' => 'Task 3', 'status' => TaskStatus::InProgress],
                ['title' => 'Task 4', 'status' => TaskStatus::InProgress],
                ['title' => 'Task 5', 'status' => TaskStatus::InProgress],
                ['title' => 'Task 6', 'status' => TaskStatus::Pending],
            )
            ->create();

        // expected results
        $exptectedResults = [
            [
                'status' => TaskStatus::Completed->value,
                'no_of_tasks' => 2,
            ],
            [
                'status' => TaskStatus::InProgress->value,
                'no_of_tasks' => 3,
            ],
            [
                'status' => TaskStatus::Pending->value,
                'no_of_tasks' => 1,
            ],
        ];

        foreach ($exptectedResults as $result) {
            $this->getWithHeader($this->endPoint, ['status' => $result['status']])
                ->assertStatus(Response::HTTP_OK)
                ->assertJsonPath('data.meta.total', $result['no_of_tasks'])
                ->assertJsonCount($result['no_of_tasks'], 'data.results');
        }
    }

    /**
     * Test the tasks can be listed with priority filter.
     */
    public function test_tasks_can_be_listed_with_priority_filter(): void
    {
        // create tasks
        Task::factory()->count(6)
            ->sequence(
                ['title' => 'Task 1', 'priority' => TaskPriority::Low],
                ['title' => 'Task 2', 'priority' => TaskPriority::High],
                ['title' => 'Task 3', 'priority' => TaskPriority::High],
                ['title' => 'Task 4', 'priority' => TaskPriority::High],
                ['title' => 'Task 5', 'priority' => TaskPriority::Medium],
                ['title' => 'Task 6', 'priority' => TaskPriority::Medium],
            )
            ->create();

        // expected results
        $exptectedResults = [
            [
                'priority' => TaskPriority::Low->value,
                'no_of_tasks' => 1,
            ],
            [
                'priority' => TaskPriority::High->value,
                'no_of_tasks' => 3,
            ],
            [
                'priority' => TaskPriority::Medium->value,
                'no_of_tasks' => 2,
            ],
        ];

        foreach ($exptectedResults as $result) {
            $this->getWithHeader($this->endPoint, ['priority' => $result['priority']])
                ->assertStatus(Response::HTTP_OK)
                ->assertJsonPath('data.meta.total', $result['no_of_tasks'])
                ->assertJsonCount($result['no_of_tasks'], 'data.results');
        }
    }

    /**
     * Test the tasks can be listed with assigned_to filter.
     */
    public function test_tasks_can_be_listed_with_assigned_to_filter(): void
    {
        // create user
        $user = User::factory()->create();

        // create tasks
        Task::factory()->count(5)->create();

        // create tasks with assigned_to
        Task::factory()->count(2)
            ->sequence(
                ['title' => 'Task 1', 'assigned_to' => $user->id],
                ['title' => 'Task 2', 'assigned_to' => $user->id],
            )
            ->create();

        // expected results
        $this->getWithHeader($this->endPoint, ['assigned_to' => $user->id])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.meta.total', 2)
            ->assertJsonCount(2, 'data.results');

    }

    /**
     * Test the tasks can be listed with due_date_range filter.
     */
    public function test_tasks_can_be_listed_with_due_date_range_filter(): void
    {
        // create tasks
        $now = now();
        $twoDaysLater = now()->addDays(2)->format('Y-m-d');
        $sevenDaysLater = now()->addDays(7)->format('Y-m-d');

        // +2 days from now
        Task::factory()->count(2)
            ->sequence(
                ['title' => 'Task 1', 'due_date' => $twoDaysLater],
                ['title' => 'Task 2', 'due_date' => $twoDaysLater],
            )->create();

        // +7 days from now
        Task::factory()->count(2)
            ->sequence(
                ['title' => 'Task 1', 'due_date' => $sevenDaysLater],
                ['title' => 'Task 2', 'due_date' => $sevenDaysLater],
            )->create();

        // expected failed results
        $exptectedFailedResults = [
            [
                'due_dates' => ',' . $now->format('Y-m-d'),
                'no_of_tasks' => 0,
            ],
            [
                'due_dates' => 'date1,date2',
                'no_of_tasks' => 0,
            ],
        ];

        foreach ($exptectedFailedResults as $result) {
            $this->getWithHeader($this->endPoint, ['due_date_range' => $result['due_dates']])
                ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
                ->assertJson(fn (AssertableJson $json) => $json->where('status', 'failed')
                    ->has('errors', 1)
                    ->has('errors.0', fn (AssertableJson $json) => $json->where('key', 'due_date_range')
                        ->etc()
                    )
                );
        }

        $exptectedResults = [
            [
                'due_dates' => $now->format('Y-m-d') . ',' . $now->format('Y-m-d'),
                'no_of_tasks' => 0,
            ],
            [
                'due_dates' => $now->format('Y-m-d') . ',' . $twoDaysLater,
                'no_of_tasks' => 2,
            ],
            [
                'due_dates' => $now->format('Y-m-d') . ',' . $sevenDaysLater,
                'no_of_tasks' => 4,
            ],
        ];

        foreach ($exptectedResults as $result) {
            $this->getWithHeader($this->endPoint, ['due_date_range' => $result['due_dates']])
                ->assertStatus(Response::HTTP_OK)
                ->assertJsonPath('data.meta.total', $result['no_of_tasks'])
                ->assertJsonCount($result['no_of_tasks'], 'data.results');
        }
    }

    /**
     * Test the tasks can be listed with tags filter.
     */
    public function test_tasks_can_be_listed_with_tags_filter(): void
    {
        // create tasks
        Tag::factory()->count(5)
            ->sequence(
                ['name' => 'Tag 1'],
                ['name' => 'Tag 2'],
                ['name' => 'Tag 3'],
                ['name' => 'Tag 4'],
                ['name' => 'Tag 5'],
            )
            ->create();

        // create tasks with 2 tags
        $tasks = Task::factory()->count(2)->create();
        $tags = Tag::whereIn('name', ['Tag 1', 'Tag 2'])->get()->pluck('id')->toArray();
        foreach ($tasks as $task) {
            $task->tags()->attach($tags);
        }

        // create tasks with 1 tags
        $task = Task::factory()->create();
        $tags = Tag::whereIn('name', ['Tag 3'])->get()->pluck('id')->toArray();
        $task->tags()->attach($tags);

        // create tasks with 5 tags
        $tasks = Task::factory()->count(5)->create();
        $tags = Tag::whereIn('name', ['Tag 1', 'Tag 4', 'Tag 5'])->get()->pluck('id')->toArray();
        foreach ($tasks as $task) {
            $task->tags()->attach($tags);
        }

        $exptectedResults = [
            [
                'tags' => '1,2',
                'no_of_tasks' => 7,
            ],
            [
                'tags' => '2',
                'no_of_tasks' => 2,
            ],
            [
                'tags' => '3',
                'no_of_tasks' => 1,
            ],
            [
                'tags' => '4',
                'no_of_tasks' => 5,
            ],
            [
                'tags' => '5',
                'no_of_tasks' => 5,
            ],
            [
                'tags' => '1,2,3',
                'no_of_tasks' => 8,
            ],
        ];

        // expected results
        foreach ($exptectedResults as $result) {
            $this->getWithHeader($this->endPoint, ['tags' => $result['tags']])
                ->assertStatus(Response::HTTP_OK)
                ->assertJsonPath('data.meta.total', $result['no_of_tasks'])
                ->assertJsonCount($result['no_of_tasks'], 'data.results');
        }
    }

    /**
     * Test the tasks can be listed with keyword filter.
     */
    public function test_tasks_can_be_listed_with_keyword_filter(): void
    {
        // create tasks
        Task::factory()->count(3)
            ->sequence(
                ['title' => 'Task 1 for USA', 'description' => 'Task 1 Dubai description'],
                ['title' => 'Task 2 for Dubai', 'description' => 'Task 2 USA'],
                ['title' => 'Task 3 for Dubai', 'description' => 'Task 3 Dubai description'],
            )
            ->create();

        // expected results
        $exptectedResults = [
            [
                'keyword' => 'USA',
                'no_of_tasks' => 2,
            ],
            [
                'keyword' => 'Dubai',
                'no_of_tasks' => 3,
            ],
        ];

        foreach ($exptectedResults as $result) {
            $this->getWithHeader($this->endPoint, ['keyword' => $result['keyword']])
                ->assertStatus(Response::HTTP_OK)
                ->assertJsonPath('data.meta.total', $result['no_of_tasks'])
                ->assertJsonCount($result['no_of_tasks'], 'data.results');
        }
    }

    /**
     * Test the tasks can be sorted with sort column.
     */
    public function test_tasks_can_be_listed_with_sort_column(): void
    {
        // create tag
        Task::factory()->count(3)
            ->sequence(
                ['title' => 'Task 1', 'priority' => TaskPriority::Low->value, 'due_date' => now()->addDays(2)->format('Y-m-d')],
                ['title' => 'Task 2', 'priority' => TaskPriority::Medium->value, 'due_date' => now()->addDays(3)->format('Y-m-d')],
                ['title' => 'Task 3', 'priority' => TaskPriority::High->value, 'due_date' => now()->addDays(4)->format('Y-m-d')],
            )
            ->create();

        // sort by title
        $filters = ['sort' => 'title', 'direction' => 'desc'];
        $response = $this->getWithHeader($this->endPoint, $filters);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.results.0.title', 'Task 3');

        // sort by title
        $filters = ['sort' => 'priority', 'direction' => 'asc'];
        $response = $this->getWithHeader($this->endPoint, $filters);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.results.0.priority', 'high');

        // sort by due_date
        $filters = ['sort' => 'due_date', 'direction' => 'asc'];
        $response = $this->getWithHeader($this->endPoint, $filters);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.results.0.title', 'Task 1');

        // sort by created_at
        $filters = ['sort' => 'created_at', 'direction' => 'asc'];
        $response = $this->getWithHeader($this->endPoint, $filters);
        $response->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.results.0.title', 'Task 1');
    }

    /**
     * Test the tasks can be listed with assigned_to filter.
     */
    public function test_tasks_can_be_listed_with_only_deleted_filter(): void
    {
        // create tasks of 5 and take 3
        $tasks = Task::factory()->count(5)->create()->take(3);

        // expected results 5 tasks
        $this->getWithHeader($this->endPoint)
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.meta.total', 5)
            ->assertJsonCount(5, 'data.results');

        // delete 3 tasks
        foreach ($tasks as $task) {
            $task->delete();
        }

        // expected results 3 tasks
        $this->getWithHeader($this->endPoint, ['only_deleted' => true])
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.meta.total', 3)
            ->assertJsonCount(3, 'data.results');
    }

    /**
     * Test the tasks can be listed with keyword filter.
     */
    public function test_tasks_logs_can_be_listed(): void
    {
        // create task
        $task = Task::factory()->create();

        $this->getWithHeader($this->endPoint . '/' . $task->id . '/logs')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.meta.total', 1)
            ->assertJsonCount(1, 'data.results')
            ->assertJsonPath('data.results.0.operation_type', 'created');

        // update task
        $task->title = 'Task 1';
        $task->save();

        // now we have 2 logs
        $this->getWithHeader($this->endPoint . '/' . $task->id . '/logs')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.meta.total', 2)
            ->assertJsonPath('data.results.0.operation_type', 'updated');

        // delete task
        $task->delete();

        // now we have 3 logs
        $this->getWithHeader($this->endPoint . '/' . $task->id . '/logs')
            ->assertStatus(Response::HTTP_NOT_FOUND);

        // restore task
        $task = Task::withTrashed()->find($task->id);
        $task->restore();

        // now we have 4 logs
        $this->getWithHeader($this->endPoint . '/' . $task->id . '/logs')
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.meta.total', 4)
            ->assertJsonPath('data.results.0.operation_type', 'restored');
    }
}
