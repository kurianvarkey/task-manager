<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Role;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class SignupRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email:rfc', 'max:100', 'unique:users,email'],
            'name' => ['required', 'string', 'max:100'],
            // 'password' => ['required', Password::defaults(), 'confirmed'],
            // 'password_confirmation' => ['required_with:password', 'string'],
            'password' => ['required', Password::defaults()],
            'role' => ['required',  Rule::in(Role::getValues())],
        ];
    }

    /**
     * Custom message for validation
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email is required!',
            'email.unique' => 'The :attribute ' . $this->email . ' has already been taken.',
            'name.required' => 'Name is required!',
            'password.required' => 'Password is required!',
            'password.confirmed' => 'Password does not match!',
            'role.in' => 'The selected role is invalid. Valid roles are: ' . implode(', ', Role::getValues()),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => trim(strtolower(filter_var($this->email, FILTER_SANITIZE_EMAIL))),
        ]);
    }
}
