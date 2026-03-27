<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RebindLicenseActivationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'machine_id' => ['required', 'string', 'max:255'],
            'installation_id' => ['required', 'string', 'max:255'],
            'device_label' => ['nullable', 'string', 'max:255'],
        ];
    }
}
