<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Base\BaseRequestQueryData;

final class ConfirmQuery extends BaseRequestQueryData
{
    public string $hash;

    public function rules(): array
    {
        return [
            'hash' => 'required|max:64',
        ];
    }

    public function messages(): array
    {
        return [];
    }
}
