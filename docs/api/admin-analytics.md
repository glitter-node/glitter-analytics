# Analytics administrator APIs

## Endpoints

- `GET /api/modules/glitter-analytics/admin/visits` requires `glitter-analytics.visits.view`.
- `GET /api/modules/glitter-analytics/admin/dashboard` requires `glitter-analytics.dashboard.view`.

Both endpoints require Sanctum authentication and an administrator role.

## Visit filters

The visit endpoint defaults to the seven calendar days ending today in the site timezone. `from` and `to` use inclusive `YYYY-MM-DD` dates and may span at most 90 calendar days. Optional filters are `path` (maximum 200 characters), `source` (`initial` or `spa`), `member` (`all`, `member`, or `guest`), `page`, and `per_page` (20, 50, or 100). Results use offset pagination and are ordered by `occurred_at DESC, id DESC`.

The response exposes occurrence time, normalized pathname, source, minimal member identity (ID and display name), visitor/session presence booleans, and normalized referrer host. It never exposes visitor/session/IP hashes, raw IP, cookies, tokens, authorization data, user agent, email, or referrer query strings.

## Metrics

The site timezone is `config('app.default_user_timezone')`; database timestamps remain UTC. Calendar boundaries are constructed in the site timezone and converted to half-open UTC ranges, so `whereDate()` is not used.

- Visitor: distinct non-null `visitor_hash`. Rows without a visitor hash do not increase this metric.
- Session: distinct non-null `session_hash`. Rows without a session hash do not increase this metric.
- Pageview: a stored `page_view` event inside the range.
- Today: the current site-timezone calendar day.
- Last seven days: today plus the previous six calendar days, with missing days returned as zero.
- Popular path: normalized pathname grouped in the database, ordered by count descending and path ascending.
- Referrer: stored `referrer_host`, excluding empty values and the configured same-origin host.
- Recent visits: the same visit query and Resource used by the paginated list, limited to ten.

Historical admin, API, build, non-path, and protocol-relative paths are excluded defensively from administrator queries.

## Retention and performance

The existing `retention_days` setting accepts 1–3650 days. The `glitter-analytics:prune` command deletes bounded chunks by `occurred_at` and is registered daily; `--dry-run` only counts eligible rows. Query APIs remain correct when rows have been removed and return normal empty responses. The default query window is seven days and the maximum is 90 days regardless of retention.

Phase 6 adds `(source, occurred_at)` and `(occurred_at, path)` indexes. Existing indexes cover time, member, visitor, and session access. MariaDB may briefly lock the table while adding indexes. Offset pagination and on-demand grouping are appropriate for the current event volume; cursor pagination and daily aggregate tables remain future work.

## Administrator verification

Open **Site Analytics → Visits** and verify date, path, source, member, and per-page filters, pagination, empty/error states, mobile width, and dark mode. Open **Site Analytics → Dashboard** and verify four summary cards, seven consecutive ISO dates, top paths, referrers or their empty state, and ten or fewer recent visits. Confirm no 64-character hash, IP, email, user agent, query string, or executable path markup appears.

The public `POST /api/modules/glitter-analytics/pageviews` contract and the opt-in browser diagnostics are unchanged.


## Phase 6.x 품질 기능

- 방문 기록 화면은 현재 필터를 유지한 UTF-8 BOM CSV 스트리밍 내보내기를 제공합니다. CSV에는 발생 시각, pathname, source, 회원 여부, visitor/session hash 존재 여부와 referrer domain만 포함하며 IP와 hash 원문은 제외합니다.
- 최근 7일 추이는 방문자와 페이지뷰 2개 series를 의존성 없는 반응형 막대로 표시하고, hover title과 aria-label을 제공합니다.
- 대시보드는 private `module.glitter-analytics.dashboard` 채널의 `dashboard.updated` 이벤트로 갱신됩니다. WebSocket이 꺼져 있거나 실패해도 pageview 저장은 유지됩니다.
- `glitter-analytics:prune`은 보존 기간 이전 이벤트를 chunk 단위로 삭제합니다. `--dry-run`은 삭제 없이 건수만 출력하며 모듈 scheduler에 daily로 등록됩니다.
