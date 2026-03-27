<?php

namespace App\Http\Requests\Admin;

use App\Domain\Auth\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(array_column(Role::cases(), 'value'))],
        ];
    }
}
