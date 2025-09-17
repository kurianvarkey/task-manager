<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Rules\TagNameCheck;

final class TagRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [];
        switch ($this->getHttpMethod()) {
            case 'POST':
                $rules = [
                    'name' => ['required', 'string', 'max:100', new TagNameCheck],
                    'color' => ['hex_color'],
                ];
                break;

            case 'PUT': // PUT replaces the resource
                $id = trim(request()->segment(3));
                $rules = [
                    'name' => ['required', 'string', 'max:100',  new TagNameCheck($id)],
                    'color' => ['hex_color'],
                ];
                break;

            case 'PATCH': // PATCH updates the resource partially
                $id = trim(request()->segment(3));
                $rules = [
                    'name' => ['nullable', 'string', 'max:100',  new TagNameCheck($id)],
                    'color' => ['nullable', 'hex_color'],
                ];
                break;

            case 'GET':
            default:
                $rules = [
                    'name' => ['nullable', 'string', 'max:100'],
                ];
                break;
        }

        return $this->mergeRules($rules);
    }

    /**
     * Custom message for validation
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'color.required' => 'Color should be in hex format',
        ];
    }
}
