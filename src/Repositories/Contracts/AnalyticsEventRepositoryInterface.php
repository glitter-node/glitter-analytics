<?php
namespace Modules\Glitter\Analytics\Repositories\Contracts;
use Carbon\CarbonImmutable; use Illuminate\Pagination\LengthAwarePaginator; use Illuminate\Support\Collection; use Modules\Glitter\Analytics\DTO\AnalyticsVisitFilter; use Modules\Glitter\Analytics\DTO\CreateAnalyticsEventData; use Modules\Glitter\Analytics\Models\AnalyticsEvent;
interface AnalyticsEventRepositoryInterface {
 public function create(CreateAnalyticsEventData $data):AnalyticsEvent; public function findLatestBySessionHash(string $hash):?AnalyticsEvent; public function clearUserReference(int $id):int; public function deleteOlderThan(CarbonImmutable $cutoff,int $limit):int; public function countOlderThan(CarbonImmutable $cutoff):int;
 public function paginateVisits(AnalyticsVisitFilter $filter):LengthAwarePaginator; public function cursorVisits(AnalyticsVisitFilter $filter):\Illuminate\Support\LazyCollection; public function recentVisits(AnalyticsVisitFilter $filter,int $limit):Collection; public function summary(AnalyticsVisitFilter $filter,CarbonImmutable $todayUtc):array; public function dailyTrend(array $days):array; public function popularPaths(AnalyticsVisitFilter $filter,int $limit):array; public function topReferrers(AnalyticsVisitFilter $filter,int $limit,?string $siteHost):array;
}
