# Phase 2 Security Review

**Date:** 2026-06-05  
**Scope:** Phase 2 epics (lesson delete, reorder, progress, profiles, forum admin)

## Checklist (skills/security-checking.md)

| Item | Status | Notes |
|------|--------|-------|
| CSRF on forms | Pass | All new POST/PUT/DELETE use `@csrf` or JSON with `X-CSRF-TOKEN` |
| Mass assignment | Pass | Profile fields validated explicitly; no new unguarded models |
| File upload (avatar) | Pass | `image`, mime whitelist, 2MB max; stored on `public` disk |
| Authorization | Pass | Admin routes behind `role:admin`; progress behind `enrolled.course` |
| Blade escaping | Pass | User content via `{{ }}`; markdown unchanged |
| Rate limiting progress | Low risk | Optional throttle deferred; enrolled-only endpoint |

## Findings

### Medium — None

### Low

1. **Progress endpoint** — No throttle middleware. Acceptable for MVP; add `throttle:60,1` if abuse seen.
2. **Avatar URLs** — Public disk; intentional for profile display.
3. **Reorder JSON endpoints** — Admin-only; validate ID sets match course (implemented).

## Dead code removed

- `StudyEvent` model and `StudyEventLogger` deleted (no migration, no callers).

## Sign-off

Phase 2 security review complete. No critical or high issues blocking release.
