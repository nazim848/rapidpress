# RapidPress Cache Implementation Playbook

## Purpose
This document is the execution plan to upgrade RapidPress caching from the current basic file cache to a stronger, production-ready system.  
It is written so implementation can continue later without losing context.

## Current Baseline (as of this plan)
- Page cache is implemented in `inc/class-page-cache.php`.
- Cache is enabled by `rapidpress_options[enable_cache]` from `admin/partials/tabs/cache.php`.
- Cache serve/write runs during `template_redirect` (not early bootstrap).
- Purge behavior is currently full-cache purge on multiple content events.
- CSS combiner has its own cache/meta path in `inc/class-css-combiner.php`.

## Execution Rules
- Complete phases in order unless explicitly marked parallel-safe.
- Keep changes backward-compatible by default.
- Add tests with each phase (or explicitly note temporary test gap in PR notes).
- Ship behind feature flags where noted.

## Milestones
- M1: Foundation + refactor safety
- M2: Early cache serving
- M3: Smart invalidation
- M4: Preload engine
- M5: Variants + bypass policy
- M6: Tooling + integrations
- M7: CSS cache lifecycle alignment

---

## Phase 1 - Foundation Refactor (Safe)

### Goal
Introduce clean abstractions for cache config/key/store without changing existing behavior.

### Tasks
1. Create cache config class.
- Add file: `inc/class-cache-config.php`
- Responsibilities:
  - Read cache options from `RP_Options`.
  - Provide typed getters: enabled, ttl, query policy, mobile variant, logged-in policy.
  - Apply defaults identical to current behavior.

2. Create cache key builder class.
- Add file: `inc/class-cache-key.php`
- Responsibilities:
  - Build normalized key using scheme + host + path.
  - Support optional query policy mode (initially default = ignore query strings like current logic).
  - Preserve filter compatibility with existing `rapidpress_cache_key`.

3. Create cache store class (file-based).
- Add file: `inc/class-cache-store.php`
- Responsibilities:
  - Resolve cache directory.
  - Read/write/delete file by key.
  - Calculate entry freshness by mtime + ttl.
  - Keep existing `rapidpress_cache_path` and `rapidpress_cache_ttl` filters working.

4. Wire dependencies in loader.
- Update `inc/class-rapidpress.php`:
  - `require_once` new classes.
  - Instantiate `Page_Cache` with optional dependencies or keep internal fallback.

5. Refactor page cache to use new classes.
- Update `inc/class-page-cache.php`.
- Keep hooks and headers unchanged (`X-RapidPress-Cache: HIT/MISS`).
- Keep default skip rules unchanged.

### Acceptance Criteria
- No behavioral change for existing users.
- Cache HIT/MISS still works exactly as before.
- Existing cache-related filters still work.

### Test Checklist
- Anonymous GET page returns MISS first request, HIT second request.
- Logged-in request bypasses cache.
- URL with query string bypasses cache.
- Editing a post still purges cache (current broad behavior retained in this phase).

---

## Phase 2 - Early Serving (Biggest Performance Gain)

### Goal
Serve cache before full WP runtime where possible.

### Tasks
1. Build drop-in manager.
- Add file: `inc/class-cache-dropin-manager.php`
- Responsibilities:
  - Install/update/remove `wp-content/advanced-cache.php`.
  - Keep generated file content versioned and idempotent.
  - Validate writable path and fail gracefully.

2. Add generated early-serving logic.
- Generated drop-in should:
  - Exit early for non-GET/HEAD, logged-in cookies, admin-like paths, query disallowed cases.
  - Resolve request cache key and file path.
  - Serve file if fresh; emit `X-RapidPress-Cache: HIT-EARLY`.
  - Fallback to normal WP bootstrap otherwise.

3. Add plugin lifecycle hooks for drop-in management.
- Update `rapidpress.php`:
  - On activation: install or validate drop-in.
  - On deactivation/uninstall: remove or disable drop-in safely.

4. Add admin setting for early cache mode.
- Update `admin/partials/tabs/cache.php`.
- Add option key: `early_cache_serving` (default off for safe rollout).
- Update sanitization in `admin/class-admin.php`.

5. Add server rewrite docs.
- Update `README.txt` with optional Apache/Nginx rules for direct static serving.

### Acceptance Criteria
- With early mode on, HIT responses can be served before plugin runtime.
- With early mode off, behavior matches previous mode.
- Drop-in updates are safe and reversible.

### Test Checklist
- Verify drop-in generated file exists and contains expected signature.
- Verify early HIT header appears on repeated anonymous request.
- Verify logged-in cookie bypasses early hit.
- Verify disabling feature removes/neutralizes drop-in behavior.

---

## Phase 3 - Smart Invalidation

### Goal
Replace broad purge with targeted URL purging to improve sustained hit rate.

### Tasks
1. Add invalidation planner service.
- Add file: `inc/class-cache-invalidation.php`
- Responsibilities:
  - Map events to affected URLs:
    - Post permalink
    - Home page
    - Post type archive
    - Taxonomy archives
    - Author/date archives where relevant
    - Feeds (if cacheable)

2. Update event handlers in page cache.
- Modify `inc/class-page-cache.php`:
  - Event hooks call invalidation planner.
  - Purge specific keys/files for resolved URLs.
  - Keep `purge_all()` fallback method.

3. Add debounce queue for burst updates.
- Add option/transient backed queue (or cron event) to collapse repetitive purges.
- Add file: `inc/class-cache-purge-queue.php` (if needed).

4. Add manual full purge action.
- Update `admin/partials/tabs/cache.php` with “Purge All Cache” button.
- Add secure AJAX endpoint in `admin/class-admin.php`.

### Acceptance Criteria
- Content updates purge only related pages.
- Full purge still available manually.
- Cache hit ratio remains stable after frequent edits.

### Test Checklist
- Edit one post -> that post + home + related archive purged.
- Unrelated cached pages stay intact.
- Manual full purge removes all cached entries.

---

## Phase 4 - Preload / Warmup

### Goal
Warm cache automatically after purge or on schedule.

### Tasks
1. Implement preloader.
- Add file: `inc/class-cache-preloader.php`
- Responsibilities:
  - Build URL queue from sitemap + recently changed URLs.
  - Throttle requests and handle retries.
  - Respect timeout/batch settings.

2. Add scheduler integration.
- Register WP-Cron events for preload jobs.
- Support manual “Preload Now”.

3. Add settings UI.
- Update `admin/partials/tabs/cache.php`:
  - Enable preload
  - Batch size
  - Interval
  - Source (sitemap/changed URLs/both)
- Update sanitizers in `admin/class-admin.php`.

4. Add status visibility.
- Show last preload time, queued URLs count, success/failure counts.

### Acceptance Criteria
- After purge, preload repopulates key pages.
- Preload does not overload server (throttled).

### Test Checklist
- Trigger preload manually and verify generated cache files.
- Confirm repeated runs do not duplicate queue endlessly.
- Confirm preload obeys limits and timeout.

---

## Phase 5 - Variants and Bypass Policy

### Goal
Support safe cache variations and configurable bypass logic.

### Tasks
1. Add variant-capable keying.
- Update `inc/class-cache-key.php`.
- Add optional key segments:
  - device class (desktop/mobile)
  - language key (if configured)
  - custom cookie segment allowlist

2. Add query-string policy controls.
- Modes:
  - Ignore all query strings (default, current-like)
  - Allowlist query keys
  - Bypass if unknown query exists
- Wire through cache eligibility logic.

3. Add bypass rules in settings.
- URL patterns never cache.
- User-agent patterns never cache.
- Optional cookie bypass list.

4. Maintain developer hooks.
- Keep existing filters and add documented new ones for:
  - variant resolution
  - bypass decisions
  - query allowlist

### Acceptance Criteria
- Variants prevent cross-context cache pollution.
- Query policy is deterministic and testable.

### Test Checklist
- Mobile and desktop can have separate cached entries when enabled.
- Allowed query keys map to stable cache files.
- Disallowed query keys bypass cache.

---

## Phase 6 - Tooling, Metrics, and Integrations

### Goal
Improve operability and edge-cache interoperability.

### Tasks
1. Add WP-CLI commands.
- Add file: `inc/class-cache-cli.php`.
- Commands:
  - `wp rapidpress cache purge`
  - `wp rapidpress cache purge-url <url>`
  - `wp rapidpress cache preload`
  - `wp rapidpress cache stats`

2. Add cache statistics service.
- Add file: `inc/class-cache-stats.php`.
- Metrics:
  - file count
  - disk usage
  - last purge time
  - last preload time
  - rolling hit/miss counters

3. Improve debug headers.
- Expand headers:
  - `X-RapidPress-Cache` (HIT/MISS/BYPASS)
  - `X-RapidPress-Cache-Reason`
  - `X-RapidPress-Cache-Key` (optional debug mode only)

4. Add edge purge integration hooks.
- Add abstraction for provider purges (Cloudflare/Varnish).
- Initially ship hooks + generic HTTP purge adapter.

### Acceptance Criteria
- Admin can observe and operate cache confidently.
- CLI allows automation.
- Edge purge can be integrated without forking plugin code.

### Test Checklist
- Run each CLI command and verify expected behavior.
- Stats values update after hits/misses/purges.
- Debug headers are accurate and optional.

---

## Phase 7 - CSS Cache Lifecycle Alignment

### Goal
Make CSS combined cache lifecycle consistent and explicit.

### Tasks
1. Enforce expiration for CSS cache metadata.
- Update `inc/class-css-combiner.php`:
  - `is_cached_file_valid()` must consider `expires`.
  - Rebuild when expired even if hash matches.

2. Add consistent cleanup policy.
- Keep only `N` latest combined files and remove stale orphan files.
- Ensure metadata references existing file.

3. Expose CSS cache controls.
- Add optional “Clear CSS cache” action in cache/tools UI.

### Acceptance Criteria
- CSS cache never persists beyond configured lifetime unintentionally.
- CSS metadata and filesystem stay in sync.

### Test Checklist
- Expired CSS cache triggers regeneration.
- Cleanup removes old files and keeps newest set.

---

## Release Plan

### Release A (low risk)
- Phase 1 + Phase 3 + partial Phase 6 (manual purge + basic stats).

### Release B (performance)
- Phase 2 + Phase 4 + remaining Phase 6.

### Release C (advanced behavior)
- Phase 5 + Phase 7.

---

## Branch / PR Workflow

### Branch Sequencing Rule (Required)
- Branches must be created sequentially and stacked.
- Do not create all phase branches from `main`.
- Create next phase branch only after finishing and committing the previous phase.
- Required flow:
  1. Create `codex/cache-phase-1-foundation` from current base.
  2. Complete phase 1 work and commit.
  3. Create `codex/cache-phase-2-early-serving` from `codex/cache-phase-1-foundation` HEAD.
  4. Complete phase 2 work and commit.
  5. Repeat this pattern for all remaining phases.

### Branch Naming
- `codex/cache-phase-1-foundation`
- `codex/cache-phase-2-early-serving`
- `codex/cache-phase-3-invalidation`
- etc.

### Per-PR Template
1. Scope
2. Files changed
3. Backward compatibility notes
4. Manual test results
5. Risks + rollback

---

## Definition of Done (Global)
- New features are toggleable and backward-compatible.
- Cache behavior is deterministic under documented policies.
- Admin has purge and visibility controls.
- CLI automation is available.
- No fatal errors when filesystem/drop-in not writable.
- Docs are updated for every shipped feature.

---

## Immediate Next Actions
1. Start Phase 1 Task 1 and Task 2 (`class-cache-config.php`, `class-cache-key.php`).
2. Refactor `class-page-cache.php` with no behavior changes.
3. Add/execute baseline cache behavior tests before Phase 2.
