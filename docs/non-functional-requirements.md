# Non-functional requirements (NFR)

**Product:** Technical Learning Management System (Laravel monolith)  
**Stack:** Laravel 13+, MySQL, Blade SSR, Tailwind CSS, Alpine.js  
**Version:** 1.0 (aligned with implementation v1)

Functional requirements are defined in the Software Product Specification and the product plan. This document states **quality attributes** and **constraints**.

---

## 1. Security

| ID | Requirement |
|----|-------------|
| SEC-01 | **Authentication:** Use Laravel’s session-based auth (or Fortify/Breeze pattern). Passwords hashed with bcrypt/argon2 per framework defaults. |
| SEC-02 | **Authorization:** Enforce role-based access (admin, instructor, student) via policies/gates and route middleware. Admin-only routes must not be reachable by other roles. |
| SEC-03 | **CSRF:** All state-changing form submissions and AJAX POSTs use CSRF tokens (`VerifyCsrfToken`). |
| SEC-04 | **XSS — Markdown:** Lesson documentation is rendered from Markdown **server-side**; sanitize or use a safe CommonMark configuration (no raw HTML injection from untrusted input). Code blocks escaped/highlighted safely. |
| SEC-05 | **XSS — UGC:** Forum posts and lesson comments are user-generated; **escape on output** in Blade (`{{ }}`) or equivalent; optionally sanitize rich text if introduced later. |
| SEC-06 | **Session:** Secure cookie flags in production (`SESSION_SECURE_COOKIE`, `httpOnly`, `same_site` per Laravel config). |
| SEC-07 | **Video:** v1 uses **YouTube embed only**; do not expose permanent first-party MP4 URLs. Future R2: **private bucket**, **short-lived signed URLs** only. |
| SEC-08 | **Rate limiting:** Forum posting limited to **5 posts per user per day** (forum posts only; not lesson comments). Apply Laravel `RateLimiter` or equivalent at application layer with clear HTTP 429 responses. |
| SEC-09 | **File uploads (avatars):** Validate MIME type and size; store outside web root or via disk that does not execute PHP; use random filenames. |

---

## 2. Performance and scalability

| ID | Requirement |
|----|-------------|
| PERF-01 | **SSR:** Primary lesson and catalog pages are server-rendered; target **TTFB** acceptable on modest hardware (no strict SLA in v1; optimize N+1 queries on hot paths). |
| PERF-02 | **Checkpoints:** Client POSTs playback position every **30s**; endpoint must respond quickly (under **~200 ms** typical server time under normal load) to avoid UI lag. |
| PERF-03 | **Database:** Index foreign keys and common filters (`user_id`, `lesson_id`, `course_id`, `created_at` on forums/comments). |
| PERF-04 | **Pagination:** Lists (forum threads, posts, notifications, comments) use cursor or offset pagination; avoid unbounded loads. |
| PERF-05 | **Caching (optional v1):** Course tree and static config may use Laravel cache; invalidate on admin content change. |

---

## 3. Reliability and data

| ID | Requirement |
|----|-------------|
| REL-01 | **Transactions:** Quiz submission and grading, enrollment, and multi-row updates use DB transactions where appropriate. |
| REL-02 | **Backups:** Production MySQL backups are an **operational** responsibility (frequency and retention outside app code; document in runbooks). |
| REL-03 | **Migrations:** Schema changes are versioned via Laravel migrations; no manual prod edits without migration. |

---

## 4. Usability and accessibility

| ID | Requirement |
|----|-------------|
| UX-01 | **Documentation-heavy UI:** Readable typography; lesson page with clear **series / outline** navigation (W3Schools-inspired content per SPS; learner layout implements library, breadcrumbs, sticky outline). |
| UX-02 | **Video:** HTML5 / YouTube embed with keyboard-accessible controls where the provider allows. |
| UX-03 | **Errors:** User-facing validation errors on forms; generic message for unexpected failures (no stack traces to users in production). |
| UX-04 | **Learner vs admin UX:** Non-admin users must not rely on admin chrome. **Admins** use a **dedicated admin layout** (sidebar, `/admin` prefix) for CMS; **learners** use the **learner layout** for catalog, lessons, quizzes, forums, and profile — see [QA_FEATURE_CHECKLIST.md](QA_FEATURE_CHECKLIST.md). |

---

## 5. Maintainability

| ID | Requirement |
|----|-------------|
| MAINT-01 | **Video backends:** YouTube and future R2 are accessed through a **single `VideoDriver` (or equivalent) abstraction**; switching is **configuration + data**, not a rewrite. |
| MAINT-02 | **Tests:** Feature tests for authz, module gating, quiz grading, checkpoints, and forum rate limits (per plan). |
| MAINT-03 | **Code:** Follow PSR-12 / Laravel Pint; keep controllers thin where practical; domain logic in actions/services. |

---

## 6. Privacy and compliance

| ID | Requirement |
|----|-------------|
| PRIV-01 | **Profiles:** Public profile fields and visibility rules must be documented; avoid leaking email/phone unless explicitly shown. |
| PRIV-02 | **Notifications:** v1 is **in-app only**; no email requirement for mentions until enabled. |
| PRIV-03 | **Legal pages:** Terms of Service / Privacy Policy are **recommended** before public launch; content is product owner responsibility. |

---

## 7. Observability

| ID | Requirement |
|----|-------------|
| OBS-01 | **Logging:** Log authentication failures, repeated rate-limit hits, and server errors at appropriate levels. |
| OBS-02 | **Queues (optional):** If notification fan-out or email grows, use queues; v1 in-app notifications may remain synchronous until volume requires async. |
| OBS-03 | **Study monitoring (audit trail):** Record learner actions in feature-specific append-only tables (`lesson_activity_logs`, `quiz_activity_logs`, `forum_activity_logs`, `course_activity_logs`) with timestamps and rich metadata (score, duration, thread title/category, reply-to). Expose admin-only monitoring UI under `/admin/monitoring`. |

---

## 8. Out of scope for v1 (NFR)

- **DRM** for video (studio-grade protection).
- **Multi-region** deployment and active-active DB.
- **Formal SLA** or 24/7 on-call (unless you add later).

---

## Traceability

| Area | Primary implementation hooks |
|------|------------------------------|
| SEC-02, SEC-08 | Middleware, policies, `RateLimiter` |
| SEC-04, SEC-05 | Markdown renderer, Blade escaping |
| UX-01, UX-04 | `resources/views/layouts/learn.blade.php`, `resources/views/layouts/admin.blade.php` |
| PERF-02 | Checkpoint route + lean controller |
| MAINT-01 | `VideoDriver` contract, `video_driver` + `video_ref` on lessons |
