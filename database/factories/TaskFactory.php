<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Tag;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'due_date' => fake()->date(),
            'assigned_to' => UserFactory::new()->create()->id,
            'status' => fake()->randomElement(TaskStatus::cases()),
            'priority' => fake()->randomElement(TaskPriority::cases()),
            'version' => 1,
            'metadata' => [],
        ];
    }

    /**
     * Configure the model after it's been created.
     */
    public function configure()
    {
        return $this->afterCreating(function (Task $task) {
            $tags = Tag::inRandomOrder()->take(rand(1, 3))->pluck('id');
            $task->tags()->attach($tags);
        });
    }
}
