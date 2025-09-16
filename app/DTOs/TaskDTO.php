<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use InvalidArgumentException;

class TaskDTO
{
    /**
     * The HTTP method.
     */
    private static $httpMethod;

    /**
     * Create a new instance.
     */
    public function __construct(
        public ?string $title,
        public ?string $description = null,
        public string|int|null $status = null,
        public string|int|null $priority = null,
        public ?string $due_date = null,
        public ?array $assigned_to = null,
        public int|string|null $version = null,
        public ?array $metadata = null,
        public ?array $tags = null
    ) {}

    /**
     * Create a new instance from an array.
     */
    public static function fromArray(array $data, string $httpMethod): self
    {
        self::$httpMethod = $httpMethod;

        $title = $data['title'] ?? null;
        // @codeCoverageIgnoreStart
        if (self::$httpMethod === 'POST' && empty($title)) {
            throw new InvalidArgumentException('Title is required');
        }
        // @codeCoverageIgnoreEnd

        return new self(
            title: $title,
            description: $data['description'] ?? null,
            status: $data['status'] ?? null,
            priority: $data['priority'] ?? null,
            due_date: $data['due_date'] ?? null,
            assigned_to: $data['assigned_to'] ?? null,
            version: (int) ($data['version'] ?? null),
            metadata: $data['metadata'] ?? null,
            tags: $data['tags'] ?? null
        );
    }

    /**
     * Set enums
     */
    public function setEnums(): void
    {
        if ($this->status && is_string($this->status)) {
            $this->status = TaskStatus::fromString($this->status)?->value;
        }

        if ($this->priority && is_string($this->priority)) {
            $this->priority = TaskPriority::fromString($this->priority)?->value;
        }
    }

    /**
     * Set default values
     */
    public function setDefaults(): void
    {
        $this->setEnums();

        $this->status = $this->status ?? TaskStatus::Pending->value;
        $this->priority = $this->priority ?? TaskPriority::Medium->value;
        $this->version = 1;
    }

    /**
     * Convert the object to its array representation.
     */
    public function toArray(): array
    {
        $data = collect(get_object_vars($this))->except(['tags'])->toArray();
        $data['assigned_to'] = $data['assigned_to']['id'] ?? null;

        return self::$httpMethod === 'PATCH'
        ? array_filter($data, fn ($value) => ! is_null($value))
        : $data;
    }

    /**
     * Convert the object to its array representation.
     */
    public function tags(): array
    {
        $data = get_object_vars($this);

        return ! empty($data['tags']) ? array_filter(array_column($data['tags'], 'id')) : [];
    }

    /**
     * Get the HTTP method.
     */
    public function getHttpMethod(): string
    {
        return self::$httpMethod;
    }
}
