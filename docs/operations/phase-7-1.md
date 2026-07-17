# Phase 7.1 operations

## CSV export

`GET /api/modules/glitter-analytics/admin/visits/export` requires Sanctum authentication, an administrator role, and `glitter-analytics.visits.view`. It accepts the same `from`, `to`, `path`, `source`, and `member` filters and the same 90-day maximum as the visits list.

Results are cursor-streamed as UTF-8 CSV with a BOM, CRLF records, and standard quoting. Cells beginning with `=`, `+`, `-`, `@`, tab, or carriage return are prefixed with an apostrophe to prevent spreadsheet formula execution. The filename contains only a fixed prefix and numeric site-timezone timestamp. Fields are occurrence time, pathname, source, member boolean, visitor/session presence, and referrer domain. Raw IP, hashes, user agent, cookies, tokens, authorization, and referrer paths/queries are excluded.

For browser verification, apply filters in **Site Analytics → Visits**, download the CSV, and confirm the row set matches the list. Check the first bytes are `EF BB BF`, Korean text opens correctly in Excel, and commas, quotes, and line breaks remain within one quoted cell. Confirm no query/hash fragment or sensitive field appears.

## Realtime initial-install policy

G7 initial installations intentionally use `broadcasting.default = null`. In that state analytics returns before dashboard aggregation, `/api/broadcasting/auth` is unavailable, and pageview collection remains HTTP 202. Phase 7.1 does not enable broadcasting.

Before a future activation, provision one existing G7 Reverb application and set the verified values for `BROADCAST_CONNECTION=reverb`, `REVERB_APP_ID`, `REVERB_APP_KEY`, `REVERB_APP_SECRET`, and server/client host, port, and scheme. Do not copy another application's credentials. Restrict `allowed_origins` to the exact GlitterView origin before exposure; the inspected wildcard default is an activation blocker. Verify WebSocket upgrade at the reverse proxy, then run the standard project-supervised `reverb:start` process and restart only affected config/PHP and worker processes.

After activation, Chrome must show a WebSocket, private-channel subscription, and `dashboard.updated` after a successful pageview. A permitted analytics administrator succeeds; anonymous, ordinary, and differently-permitted users receive 401/403. Leaving/re-entering and reconnecting must not duplicate listeners. Rollback restores the null broadcast connection, clears configuration through the deployment lifecycle, and stops only this project's Reverb process.

When enabled with the inspected synchronous queue, every pageview performs dashboard summary, seven-day trend, popular-path, referrer, and recent-visit queries before broadcast. This is low-traffic behavior. A supported queue worker plus measured coalescing should precede high-volume activation; Phase 7.1 adds no Redis, counter cache, or distributed lock.

## Isolated PHPUnit database

The test bootstrap requires `.env.testing` and rejects a production database name. The inspected runtime database account has privileges only on the production schema, so no database or `.env.testing` was created.

A DBA can create an empty `g7_testing` schema and a dedicated account granted only `SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, INDEX, ALTER, REFERENCES` on `g7_testing`.*. Build `.env.testing` from `.env.testing.example` with a new testing `APP_KEY`, test-only credentials, `APP_ENV=testing`, array cache/session/mail drivers, synchronous test queue, and null broadcasting. Before migration, verify every configured read/write database name differs from production. Cleanup drops only the explicitly verified `g7_testing` schema and its dedicated account.
