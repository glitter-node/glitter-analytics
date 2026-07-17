<?php

namespace Modules\Glitter\Analytics\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Base\AdminBaseController;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Modules\Glitter\Analytics\Http\Requests\AdminAnalyticsVisitRequest;
use Modules\Glitter\Analytics\Http\Resources\AnalyticsVisitResource;
use Modules\Glitter\Analytics\Services\AnalyticsQueryService;
use Modules\Glitter\Analytics\Services\AnalyticsCsvSanitizer;

class AnalyticsVisitExportController extends AdminBaseController
{
    public function __construct(private AnalyticsQueryService $queries, private AnalyticsCsvSanitizer $csv) {}

    public function __invoke(AdminAnalyticsVisitRequest $request): StreamedResponse
    {
        $timezone = (string) config("app.default_user_timezone", "Asia/Seoul");
        return response()->streamDownload(function () use ($request, $timezone): void {
            $output = fopen("php://output", "wb");
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ["occurred_at", "path", "source", "is_member", "has_visitor", "has_session", "referrer_domain"], ",", "\"", "", "\r\n");
            foreach ($this->queries->export($request->validated()) as $event) {
                $row = (new AnalyticsVisitResource($event))->resolve(Request::create("/"));
                fputcsv($output, $this->csv->row([$row["occurred_at"], $row["path"], $row["source"], $row["member"]["is_member"] ? "1" : "0", $row["has_visitor"] ? "1" : "0", $row["has_session"] ? "1" : "0", $row["referrer_host"] ?? ""]), ",", "\"", "", "\r\n");
            }
            fclose($output);
        }, "analytics-visits-".now($timezone)->format("Ymd-His").".csv", ["Content-Type" => "text/csv; charset=UTF-8", "X-Content-Type-Options" => "nosniff"]);
    }
}
