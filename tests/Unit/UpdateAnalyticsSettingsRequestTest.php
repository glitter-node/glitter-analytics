<?php

namespace Modules\Glitter\Analytics\Tests\Unit;

use Modules\Glitter\Analytics\Http\Requests\UpdateAnalyticsSettingsRequest;
use PHPUnit\Framework\TestCase;

class UpdateAnalyticsSettingsRequestTest extends TestCase
{
    protected function setUp(): void
    {
        require_once dirname(__DIR__, 2).'/src/Http/Requests/UpdateAnalyticsSettingsRequest.php';
    }

    public function test_normalizes_lf_and_crlf_lines(): void
    {
        $this->assertSame(
            ['/admin/*', '/api/*', '/build/*'],
            UpdateAnalyticsSettingsRequest::normalizeExcludePathsText(" /admin/* \r\n/api/*\n\n/build/* "),
        );
    }

    public function test_removes_duplicates_and_does_not_split_commas(): void
    {
        $this->assertSame(
            ['/a,/b', '/c'],
            UpdateAnalyticsSettingsRequest::normalizeExcludePathsText("/a,/b\n/c\n/c"),
        );
    }

    public function test_rules_retain_path_limits(): void
    {
        $rules = (new UpdateAnalyticsSettingsRequest)->rules();
        $this->assertContains('max:100', $rules['exclude_paths']);
        $this->assertContains('max:512', $rules['exclude_paths.*']);
        $this->assertSame(['sometimes', 'string'], $rules['exclude_paths_text']);
    }
}
