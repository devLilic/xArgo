<?php

namespace Database\Factories;

use App\Models\App;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<App>
 */
class AppFactory extends Factory
{
    protected $model = App::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();
        $slug = Str::slug($name).'-'.fake()->unique()->numberBetween(100, 999);

        return [
            'name' => $name,
            'slug' => $slug,
            'app_id' => 'app.'.str_replace('-', '.', $slug),
            'is_active' => true,
        ];
    }
}
