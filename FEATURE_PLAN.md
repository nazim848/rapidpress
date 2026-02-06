# RapidPress Feature Roadmap (Next 2 Releases)

## Summary
Plan two releases focused on measurable performance gains for site owners, with a mostly-free feature set and select pro enhancements. Emphasis on safe defaults, minimal configuration, and clear fallbacks.

## Release 1 (Core Performance Wins)

### Goals
- Improve Core Web Vitals with minimal user configuration.
- Add safe, low-risk optimizations that work with most themes/plugins.

### Planned Features
1. Critical CSS (Free)
   - Auto-detect above-the-fold CSS for the homepage and key templates.
   - Inline critical CSS; defer remaining CSS with a `link rel="preload"` + `onload` pattern.
   - Provide opt-out and exclusions.

2. Font Optimization (Free)
   - Preload detected font files.
   - Add `font-display: swap` for enqueued fonts (Google Fonts + local).
   - Optional local font hosting (Pro).

3. Smart Preload (Free)
   - Preload hero image and key CSS/JS.
   - UI to control count and exclusions.

4. Diagnostics Panel (Free)
   - "Performance Health" tab with:
     - Active optimizations
     - Detected render-blocking assets
     - Warnings for conflicts (e.g., duplicated minification)

### Pro Enhancements
- Automatic Critical CSS per URL with scheduled regeneration.
- Local Google Fonts hosting with automatic CSS rewrite.

## Release 2 (Caching & Media)

### Goals
- Reduce TTFB with smarter caching.
- Improve media delivery efficiency.

### Planned Features
1. Advanced Page Cache (Free)
   - Cache specific templates and common pages (home, blog, archive).
   - Granular rules: exclude logged-in, query args, cookies.
   - Purge rules for common content events.

2. Media Optimization (Free)
   - WebP/AVIF conversion for uploads.
   - Serve next-gen formats where supported.
   - Auto-generate responsive `srcset` improvements if missing.

3. CDN URL Rewriter (Free)
   - Replace static asset URLs with a configurable CDN domain.

### Pro Enhancements
- Edge cache integration for popular CDNs.
- Image compression pipeline with configurable quality profiles.

## Admin UX Improvements (Both Releases)
- Presets: "Safe", "Balanced", "Aggressive"
- Inline warnings for known compatibility issues
- One-click rollback of recent optimization changes

## Public API / Interface Changes
- New options in `rapidpress_options`:
  - `critical_css_enable`, `critical_css_exclusions`
  - `font_preload_enable`, `font_display_swap`
  - `smart_preload_count`, `smart_preload_exclusions`
  - `webp_enable`, `avif_enable`
  - `cdn_url`, `cdn_exclusions`
- New filters:
  - `rapidpress_critical_css`
  - `rapidpress_preload_assets`
  - `rapidpress_font_urls`
  - `rapidpress_cdn_rewrite`

## Test Cases and Scenarios
1. Critical CSS on default themes (Twenty Twenty-One, Twenty Twenty-Four).
2. Preload + defer CSS in common builders (Elementor, Beaver Builder).
3. Font optimization with Google Fonts and local fonts.
4. Page cache with:
   - logged-in sessions
   - WooCommerce carts/checkout pages excluded
   - query strings excluded
5. Media conversion in uploads, with fallback to original image.
6. CDN rewrite with mixed content prevention.

## Assumptions and Defaults
- WordPress.org compliance for free features.
- Pro features can require external services.
- Default configuration favors safety over aggressive optimization.
- Target users are site owners, so UI must remain simple and guided.
