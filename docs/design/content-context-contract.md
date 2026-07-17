# Analytics content context contract (proposal)

Status: design only. Phase 7.1 does not change the event schema, tracker, or Board/Page/Shop modules.

## Decision

Content modules should provide an optional, server-trusted content context while G7 resolves the public route/layout. The resolved context is emitted in the existing public layout bootstrap payload and read by the analytics tracker. Arbitrary DOM metadata, client-provided titles, and pathname parsing are not authoritative sources.

The provider is extension-aware: when a module is absent, inactive, or has no provider, the context is `null` and the existing pageview contract is unchanged. A future implementation should use the established G7 hook/filter and layout payload extension points rather than a new global browser object.

## Proposed version 1 payload

```json
{
  "version": 1,
  "content_type": "board.post",
  "module_identifier": "sirsoft-board",
  "content_key": "public-stable-key",
  "container_type": "board",
  "container_key": "public-board-key"
}
```

Required fields when a context exists are `version`, `content_type`, `module_identifier`, and `content_key`. Container fields are optional and must be supplied together. Initial type vocabulary is `board.post`, `page`, and `shop.product`; new types require a documented, namespaced value.

Keys must be stable public identifiers approved by the providing module. Internal primary keys may be used only when that module already treats them as public. Titles, email addresses, account identifiers, private slugs, search terms, arbitrary request parameters, and other user-authored text are forbidden.

## Data flow

1. The active content module recognizes its own resolved route/model and returns a typed context through the standard extension hook/provider.
2. Core validates the identifier vocabulary, maximum lengths, publication/access state, and provider ownership.
3. The public layout response exposes the validated optional context.
4. The tracker copies it into an optional `content` member of a pageview request.
5. The public API validates it again and stores only approved identifiers.
6. Analytics queries group stored identifiers; they never infer content from a pathname.

Board would provide `board.post` plus a public board container key; Page would provide `page`; Shop would provide `shop.product`. Unsupported custom modules continue to emit ordinary pageviews. Deactivation or uninstall prevents new contexts but historical string identifiers remain reportable without loading the source module.

## Privacy and security

- The server is authoritative; a client value is never accepted merely because it appears in DOM or a URL.
- Private or unauthorized content must return `null` unless an explicit product policy permits aggregate tracking.
- Titles are not stored by default. Display names are resolved at read time only when the source module is active and permission checks pass.
- Values are length-limited enums/identifiers and are returned as text, never executable markup.
- Existing visitor, session, and IP hashing is unchanged and no new cross-module user profile is created.

## Compatibility and storage

Version 1 is an optional additive request member. Existing 0.7.x clients and events remain valid with `content = null`; unsupported modules keep the current pageview behavior. A future migration may add nullable `content_type`, `module_identifier`, `content_key`, `container_type`, and `container_key` columns. Indexes should be chosen from measured group-by queries rather than added pre-emptively.

The public pageview endpoint remains the same. Unknown content-contract versions should be ignored or rejected only at the optional member boundary, without changing the existing pageview success contract.

## Test strategy before implementation

- Provider contract tests for Board, Page, Shop, inactive modules, private content, and deleted content.
- public layout payload tests proving only server-resolved fields are emitted.
- Request tests for optional compatibility, enum/length limits, spoofed module identifiers, and forbidden fields.
- Tracker tests for initial/SPA context changes without query/hash inference.
- Repository and migration tests for null legacy rows and historical data after uninstall.
- Admin resource tests proving identifiers remain text and no title or private field leaks.

## Open decisions

- The exact existing hook name and typed DTO ownership must be selected with the core extension maintainers.
- Each module must document whether its public key may expose an internal numeric ID.
- The policy for authenticated access to private content needs a product decision.
- Retention, cardinality limits, and indexes require production-volume measurements.
- Read-time label resolution and behavior for renamed/deleted content remain Phase 7+ decisions.
