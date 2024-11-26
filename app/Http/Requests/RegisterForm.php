<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Base\BaseRequestFormData;

final class RegisterForm extends BaseRequestFormData
{
    public string $username;
    public string $email;
    public string $phone;
    public string $password;
    public bool $isAgreeMarketing;
    public bool $isAgreePolicy;

    public function rules(): array
    {
        return [
            'username' => 'required|max:255',
            'email' => 'required|max:255',
            'phone' => 'required|max:255',
            'password' => 'required',
            'isAgreeMarketing' => 'required|boolean',
            'isAgreePolicy' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email необходимо заполнить email',
        ];
    }
}
