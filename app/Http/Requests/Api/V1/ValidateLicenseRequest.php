<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class ValidateLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'licenseKey' => ['required', 'string', 'max:255'],
            'activationToken' => ['required', 'string', 'max:255'],
            'appId' => ['required', 'string', 'max:255'],
            'appVersion' => ['required', 'string', 'max:64'],
            'machineId' => ['required', 'string', 'max:255'],
            'installationId' => ['required', 'string', 'max:255'],
        ];
    }
}
