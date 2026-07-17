<?php

namespace Modules\Glitter\Analytics\Tests\Feature\Public;

use Modules\Glitter\Analytics\Tests\ModuleTestCase;

class PageViewApiTest extends ModuleTestCase
{
    public function test_valid_guest_request_is_accepted_without_exposing_identifiers(): void
    {
        $response = $this->postJson('/api/modules/glitter-analytics/pageviews', ['path' => '/__analytics_feature__?secret=1', 'source' => 'initial', 'locale' => 'ko', 'visitor_id' => '018f0d34-1abc-4def-8abc-0123456789ab', 'session_id' => '018f0d34-2abc-4def-8abc-0123456789ab']);
        $response->assertStatus(202)->assertJsonStructure(['success', 'message', 'data' => ['recorded']]);
        $response->assertJsonMissingPath('data.event_id')->assertJsonMissingPath('data.ip_hash')
            ->assertJsonMissingPath('data.visitor_hash')->assertJsonMissingPath('data.session_hash');
    }

    public function test_identity_spoofing_fields_are_rejected(): void
    {
        $this->postJson('/api/modules/glitter-analytics/pageviews', ['path' => '/', 'source' => 'initial', 'ip_hash' => str_repeat('a', 64)])->assertStatus(422);
    }

    public function test_invalid_client_identifier_is_rejected(): void
    {
        $this->postJson('/api/modules/glitter-analytics/pageviews', ['path' => '/', 'source' => 'initial', 'visitor_id' => 'fixed-identifier'])->assertStatus(422);
    }

    public function test_invalid_source_and_unsafe_referrer_are_rejected(): void
    {
        $this->postJson('/api/modules/glitter-analytics/pageviews', ['path' => '/', 'source' => 'server', 'referrer' => 'javascript:alert(1)'])->assertStatus(422);
    }
}
