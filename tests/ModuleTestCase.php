<?php

namespace Modules\Glitter\Analytics\Tests;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Sanctum;
use Modules\Glitter\Analytics\Providers\AnalyticsServiceProvider;
use Tests\TestCase;

abstract class ModuleTestCase extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registerModuleAutoload();
        $this->app->register(AnalyticsServiceProvider::class);
        $this->registerModuleRoutes();
    }

    protected function modulePath(string $path = ''): string
    {
        $base = dirname(__DIR__);

        return $path === '' ? $base : $base.'/'.$path;
    }

    protected function authenticateAdmin(bool $withPermission = true, string $permissionIdentifier = 'glitter-analytics.dashboard.view'): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(
            ['identifier' => 'admin'],
            ['name' => ['ko' => '관리자', 'en' => 'Administrator']]
        );
        $user->roles()->syncWithoutDetaching([$role->id]);

        if ($withPermission) {
            $permission = Permission::firstOrCreate(
                ['identifier' => $permissionIdentifier],
                [
                    'name' => ['ko' => '분석 대시보드 조회', 'en' => 'View analytics dashboard'],
                    'type' => 'admin',
                    'order' => 0,
                ]
            );
            $role->permissions()->syncWithoutDetaching([$permission->id]);
        }

        Sanctum::actingAs($user);

        return $user;
    }

    private function registerModuleAutoload(): void
    {
        $base = $this->modulePath();

        spl_autoload_register(static function (string $class) use ($base): void {
            $prefix = 'Modules\\Glitter\\Analytics\\';
            if (! str_starts_with($class, $prefix)) {
                return;
            }

            $relative = substr($class, strlen($prefix));
            $file = $base.'/src/'.str_replace('\\', '/', $relative).'.php';
            if (is_file($file)) {
                require_once $file;
            }
        });
    }

    private function registerModuleRoutes(): void
    {
        $routes = $this->modulePath('src/routes/api.php');
        if (! is_file($routes)) {
            return;
        }

        Route::prefix('api/modules/glitter-analytics')
            ->name('api.modules.glitter-analytics.')
            ->middleware('api')
            ->group($routes);
    }
}
