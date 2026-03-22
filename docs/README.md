# Technical LMS — documentation

This folder describes **architecture**, **data**, **NFRs**, and **QA** for the Laravel monolith (SPS v1.1 + agreed product decisions). Functional scope remains authoritative in the Software Product Specification and the implementation in the repo.

**Consistency note:** The **database and domain model** did not change when we split the UI. What did change is **presentation**:

| Layer | Implementation (code) | Described in |
|-------|------------------------|--------------|
| **Learner experience** | `resources/views/layouts/learn.blade.php` — catalog, lessons, forums, auth cards, dashboard | [QA_FEATURE_CHECKLIST.md](QA_FEATURE_CHECKLIST.md) (manual tests), UX bullets in [non-functional-requirements.md](non-functional-requirements.md) |
| **Admin experience** | `resources/views/layouts/admin.blade.php` — sidebar CMS under `/admin` | Same as above + [component-diagram.md](component-diagram.md) (presentation split) |

Architecture diagrams (components, sequences, ERD, domain classes) describe **behavior and data**, not every Blade file name.

---

## Index

| Document | Purpose |
|----------|---------|
| [non-functional-requirements.md](non-functional-requirements.md) | NFRs: security, performance, reliability, UX, constraints |
| [data-model-erd.md](data-model-erd.md) | Entity-relationship model (tables, keys, cardinalities) |
| [domain-class-diagram.md](domain-class-diagram.md) | Domain classes, services, relationships (logical) |
| [component-diagram.md](component-diagram.md) | Logical components, dependencies, learner vs admin presentation |
| [sequence-critical-flows.md](sequence-critical-flows.md) | Sequences: mark complete, quiz, mentions |
| [QA_FEATURE_CHECKLIST.md](QA_FEATURE_CHECKLIST.md) | Manual QA / external testing (aligned with current UI) |

**Diagrams** use [Mermaid](https://mermaid.js.org/) where applicable.

---

## Lesson progress (learner UX)

Completion is **manual** (Udemy-style **Mark as complete** checkbox). The app does **not** track video playback seconds via periodic checkpoints.

---

## YouTube embed issues (“Sign in to confirm you’re not a bot”)

That message comes from **YouTube**, not Laravel. It appears more often on **localhost**, **VPNs**, **datacenter IPs**, and sometimes with the **privacy-enhanced** embed host (`youtube-nocookie.com`).

**Mitigations in this repo:** lesson pages include **Open video on YouTube**; embeds use `origin` (from `APP_URL`), `referrerpolicy`, and player params. Set **`YOUTUBE_EMBED_USE_NOCOOKIE=false`** in `.env` to use `youtube.com/embed` instead of `youtube-nocookie.com`. Use a **real HTTPS domain** (staging/production) for fewer issues. For full control, use the **R2** video driver with self-hosted MP4s (`DEFAULT_VIDEO_DRIVER` / admin lesson settings).

---

## Demo data (`migrate:fresh --seed`)

`database/seeders/DatabaseSeeder.php` creates demo users (see [QA_FEATURE_CHECKLIST.md](QA_FEATURE_CHECKLIST.md)), **Laravel Foundations**, and **`HtmlCourseSeeder`** — **Learn HTML — Step by Step** (twelve lessons aligned to [this YouTube playlist](https://www.youtube.com/playlist?list=PLGwzwzSIMCmc_aR4lHJcxpNFoVuuc3UHs), plus W3Schools readings and selective quizzes). The demo student is enrolled in both sample courses. Re-running `db:seed` without a fresh migrate skips everything when `admin@example.com` already exists.
