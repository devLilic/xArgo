<?php

namespace Tests\Feature\Apps;

use App\Models\App;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_apps_table_exposes_the_required_schema(): void
    {
        $this->assertTrue(Schema::hasTable('apps'));
        $this->assertTrue(Schema::hasColumns('apps', [
            'name',
            'slug',
            'app_id',
            'is_active',
            'deleted_at',
        ]));
    }

    public function test_apps_can_be_created_with_active_default(): void
    {
        $app = App::query()->create([
            'name' => 'X Argo Desktop',
            'slug' => 'x-argo-desktop',
            'app_id' => 'com.xargo.desktop',
        ]);

        $this->assertDatabaseHas('apps', [
            'id' => $app->id,
            'name' => 'X Argo Desktop',
            'slug' => 'x-argo-desktop',
            'app_id' => 'com.xargo.desktop',
            'is_active' => true,
        ]);
    }

    public function test_app_id_must_be_unique(): void
    {
        App::factory()->create([
            'app_id' => 'com.xargo.desktop',
        ]);

        $this->expectException(QueryException::class);

        App::factory()->create([
            'app_id' => 'com.xargo.desktop',
        ]);
    }
}
