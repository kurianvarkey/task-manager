<?php

declare(strict_types=1);

namespace App\Http\Resources\Task;

use App\Http\Resources\Tag\TagResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->whenHas('id'),
            'title' => $this->whenHas('title'),
            'description' => $this->whenHas('description'),
            'status' => $this->whenHas('status')?->value,
            'priority' => $this->whenHas('priority')?->value,
            'due_date' => $this->whenHas('due_date'),
            'assigned_to' => new UserResource($this->whenLoaded('assignedUser')),
            'metadata' => $this->whenHas('metadata'),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'version' => $this->whenHas('version'),
            'created_at' => $this->whenHas('created_at'),
            'updated_at' => $this->whenHas('updated_at'),
        ];
    }
}
