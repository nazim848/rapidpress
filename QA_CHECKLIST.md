# RapidPress Cache QA Checklist

## Scope
Validation checklist for cache roadmap phases:
- Foundation refactor
- Early cache serving
- Targeted invalidation
- Preload engine
- Variants and bypass policies
- Tooling/CLI/headers
- CSS cache lifecycle

## Test Environment
- Date:
- Tester:
- WP Version:
- PHP Version:
- Theme:
- Active plugins:
- Object cache enabled (yes/no):
- CDN/proxy layer (none/Cloudflare/Varnish/other):

## Legend
- Status: `PASS` / `FAIL` / `BLOCKED`
- Evidence: screenshot, response header, CLI output, or notes

---

## 1) Baseline Enable/Disable

| ID | Test | Steps | Expected | Status | Evidence |
|---|---|---|---|---|---|
| 1.1 | Cache enabled produces MISS then HIT | Enable `Enable Cache`; open same public page twice | First load `X-RapidPress-Cache: MISS`, second `X-RapidPress-Cache: HIT` |  |  |
| 1.2 | Cache disabled bypasses | Disable `Enable Cache`; open public page | `X-RapidPress-Cache: BYPASS` and reason contains `cache_disabled` |  |  |

---

## 2) Bypass Rules

| ID | Test | Steps | Expected | Status | Evidence |
|---|---|---|---|---|---|
| 2.1 | Logged-in bypass default | Login as admin; open frontend page | `BYPASS` with reason `logged_in` |  |  |
| 2.2 | URL bypass pattern works | Add `/checkout` in `Never Cache URLs`; open matching URL | `BYPASS` with reason `url_rule` |  |  |
| 2.3 | UA bypass pattern works | Add `Lighthouse` in `Never Cache User Agents`; request with matching UA | `BYPASS` with reason `user_agent_rule` |  |  |

---

## 3) Query String Policy

| ID | Test | Steps | Expected | Status | Evidence |
|---|---|---|---|---|---|
| 3.1 | Query bypass mode | Set `Query String Policy = Bypass`; request `/page/?x=1` | `BYPASS` reason `query_string` |  |  |
| 3.2 | Query ignore mode | Set `Query String Policy = Ignore`; request `/page/?x=1` twice | First request MISS, second HIT |  |  |

---

## 4) Mobile Variant

| ID | Test | Steps | Expected | Status | Evidence |
|---|---|---|---|---|---|
| 4.1 | Separate mobile/desktop cache | Enable `Mobile Cache Variant`; request same URL with desktop UA then mobile UA | Both contexts get independent MISS->HIT cycles |  |  |

---

## 5) Targeted Invalidation

| ID | Test | Steps | Expected | Status | Evidence |
|---|---|---|---|---|---|
| 5.1 | Post update purges related URLs | Warm cache for home, post A, unrelated page B; update post A | Home + post A invalidated; page B remains HIT |  |  |
| 5.2 | Comment update purges related URLs | Warm post page; add comment | Post page invalidated then recached |  |  |
| 5.3 | Manual full purge | Click `Purge All Page Cache` | Cached HTML files removed; next request is MISS |  |  |

---

## 6) Preloader

| ID | Test | Steps | Expected | Status | Evidence |
|---|---|---|---|---|---|
| 6.1 | Manual preload runs | Click `Preload Cache Now` | Success message with URL count |  |  |
| 6.2 | Preload metadata updates | After manual run, check cache tab | `Last preload` timestamp/count updated |  |  |
| 6.3 | Scheduler created | Enable `Enable Preload`; save | Cron event `rapidpress_cache_preload_event` exists |  |  |
| 6.4 | Scheduler removed | Disable `Enable Preload`; save | Cron event removed |  |  |

---

## 7) Early Cache Serving (`advanced-cache.php`)

| ID | Test | Steps | Expected | Status | Evidence |
|---|---|---|---|---|---|
| 7.1 | Drop-in generated | Enable `Enable Cache` + `Early Cache Serving`; save | `wp-content/advanced-cache.php` exists and is RapidPress-managed |  |  |
| 7.2 | Early header on hit | Warm page then reload | `X-RapidPress-Cache: HIT-EARLY` on cached response |  |  |
| 7.3 | Deactivation cleanup | Deactivate plugin | Managed drop-in removed |  |  |
| 7.4 | Unmanaged drop-in safety | Put non-RapidPress `advanced-cache.php`; toggle setting | File is not overwritten/removed by RapidPress |  |  |

---

## 8) CSS Cache Lifecycle

| ID | Test | Steps | Expected | Status | Evidence |
|---|---|---|---|---|---|
| 8.1 | CSS cache clear action | Click `Clear CSS Cache` | Combined CSS files removed and metadata reset |  |  |
| 8.2 | Expiry respected | Let metadata expire or simulate expiry | Combined CSS regenerated on next request |  |  |
| 8.3 | Missing file handling | Delete combined file but keep meta | Validator treats cache invalid and regenerates |  |  |

---

## 9) WP-CLI Tooling

| ID | Test | Command | Expected | Status | Evidence |
|---|---|---|---|---|---|
| 9.1 | Cache stats | `wp rapidpress cache stats` | Shows directory, file count, and size |  |  |
| 9.2 | Purge all | `wp rapidpress cache purge` | All page cache files purged |  |  |
| 9.3 | Purge URL | `wp rapidpress cache purge-url <url>` | Target URL cache removed |  |  |
| 9.4 | Preload | `wp rapidpress cache preload` | Reports preloaded URL count |  |  |

---

## 10) Regression Smoke

| ID | Test | Steps | Expected | Status | Evidence |
|---|---|---|---|---|---|
| 10.1 | Settings save works | Save non-cache settings tabs | No fatal errors; settings persist |  |  |
| 10.2 | Frontend rendering | Browse key pages | No broken layout/scripts due to cache changes |  |  |
| 10.3 | Admin stability | Visit RapidPress settings and WP admin pages | No warnings/fatals |  |  |

---

## Defects Found

| ID | Severity | Summary | Repro Steps | Expected | Actual | Status |
|---|---|---|---|---|---|---|
|  |  |  |  |  |  |  |

---

## Sign-off
- QA Result: `PASS` / `FAIL` / `CONDITIONAL`
- Signed by:
- Date:
- Notes:
