<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Base\BaseRequestFormData;

final class LoginForm extends BaseRequestFormData
{
    public string $email;
    public string $password;
    public bool $rememberMe = false;

    public function rules(): array
    {
        return [
            'email' => 'required|max:255',
            'password' => 'required',
            'rememberMe' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email необходимо заполнить email',
        ];
    }
}
