<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\Rules\UserCheck;
use App\Models\Tag;
use Illuminate\Validation\Rule;

final class TaskRequest extends BaseRequest
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
                    'title' => ['required', 'string', 'min:5', 'max:100'],
                    'description' => ['string'],
                    'status' => [Rule::in(TaskStatus::getValues())],
                    'priority' => [Rule::in(TaskPriority::getValues())],
                    'due_date' => ['date'],
                    'assigned_to' => ['integer', new UserCheck],
                    'metadata' => ['array'],
                    'tags' => ['nullable', 'array'],
                    'tags.*.id' => ['required_with:tags', 'integer'],
                ];
                break;

            case 'PUT':
            case 'PATCH':
                $rules = [
                    'title' => ['string', 'min:5', 'max:100'],
                    'description' => ['string'],
                    'status' => [Rule::in(TaskStatus::getValues())],
                    'priority' => [Rule::in(TaskPriority::getValues())],
                    'due_date' => ['date'],
                    'assigned_to' => ['integer', new UserCheck],
                    'version' => ['integer'],
                    'metadata' => ['array'],
                    'tags' => ['nullable', 'array'],
                    'tags.*.id' => ['required_with:tags', 'integer'],
                ];
                break;

            case 'GET':
            default:
                $rules = [
                    'name' => ['string', 'max:100'],
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
            'title.required' => 'Title is required',
            'status.in' => 'The selected status is invalid. Valid statuses are: ' . implode(', ', TaskStatus::getValues()),
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        if (! in_array($this->getHttpMethod(), ['POST', 'PUT', 'PATCH'])) {
            return;
        }

        $data = $this->validated();

        if (! empty($data['tags'])) {
            $tagIds = array_filter(array_column($data['tags'], 'id'));
            $tagsCount = Tag::whereIn('id', $tagIds)->count();

            if (count($tagIds) !== $tagsCount) {
                $this->validator->errors()->add('tags', 'Invalid tag ids. Some tags not found.');
            }
        }

        $this->validator->setData($data);
    }
}
