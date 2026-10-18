# Pageview collection API

## Endpoint

`POST /api/modules/glitter-analytics/pageviews`

Authentication is optional (`optional.sanctum`). If an Origin header is present, it must exactly match the current site origin. Requests are limited to 120 per minute by the standard throttle middleware. The endpoint accepts JSON and returns HTTP 202 because collection is an auxiliary, accepted operation.

## Request

```json
{"path":"/boards/free","referrer":"https://example.com/","source":"initial","locale":"ko","visitor_id":"018f0d34-1abc-4def-8abc-0123456789ab","session_id":"018f0d34-2abc-4def-8abc-0123456789ab"}
```

- `path`: required same-origin path beginning with `/`, up to 2048 input characters. Protocol-relative paths and control characters are rejected. Query and fragment are removed; the stored result is at most 512 characters.
- `referrer`: optional HTTP/HTTPS URL. Userinfo, query and fragment are never stored. Host is lowercased.
- `source`: `initial` or `spa`.
- `locale`: optional configured supported locale.
- `visitor_id` and `session_id`: optional UUID v4 values generated randomly by the browser tracker. The server HMAC-signs them with separate purposes; their raw values are never stored or returned.
- Client-supplied time, user ID, event type, hashes, user agent, cookie and token fields are prohibited.

## Response

```json
{"success":true,"message":"...","data":{"recorded":true}}
```

Normal exclusions also return 202 with `recorded: false`. Internal exclusion reasons and event identifiers are not exposed. Validation failures return 422 and rate-limit failures return 429.

## Collection policy and privacy

Collection is skipped when the module setting is disabled, DNT or GPC is respected, the request is prefetch, BotDetector classifies it as a bot, or the normalized path matches an excluded path such as `/admin/*`, `/api/*`, or `/build/*`.

Raw IP, User-Agent, cookie, authorization, query and fragment values are not stored. IP mode `hash` stores a purpose-separated HMAC; `mask` first applies IPv4 /24 or IPv6 /64 network masking; `none` stores no IP hash. The bundled browser tracker stores a random visitor UUID in localStorage for at most 90 days and a random tab-session UUID in sessionStorage. Storage failures fall back to in-memory IDs. The server stores only purpose-separated HMAC-SHA256 hashes; no fingerprint is synthesized. IDs are analysis labels, never authentication credentials.

The global browser asset sends one initial pageview and observes pathname changes from pushState, replaceState, and popstate. Query-only and fragment-only changes are ignored. DNT, GPC, iframe, admin, API, build, and static-asset paths are skipped before transport. It uses same-origin sendBeacon first and one fetch keepalive fallback, with no persistent retry or offline queue. BFCache restoration does not create another pageview.

Duplicate suppression is a five-second best-effort module-cache guard. Its key contains only a SHA-256 digest, never raw IP or path. Because the current `CacheInterface` has no atomic add operation, simultaneous races may rarely record more than one event; cache failure is fail-open so analytics cannot block the user experience.


## Browser verification and diagnostics

Open a normal public page with `?analytics_debug=1` (for example, `/?analytics_debug=1`). This enables diagnostics for the current tab in `sessionStorage` under `glitter_analytics_debug`, so logs continue across SPA navigation even when later URLs omit the query. Open `?analytics_debug=0` to disable it. The debug query never changes the tracked path because the tracker reads only `location.pathname`.

In Chrome DevTools:

1. Open Network and enable **Preserve log** and **Disable cache**.
2. Select **All** (not only Fetch/XHR), filter for `pageviews`, and perform a hard reload.
3. Confirm one `POST /api/modules/glitter-analytics/pageviews`. A beacon may appear as beacon, ping, other, or under All rather than Fetch/XHR.
4. For a fetch fallback, confirm HTTP 202. A successful `sendBeacon()` call only logs `queued=true`: this means the browser accepted the request into its send queue, not that the server returned HTTP 202.
5. Navigate A → B → A through the SPA. Each pathname change sends one payload with `source=spa`. Query-only, fragment-only, and same-path rerenders send nothing.

The initial payload has `source=initial` and includes `path`, `visitor_id`, and `session_id` (plus optional locale/referrer). Diagnostic events are prefixed with `[glitter-analytics]`: `init`, `eligibility`, `initial:deferred`, `initial:ready`, `spa:navigation`, `skipped`, `transport:beacon`, `transport:fetch`, and `transport:error`. Eligibility and skip logs include a machine-readable reason. Transport logs include path/source, identifier presence booleans, beacon queue acceptance, or fetch HTTP status. They never include raw visitor/session IDs, cookies, tokens, authorization headers, raw IP addresses, or referrer contents.

After the request, verify the corresponding database event through the normal analytics administration/storage inspection flow. The stored row must contain visitor/session hashes rather than the browser UUID values.
