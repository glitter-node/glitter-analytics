<?php
namespace Modules\Glitter\Analytics\Tests\Feature\Admin;
use Carbon\CarbonImmutable; use Modules\Glitter\Analytics\Models\AnalyticsEvent; use Modules\Glitter\Analytics\Tests\ModuleTestCase;
class AnalyticsVisitsApiTest extends ModuleTestCase {
 private string $url='/api/modules/glitter-analytics/admin/visits';
 public function test_auth_and_permission_are_required():void{$this->getJson($this->url)->assertUnauthorized();$this->authenticateAdmin(false);$this->getJson($this->url)->assertForbidden();}
 public function test_filters_paginates_and_hides_sensitive_fields():void{$this->authenticateAdmin(true,'glitter-analytics.visits.view');$now=CarbonImmutable::now('UTC');AnalyticsEvent::query()->create(['event_type'=>'page_view','source'=>'initial','occurred_at'=>$now,'path'=>'/phase-six?secret=1','visitor_hash'=>str_repeat('a',64),'session_hash'=>str_repeat('b',64),'ip_hash'=>str_repeat('c',64)]);$r=$this->getJson($this->url.'?path=phase-six&source=initial&member=guest&per_page=20');$r->assertOk()->assertJsonPath('data.data.0.path','/phase-six')->assertJsonPath('data.data.0.source','initial')->assertJsonPath('data.data.0.has_visitor',true)->assertJsonMissingPath('data.data.0.visitor_hash')->assertJsonMissingPath('data.data.0.session_hash')->assertJsonMissingPath('data.data.0.ip_hash')->assertJsonMissingPath('data.data.0.user_agent');}
 public function test_rejects_excessive_range_and_per_page():void{$this->authenticateAdmin(true,'glitter-analytics.visits.view');$this->getJson($this->url.'?from=2026-01-01&to=2026-04-01')->assertStatus(422);$this->getJson($this->url.'?per_page=500')->assertStatus(422);}
 public function test_empty_result_is_successful():void{$this->authenticateAdmin(true,'glitter-analytics.visits.view');$this->getJson($this->url.'?path=definitely-not-present')->assertOk()->assertJsonPath('data.meta.total',0);}
}
