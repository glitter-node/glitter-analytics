<?php
namespace Modules\Glitter\Analytics\Tests\Feature\Admin;
use Carbon\CarbonImmutable; use Modules\Glitter\Analytics\Models\AnalyticsEvent; use Modules\Glitter\Analytics\Tests\ModuleTestCase;
class AnalyticsDashboardApiTest extends ModuleTestCase {
 private string $url='/api/modules/glitter-analytics/admin/dashboard';
 public function test_auth_and_permission_are_required():void{$this->getJson($this->url)->assertUnauthorized();$this->authenticateAdmin(false);$this->getJson($this->url)->assertForbidden();}
 public function test_dashboard_returns_distinct_counts_zero_filled_days_and_no_hashes():void{$this->authenticateAdmin();$now=CarbonImmutable::now('UTC');foreach(['/popular','/popular','/other'] as $i=>$path)AnalyticsEvent::query()->create(['event_type'=>'page_view','source'=>$i?'spa':'initial','occurred_at'=>$now,'path'=>$path,'visitor_hash'=>$i<2?str_repeat('a',64):str_repeat('d',64),'session_hash'=>$i<2?str_repeat('b',64):str_repeat('e',64)]);$r=$this->getJson($this->url);$r->assertOk()->assertJsonCount(7,'data.trend')->assertJsonPath('data.popular_paths.0.path','/popular');$json=$r->getContent();$this->assertStringNotContainsString(str_repeat('a',64),$json);$this->assertStringNotContainsString('ip_hash',$json);}
}
