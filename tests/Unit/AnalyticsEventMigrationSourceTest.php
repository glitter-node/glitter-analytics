<?php

namespace Modules\Glitter\Analytics\Tests\Unit;

use PHPUnit\Framework\TestCase;

class AnalyticsEventMigrationSourceTest extends TestCase
{
    public function test_migration_has_privacy_safe_columns_comments_indexes_and_down(): void
    {
        $path = dirname(__DIR__, 2).'/database/migrations/2026_07_14_000001_create_analytics_events_table.php';
        $source = file_get_contents($path);

        $this->assertStringContainsString("Schema::create('analytics_events'", $source);
        $this->assertStringContainsString('public function down(): void', $source);
        $this->assertStringContainsString("Schema::dropIfExists('analytics_events')", $source);
        $this->assertStringNotContainsString("'ip_address'", $source);
        $this->assertStringNotContainsString("'user_agent'", $source);
        $this->assertStringNotContainsString("'cookies'", $source);
        $this->assertStringNotContainsString("'query_string'", $source);
        $this->assertStringNotContainsString("'authorization'", $source);
        $this->assertSame(16, substr_count($source, '->comment('));
        $this->assertSame(5, substr_count($source, '->index('));
    }
}
