<?php

namespace Modules\Glitter\Analytics\Tests\Feature\Admin;

use Modules\Glitter\Analytics\Tests\ModuleTestCase;

class AnalyticsStatusApiTest extends ModuleTestCase
{
    private string $url = '/api/modules/glitter-analytics/admin/status';

    public function test_guest_is_rejected(): void
    {
        $this->getJson($this->url)->assertUnauthorized();
    }

    public function test_admin_without_permission_is_rejected(): void
    {
        $this->authenticateAdmin(false);
        $this->getJson($this->url)->assertForbidden();
    }

    public function test_authorized_admin_receives_phase_one_status(): void
    {
        $this->authenticateAdmin();

        $this->getJson($this->url)
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.identifier', 'glitter-analytics')
            ->assertJsonPath('data.version', '0.7.0')
            ->assertJsonPath('data.enabled', true)
            ->assertJsonPath('data.status', 'ready')
            ->assertJsonPath('data.tracking_enabled', true)
            ->assertJsonPath('data.database_ready', true);
    }
}
