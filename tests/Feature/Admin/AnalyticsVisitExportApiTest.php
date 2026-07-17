<?php
namespace Modules\Glitter\Analytics\Tests\Feature\Admin;
use Carbon\CarbonImmutable;
use Modules\Glitter\Analytics\Models\AnalyticsEvent;
use Modules\Glitter\Analytics\Tests\ModuleTestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
class AnalyticsVisitExportApiTest extends ModuleTestCase {
 public function test_csv_requires_permission_and_streams_bom_without_sensitive_fields():void {
  $url="/api/modules/glitter-analytics/admin/visits/export";
  $this->getJson($url)->assertUnauthorized();
  $this->authenticateAdmin(true,"glitter-analytics.visits.view");
  AnalyticsEvent::query()->create(["event_type"=>"page_view","source"=>"initial","occurred_at"=>CarbonImmutable::now("UTC"),"path"=>"/csv-safe?secret=1","visitor_hash"=>str_repeat("a",64),"session_hash"=>str_repeat("b",64),"ip_hash"=>str_repeat("c",64),"referrer_host"=>"example.com"]);
  $response=$this->get($url."?path=csv-safe");
  $response->assertOk()->assertHeader("content-type","text/csv; charset=UTF-8");
  ob_start(); $response->sendContent(); $csv=(string)ob_get_clean();
  $this->assertStringStartsWith("\xEF\xBB\xBF",$csv); $this->assertStringContainsString("/csv-safe",$csv); $this->assertStringContainsString("\r\n",$csv); $this->assertStringNotContainsString("secret=1",$csv); $this->assertStringNotContainsString(str_repeat("a",64),$csv); $this->assertStringNotContainsString(str_repeat("c",64),$csv);
 }
 public function test_csv_denies_admin_without_permission_and_regular_member(): void {
  $url="/api/modules/glitter-analytics/admin/visits/export";
  $this->authenticateAdmin(false); $this->getJson($url)->assertForbidden();
  Sanctum::actingAs(User::factory()->create()); $this->getJson($url)->assertForbidden();
 }
}
