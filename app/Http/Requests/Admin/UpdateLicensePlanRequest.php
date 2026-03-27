<?php

namespace App\Http\Requests\Admin;

use App\Domain\Licensing\LicenseDurationType;
use App\Models\LicensePlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLicensePlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var LicensePlan $licensePlan */
        $licensePlan = $this->route('plan');

        return [
            'app_id' => ['required', 'integer', 'exists:apps,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('license_plans', 'code')
                    ->where(fn ($query) => $query->where('app_id', $this->integer('app_id')))
                    ->ignore($licensePlan->id),
            ],
            'duration_type' => ['required', Rule::enum(LicenseDurationType::class)],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'default_max_devices' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
