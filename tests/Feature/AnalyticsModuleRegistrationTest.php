<?php

namespace Modules\Glitter\Analytics\Tests\Feature;

use Modules\Glitter\Analytics\Module;
use Modules\Glitter\Analytics\Tests\ModuleTestCase;

class AnalyticsModuleRegistrationTest extends ModuleTestCase
{
    public function test_registration_sources_define_settings_route_and_layout(): void
    {
        require_once $this->modulePath('module.php');
        $module = new Module;

        $defaults = json_decode(file_get_contents($module->getSettingsDefaultsPath()), true, 512, JSON_THROW_ON_ERROR);
        $routes = json_decode(file_get_contents($this->modulePath('resources/routes/admin.json')), true, 512, JSON_THROW_ON_ERROR);
        $layout = json_decode(file_get_contents($this->modulePath('resources/layouts/admin/admin_analytics_dashboard.json')), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(['general'], $defaults['_meta']['categories']);
        $this->assertTrue($defaults['defaults']['general']['enabled']);
        $this->assertSame(90, $defaults['defaults']['general']['retention_days']);
        $this->assertSame('*/admin/analytics', $routes['routes'][0]['path']);
        $this->assertSame('admin_analytics_dashboard', $routes['routes'][0]['layout']);
        $this->assertSame('admin_analytics_dashboard', $layout['layout_name']);
        $this->assertSame(['glitter-analytics.dashboard.view'], $layout['permissions']);
    }
}
