<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Base\BaseRequestFormData;

final class ForgotPasswordForm extends BaseRequestFormData
{
    public string $email;

    public function rules(): array
    {
        return [
            'email' => 'required|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Email необходимо заполнить email',
        ];
    }
}
