<?php

declare(strict_types=1);

namespace App\Http\Requests;

final class UserRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'email' => ['nullable', 'email:rfc', 'max:100'],
            'name' => ['nullable', 'string', 'max:100'],
        ];

        return $this->mergeRules($rules);
    }
}
