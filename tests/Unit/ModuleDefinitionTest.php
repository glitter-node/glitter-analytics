<?php

namespace Modules\Glitter\Analytics\Tests\Unit;

use App\Extension\AbstractModule;
use Modules\Glitter\Analytics\Module;
use PHPUnit\Framework\TestCase;

class ModuleDefinitionTest extends TestCase
{
    private Module $module;

    protected function setUp(): void
    {
        require_once dirname(__DIR__, 2).'/module.php';
        $this->module = new Module;
    }

    public function test_module_metadata_and_base_class_are_correct(): void
    {
        $this->assertInstanceOf(AbstractModule::class, $this->module);
        $this->assertSame('glitter-analytics', $this->module->getIdentifier());
        $this->assertSame('Glitter.kr', $this->module->getVendor());
        $this->assertSame('0.7.0', $this->module->getVersion());
    }

    public function test_four_admin_permissions_are_declared(): void
    {
        $actions = [];
        foreach ($this->module->getPermissions()['categories'] as $category) {
            foreach ($category['permissions'] as $permission) {
                $actions[] = $category['identifier'].'.'.$permission['action'];
                $this->assertSame('admin', $permission['type']);
                $this->assertSame(['admin'], $permission['roles']);
            }
        }$this->assertSame(['dashboard.view', 'visits.view', 'settings.view', 'settings.update'], $actions);
    }

    public function test_realtime_channel_and_daily_retention_schedule_are_declared(): void
    {
        $this->assertSame(["permission" => "glitter-analytics.dashboard.view"], $this->module->getChannels()["module.glitter-analytics.dashboard"]);
        $this->assertSame("glitter-analytics:prune", $this->module->getSchedules()[0]["command"]);
        $this->assertSame("daily", $this->module->getSchedules()[0]["schedule"]);
    }

    public function test_three_child_admin_menus_are_declared(): void
    {
        $menu = $this->module->getAdminMenus()[0];
        $this->assertSame('glitter-analytics', $menu['slug']);
        $this->assertNull($menu['url']);
        $this->assertSame(['glitter-analytics-dashboard', 'glitter-analytics-visits', 'glitter-analytics-settings'], array_column($menu['children'], 'slug'));
        $this->assertSame(['/admin/analytics', '/admin/analytics/visits', '/admin/analytics/settings'], array_column($menu['children'], 'url'));
    }
}
