<?php

declare(strict_types=1);

namespace App\Http\Resources\Task\Log;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray(Request $request): array
    {
        return [
            'task_id' => $this->whenHas('task_id'),
            'operation_type' => $this->whenHas('operation_type', function () {
                return $this->operation_type ? $this->operation_type->toString() : '';
            }),
            'changes' => $this->whenHas('changes'),
            'created_by' => new UserResource($this->whenLoaded('createdBy')),
            'created_at' => $this->whenHas('created_at'),
        ];
    }
}
