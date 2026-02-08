# AGENTS.md

This file defines how coding agents should work in this repository.

## Project Summary

RapidPress is a WordPress performance plugin (PHP) with no frontend build step in this repo.  
Main entry point: `rapidpress.php`  
Primary namespace: `RapidPress`

Compatibility targets from plugin headers/readme:
- WordPress: 5.0+
- PHP: 7.2+

## Repository Map

- `rapidpress.php`: plugin bootstrap, constants, lifecycle hooks.
- `inc/`: core optimization and cache logic (`class-*.php`).
- `admin/`: admin settings UI and admin-only behavior.
- `public/`: public runtime hooks and assets.
- `languages/`: translation template(s).
- `uninstall.php`: uninstall cleanup.
- `vendor/`: Composer autoload artifacts already present.

## Code Conventions

- Keep new code PHP 7.2-compatible (no newer syntax like union types, attributes, constructor property promotion, etc.).
- Follow existing naming scheme: files `class-<feature>.php`; classes `Pascal_Case` with underscores (example: `Cache_Preloader`); namespace `RapidPress`.
- Add direct-access guards in loadable PHP files: `if (!defined('ABSPATH')) { exit; }`.
- Register WordPress hooks through existing architecture where possible; bootstrap wiring belongs in `inc/class-rapidpress.php`.
- Avoid introducing ad-hoc global hooks when class-based hooks are already used.
- Use WordPress sanitization/escaping/capability checks consistently: sanitize input, verify nonces, check capabilities, and escape output.
- Prefer plugin constants for paths/URLs (`RAPIDPRESS_PATH`, `RAPIDPRESS_PLUGIN_URL`, etc.) over hardcoded paths.
- Keep changes focused; avoid refactoring unrelated files in the same task.

## Behavior Expectations

- Preserve backward compatibility for existing option keys and hooks unless explicitly asked to introduce a breaking change.
- When changing cache features, verify activation/deactivation interactions in `Cache_Dropin_Manager` and `Cache_Preloader`.
- Keep admin UX defensive: feature toggles should fail safe (disabled) if prerequisites are missing.

## Validation Checklist

Run what is relevant to your change:

1. PHP lint on touched files (or all PHP files for broad changes):
```bash
find . -name "*.php" -print0 | xargs -0 -n1 php -l
```
2. If settings/options were changed, verify save/load in WP Admin (`RapidPress` settings screen).
3. If front-end optimization changed, smoke test a public page for:
- no fatal errors in debug log
- expected HTML/CSS/JS output behavior
- no obvious layout or script regressions
4. If cache behavior changed, validate cache clear/preload flows and plugin activate/deactivate paths.

## Out-of-Scope Defaults

- Do not add new dependencies unless required for the task.
- Do not change plugin metadata/version unless the task explicitly asks for release/version updates.
- Do not commit secrets, environment-specific URLs, or machine-specific paths.
