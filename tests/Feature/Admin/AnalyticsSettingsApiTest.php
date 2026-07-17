<?php

namespace Modules\Glitter\Analytics\Tests\Feature\Admin;

use Modules\Glitter\Analytics\Tests\ModuleTestCase;

class AnalyticsSettingsApiTest extends ModuleTestCase
{
    private string $url = '/api/modules/glitter-analytics/admin/settings';

    public function test_guest_cannot_read_settings(): void
    {
        $this->getJson($this->url)->assertUnauthorized();
    }

    public function test_invalid_update_is_rejected(): void
    {
        $this->authenticateAdmin();
        $this->putJson($this->url, ['retention_days' => 0])->assertStatus(422);
    }
}
