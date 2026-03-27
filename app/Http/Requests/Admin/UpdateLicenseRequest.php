<?php

namespace App\Http\Requests\Admin;

use App\Models\LicensePlan;
use Illuminate\Foundation\Http\FormRequest;

class UpdateLicenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'app_id' => ['required', 'integer', 'exists:apps,id'],
            'plan_id' => ['required', 'integer', 'exists:license_plans,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email:rfc', 'max:255'],
            'max_devices' => ['required', 'integer', 'min:1'],
            'expires_at' => ['nullable', 'date'],
            'grace_hours' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function after(): array
    {
        return [
            function (): void {
                $planId = $this->integer('plan_id');
                $appId = $this->integer('app_id');

                if ($planId === 0 || $appId === 0) {
                    return;
                }

                $planBelongsToApp = LicensePlan::query()
                    ->whereKey($planId)
                    ->where('app_id', $appId)
                    ->exists();

                if (! $planBelongsToApp) {
                    $this->validator->errors()->add('plan_id', 'The selected plan does not belong to the selected app.');
                }
            },
        ];
    }
}
