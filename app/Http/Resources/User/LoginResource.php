<?php

declare(strict_types=1);

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->whenHas('name'),
            'email' => $this->whenHas('email'),
            'role' => $this->whenHas('role'),
            'api_key' => $this->whenHas('api_key'),
        ];
    }
}
