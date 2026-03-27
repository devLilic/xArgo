<?php

namespace App\Http\Requests\Admin;

use App\Models\App;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAppRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var App $app */
        $app = $this->route('app');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('apps', 'slug')->ignore($app->id)],
            'app_id' => ['required', 'string', 'max:255', Rule::unique('apps', 'app_id')->ignore($app->id)],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
