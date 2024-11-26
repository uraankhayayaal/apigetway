<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Base\BaseRequestFormData;

final class ResetPasswordForm extends BaseRequestFormData
{
    public string $passwordResetToken;
    public string $password;

    public function rules(): array
    {
        return [
            'passwordResetToken' => 'required|max:255',
            'password' => 'required',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
