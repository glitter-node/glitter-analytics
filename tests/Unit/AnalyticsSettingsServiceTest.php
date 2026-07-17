<?php

namespace Modules\Glitter\Analytics\Tests\Unit;

use App\Services\ModuleSettingsService;
use Modules\Glitter\Analytics\Services\AnalyticsSettingsService;
use PHPUnit\Framework\TestCase;

class AnalyticsSettingsServiceTest extends TestCase
{
    protected function setUp(): void
    {
        require_once dirname(__DIR__, 2).'/src/Services/AnalyticsSettingsService.php';
    }

    public function test_reads_general_settings(): void
    {
        $core = $this->createMock(ModuleSettingsService::class);
        $core->expects($this->once())->method('get')->with('glitter-analytics', 'general', [])->willReturn(['enabled' => true]);
        $this->assertSame(['enabled' => true], (new AnalyticsSettingsService($core))->get());
    }

    public function test_normalizes_paths_and_preserves_types_when_updating(): void
    {
        $input = ['enabled' => true, 'retention_days' => 90, 'ip_anonymization' => 'hash', 'exclude_bots' => true, 'exclude_paths' => [' /admin/* ', '', '/admin/*', '/api/*'], 'session_timeout_minutes' => 30, 'respect_do_not_track' => true];
        $normalized = [...$input, 'exclude_paths' => ['/admin/*', '/api/*']];
        $core = $this->createMock(ModuleSettingsService::class);
        $core->expects($this->once())->method('save')->with('glitter-analytics', ['general' => $normalized])->willReturn(true);
        $core->expects($this->once())->method('clearCache')->with('glitter-analytics');
        $core->expects($this->once())->method('get')->willReturn($normalized);
        $this->assertSame($normalized, (new AnalyticsSettingsService($core))->update($input));
    }
}
