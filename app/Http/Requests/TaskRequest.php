<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Requests\Rules\Boolean;
use App\Http\Requests\Rules\DateRange;
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
                    'description' => ['nullable', 'string'],
                    'status' => [Rule::in(TaskStatus::getValues())],
                    'priority' => [Rule::in(TaskPriority::getValues())],
                    'due_date' => ['nullable', 'date', 'after_or_equal:today'],
                    'assigned_to' => ['nullable', 'array'],
                    'assigned_to.id' => ['required_with:assigned_to', 'integer', new UserCheck],
                    'metadata' => ['nullable', 'array'],
                    'tags' => ['nullable', 'array'],
                    'tags.*.id' => ['required_with:tags', 'integer'],
                ];
                break;

            case 'PUT': // PUT replaces the resource
                $rules = [
                    'title' => ['required', 'string', 'min:5', 'max:100'],
                    'description' => ['nullable', 'string'],
                    'status' => ['required', Rule::in(TaskStatus::getValues())],
                    'priority' => ['required', Rule::in(TaskPriority::getValues())],
                    'due_date' => ['nullable', 'date', 'after_or_equal:today'],
                    'assigned_to' => ['nullable', 'array'],
                    'assigned_to.id' => ['required_with:assigned_to', 'integer', new UserCheck],
                    'metadata' => ['nullable', 'array'],
                    'tags' => ['nullable', 'array'],
                    'tags.*.id' => ['required_with:tags', 'integer'],
                    'version' => ['required', 'integer'],
                ];
                break;

            case 'PATCH': // PATCH updates the resource partially
                $rules = [
                    'status' => ['nullable', Rule::in(TaskStatus::getValues())],
                    'priority' => ['nullable', Rule::in(TaskPriority::getValues())],
                ];
                break;

            case 'GET':
            default:
                $rules = [
                    'status' => ['nullable', Rule::in(TaskStatus::getValues())],
                    'priority' => ['nullable', Rule::in(TaskPriority::getValues())],
                    'assigned_to' => ['nullable', 'integer', new UserCheck],
                    'tags' => ['nullable', 'string', 'regex:/^(\d+,)*\d+$/'],
                    'due_date_range' => ['nullable', new DateRange],
                    'keyword' => ['nullable', 'string'],
                    'only_deleted' => ['nullable', new Boolean],
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
            'priority.in' => 'The selected priority is invalid. Valid priorities are: ' . implode(', ', TaskPriority::getValues()),
        ];
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        if (! in_array($this->getHttpMethod(), ['POST', 'PUT'])) {
            return;
        }

        $data = $this->validated();
        if (! empty($data['tags'])) {
            $tagIds = array_filter(array_column($data['tags'], 'id'));
            $tagsCount = Tag::whereIn('id', $tagIds)->count();

            if (count($tagIds) !== $tagsCount) {
                $this->validator->errors()->add('tags', 'Invalid tag ids. Some tag(s) is not found.');
            }
        }

        $this->validator->setData($data);
    }
}
