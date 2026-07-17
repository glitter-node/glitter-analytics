<?php
namespace Modules\Glitter\Analytics\Tests\Unit;
use Carbon\CarbonImmutable; use Modules\Glitter\Analytics\Repositories\Contracts\AnalyticsEventRepositoryInterface; use Modules\Glitter\Analytics\Services\AnalyticsQueryService; use PHPUnit\Framework\TestCase;
class AnalyticsQueryServiceTest extends TestCase {
 public function test_filter_uses_site_timezone_and_exclusive_utc_end():void{config(['app.default_user_timezone'=>'Asia/Seoul']);$s=new AnalyticsQueryService($this->createMock(AnalyticsEventRepositoryInterface::class));$f=$s->filter([],CarbonImmutable::parse('2026-07-17 12:00:00','Asia/Seoul'));$this->assertSame('2026-07-11',$f->fromDate);$this->assertSame('2026-07-17',$f->toDate);$this->assertSame('2026-07-10 15:00:00',$f->fromUtc->format('Y-m-d H:i:s'));$this->assertSame('2026-07-17 15:00:00',$f->toUtc->format('Y-m-d H:i:s'));}
 public function test_filter_preserves_whitelisted_values():void{config(['app.default_user_timezone'=>'UTC']);$s=new AnalyticsQueryService($this->createMock(AnalyticsEventRepositoryInterface::class));$f=$s->filter(['from'=>'2026-07-01','to'=>'2026-07-02','path'=>' /safe ','source'=>'spa','member'=>'guest','page'=>2,'per_page'=>50]);$this->assertSame('/safe',$f->path);$this->assertSame('spa',$f->source);$this->assertSame('guest',$f->member);$this->assertSame(50,$f->perPage);}
}
