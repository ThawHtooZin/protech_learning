# Product Requirements Document (PRD)

## Protech Learning System — Version 1

### 1. Project Description

#### Problems

- Technical learners often follow unstructured video playlists with no checkpoints, so progress is hard to track and easy to skip.
- Course creators need a simple way to publish sequential lessons with quizzes that unlock the next step.
- Small training programs lack an affordable LMS that combines video lessons, assessments, forums, and admin oversight without enterprise complexity.
- Admins need visibility into who is studying, what quizzes were taken, and where learners get stuck.
- Open registration without moderation can flood the platform with inactive or spam accounts.

#### Aims & Objectives

- Build a web-based Learning Management System (LMS) for structured, video-first technical courses.
- Let enrolled learners access any lesson in a course; track progress via **video watch** (Phase 3 — was quiz-gated in Phases 1–2).
- Support admin-assigned enrollment and admin approval before learners access the platform.
- Provide a reusable question bank and **optional** lesson/module quizzes for practice (not required to progress).
- Enable community discussion through forums and per-lesson comments with @mentions.
- Give admins a CMS for courses, users, and activity monitoring.
- Keep Version 1 focused: no mobile app, no certificates, no payments, no instructor portal.

### 2. Scope of the Project

#### Project Deliverables

##### Authentication & Access Control

- User registration, login, and logout.
- Roles: Admin, Student (Instructor enum reserved for later).
- Admin approval workflow: new students register but cannot use learner features until approved.
- Middleware gates: `auth`, `approved`, `role:admin`, `enrolled.course`.
- Admins bypass approval, enrollment, and lesson gating for preview and support.

##### User Profiles

- Public profile pages at `/u/{handle}`.
- Editable display name, handle, and bio.
- Password change for learners; admin can reset user passwords.

##### Course Catalog & Enrollment

- Public browse of published courses.
- Course detail page with module/lesson outline and progress (for enrolled users).
- Admin assigns courses to users from the user admin screen.
- Course completion percentage and answer accuracy on the course page.

##### Course Content Management (Admin)

- CRUD for courses (title, slug, description, published flag).
- Add/delete modules within a course (append by sort order).
- Create/edit lessons within modules.
- Lesson fields: title, video driver (`youtube` or `r2`), video reference, duration, markdown documentation.
- Optional module recap quizzes (create); lesson quizzes (full CRUD).

##### Video Lessons

- YouTube embed (default) with configurable nocookie domain.
- Cloudflare R2 / S3-compatible private video via signed URLs.
- Lesson page: video player, rendered markdown docs, course outline, comments, quiz link.

##### Learning & Progress (Phase 3)

- Lessons ordered by module `sort_order`, then lesson `sort_order` (display order only).
- **Any lesson** in a published course is open to enrolled learners (no quiz or order gate).
- Lesson **complete** = `lesson_progress.watched` (video watch threshold via progress endpoint).
- Course completion % = watched lessons ÷ total lessons.
- Learner quiz UI disabled by default (`LMS_QUIZZES_LEARNER_ENABLED=false`); admin quiz tools remain.

##### Assessment (optional module — Phase 3 direction)

> Quizzes are **secondary**. They do not gate the learning path after Phase 3.

- Central question bank: MCQ questions with technology/topic tags, body, and options.
- **Lesson quiz (optional):** Attachable per lesson; learners may take for practice; attempts and scores logged; **not required** to unlock next lesson.
- **Module recap quiz (optional):** Attachable per module; does not gate next module.
- Quiz attempt records: score, pass flag, per-answer correctness (for analytics and learner feedback).
- Quiz result page remains when a learner chooses to take a quiz.

##### Community

- Forum categories, tags, threads, and nested replies.
- Per-lesson threaded comments.
- @mention parsing with database notifications and deep links.
- Configurable daily forum post rate limit.

##### Notifications

- Database notification inbox for mentions.
- Mark single or all notifications as read.

##### Administration

- Admin dashboard with course and question counts.
- User management: list, approve, revoke, role change, course assignment, password reset, delete.
- Revoking approval clears user enrollments.
- Forum setup: create categories and tags.
- Activity monitoring: unified log plus filtered views for lessons, quizzes, forums, courses, and per-user drill-down.

##### Seeding & Demo Content

- Default seeder: admin user, profile, forum category, tags.
- Optional HTML course bootstrap seeder: 5 modules, 14 YouTube lessons, lesson quizzes.

#### Project Exclusions (Version 1)

- Instructor role permissions and UI (enum exists only).
- Self-service enrollment (controller stub exists; no route).
- Self-service forgot-password flow (admins reset passwords from the user admin screen).
- Email verification enforcement.
- Video playback position tracking during watch (fields exist; only set on quiz submit).
- Avatar upload and social links editing in profile UI.
- Question types beyond MCQ.
- Certificates, badges, gamification.
- Payments, subscriptions, or billing.
- Live classes, webinars, or scheduling.
- REST API or mobile app.
- Multi-tenant or multi-organization support.
- Content versioning or draft/publish workflow per lesson.
- Lesson/module delete and reorder UI (partial admin gaps).
- Module quiz edit/delete admin UI.
- Forum category/tag edit/delete admin UI.
- Real-time notifications (websockets); database only.

#### Project Constraints

- Laravel 13, PHP 8.3+, Blade, Tailwind CSS 4, Vite, Alpine.js.
- Web-only routes; no separate API layer.
- One database; session and queue use database driver by default.
- Sequential gating logic must stay deterministic and test-covered.
- Quiz grading and progress updates must run inside database transactions.
- AI development follows `.cursorrules`: one migration per table, separate seeders per model, epic-by-epic delivery.
- Source of truth for features and logic: this PRD and `docs/phase-1-technical-design-and-tasks.md`.

#### Assumptions

- Single organization runs one Protech LMS instance.
- Initial content targets technical courses (HTML course seeded as reference).
- YouTube is the default video host; R2/S3 for private video when configured.
- English UI for Version 1.
- Admins manually approve users and assign courses.
- Learners have modern browsers with JavaScript enabled.
- Offline mode is not required.

### 3. Requirements Specification

#### Functional Requirements

##### Authentication & Registration

- FR-A1: Guests may register with name, email, password, and profile handle.
- FR-A2: New registrations default to Student role and `approved_at = null`.
- FR-A3: Unapproved users may log in but are redirected to `/approval/pending` for all learner routes.
- FR-A4: Admins are auto-approved and bypass the approval gate.
- FR-A5: Users may log out; sessions are invalidated on logout.

##### User Administration

- FR-U1: Admins may list, view, approve, and revoke users.
- FR-U2: Revoking approval clears all enrollments for that user.
- FR-U3: Admins may change user role (admin, instructor, student).
- FR-U4: Admins may assign or remove course enrollments per user.
- FR-U5: Admins may reset a user's password.
- FR-U6: Admins may delete users except themselves.

##### Profiles

- FR-P1: Each user has one profile with handle, display name, and bio.
- FR-P2: Handles are unique and used as the public profile route key.
- FR-P3: Authenticated users may edit their profile and change password.

##### Course Catalog

- FR-C1: Published courses appear on the public catalog.
- FR-C2: Unpublished courses are hidden from learners.
- FR-C3: Enrolled learners see progress percentage and lesson outline on the course page.
- FR-C4: Non-enrolled learners see course info and a message to contact admin for enrollment.

##### Lesson Access & Gating

**Current code (Phases 1–2):**

- FR-L1: Enrolled learners may open the first lesson in course order.
- FR-L2 (legacy): Each subsequent lesson requires prior `lesson_progress.quiz_passed = true`.
- FR-L3 (legacy): Lesson without quiz cannot complete — **removed in Phase 3**.
- FR-L4: Admins may open any lesson without gating.
- FR-L5: Lesson routes require enrollment via `enrolled.course` middleware.

**Phase 3 (shipped Epic 1):**

- FR-L6: Enrolled learners may open **any** lesson in a published course without prior completion.
- FR-L7: Lesson completion uses `lesson_progress.watched`; lessons without quizzes are fully completable.

##### Quizzes (optional module — Phase 3)

**Current code (Phases 1–2):** FR-Q1–Q2 gate progression via quizzes.

**Phase 3 target:**

- FR-Q1: Lesson quizzes are **optional**; taking one does **not** unlock the next lesson.
- FR-Q2: Module recap quizzes are **optional**; do not gate modules.
- FR-Q3: Module recap score still uses `pass_threshold_percent` when learner chooses to take it.
- FR-Q4: Quiz attempts store score, pass flag, and per-question answers.
- FR-Q5: Destroying a lesson quiz does not block course progress (may clear optional attempt data only).

##### Question Bank

- FR-QB1: Admins may CRUD MCQ questions with technology, topic, body, and options.
- FR-QB2: Question bank supports search and filter by technology/topic.
- FR-QB3: Questions are reusable across multiple quizzes via pivot with sort order.

##### Forums & Comments

- FR-F1: Approved learners may browse forums, create threads, and post replies.
- FR-F2: Forum posts support one level of nesting via `parent_id`.
- FR-F3: Threads may have multiple tags.
- FR-F4: Daily post limit enforced per user from `config('lms.forum.max_posts_per_day')`.
- FR-F5: Lesson comments support threading and @mentions.
- FR-F6: Mentioned users receive database notifications with contextual links.

##### Activity Monitoring

- FR-M1: System logs lesson opened, lesson comment posted, quiz started, quiz submitted, forum thread/post created, and course completed events.
- FR-M2: Admins may view unified and filtered activity logs with date/user/course filters.
- FR-M3: Admins may view per-user activity summary.

##### Video

- FR-V1: Lessons use `video_driver` (`youtube` or `r2`) and `video_ref`.
- FR-V2: YouTube lessons render embed iframe; R2 lessons render signed HTML5 video URL.
- FR-V3: Default driver is configurable via `DEFAULT_VIDEO_DRIVER`.

#### Non-Functional Requirements

##### Performance

- NFR-P1: Course catalog and lesson pages should load in under 2 seconds on typical broadband.
- NFR-P2: Activity log queries should use indexes and pagination to avoid full-table scans.

##### Security

- NFR-S1: Passwords hashed with Laravel defaults.
- NFR-S2: All forms protected with CSRF tokens.
- NFR-S3: Blade output escaped; markdown rendered through safe HTML pipeline.
- NFR-S4: Mass assignment protected via `$fillable` / `$guarded` on models.
- NFR-S5: Authorization enforced via middleware and controller checks on every protected action.
- NFR-S6: R2 signed URLs expire per `LMS_VIDEO_SIGNED_URL_TTL`.

##### Usability

- NFR-U1: Learner UI uses dark zinc/emerald theme with clear navigation (Browse, Forum, Dashboard).
- NFR-U2: Admin UI uses sidebar CMS layout separate from learner shell.
- NFR-U3: Course outline shows watched/completed lesson states (no lock state when enrolled).
- NFR-U4: Error and validation messages are clear and actionable.

##### Maintainability

- NFR-M1: Business logic lives in dedicated services (`LessonAccessService`, `QuizGradingService`, `ActivityLogger`, etc.).
- NFR-M2: Code follows SOLID principles.
- NFR-M3: One migration file per database table.
- NFR-M4: Separate seeder file per model/domain seed.

##### Testability

- NFR-T1: Critical flows covered by Pest feature tests (registration, gating, quiz admin, monitoring, password).
- NFR-T2: New epics require happy and unhappy path tests before approval.
- NFR-T3: Security checks per `skills/security-checking.md` on every review.

##### Auditability

- NFR-A1: Activity logs are append-only records with event type, timestamps, and contextual IDs.
- NFR-A2: Quiz attempts and answers are preserved for review.

### Recommended Version 1 Phases

> **Status:** Most of Version 1 is implemented. Technical breakdown and epic status: `docs/phase-1-technical-design-and-tasks.md`.

#### Phase 1: Foundation & Access

- Laravel project setup, auth, roles, admin approval, profiles.

#### Phase 2: Course Content

- Courses, modules, lessons, video drivers, markdown docs, admin CMS.

#### Phase 3: Assessment & Gating (historical V1 plan)

> **Superseded.** Built in Phases 1–2 with quiz-based gating. See **Phase 3: Learning Path Redesign** below.

#### Phase 4: Community (historical V1 plan)

- Forums, lesson comments, mentions, notifications, rate limits.

### Phase 4: Teams & Assignments

> **Status:** Epic 1 and Epic 2 shipped. See [`docs/srs-team-assignments.md`](./srs-team-assignments.md) and [`docs/srs-team-general-posts.md`](./srs-team-general-posts.md).

| Epic | Feature |
|------|---------|
| 1 | Team file assignments (MS Teams–style upload + instructor review) — **shipped** |
| 2 | Team General posts + restored learner navbar — **shipped** |

**Epic 1 summary:** Assignments live on a **team**. Instructors/admins create them; students upload files/zips; instructors review and return with feedback.

**Epic 2 summary:** Team pages use the full learner navbar. Instructors/admins post on General; students reply. `@handle` and `@all` notify team members.

#### Phase 5: Administration & Monitoring

- User management, enrollment assignment, activity logs, forum setup.

#### Phase 6: Hardening & Remaining V1 Gaps

> **Superseded by Phase 2.** See `docs/phase-2-technical-design-and-tasks.md`.

### Phase 2: Complete V1 & Platform Polish (Active)

> **Status:** Complete. Technical breakdown: `docs/phase-2-technical-design-and-tasks.md`.

Finish all remaining V1 gaps and platform polish in one pass before new product features.

| Epic | Feature |
|------|---------|
| 1 | Remove dead `StudyEvent` code |
| 2 | Lesson delete (admin, safe cascade) |
| 3 | Module & lesson reorder (YouTube-style drag UX) |
| 4 | Module quiz admin CRUD |
| 5 | Video watch progress + resume playback |
| 6 | Social profile foundation (avatar, links, public progress) |
| 7 | Forum admin (edit/delete/reorder categories & tags) |
| 8 | ~~Password reset~~ Removed — admin resets passwords instead |
| 9 | Full Pest test coverage for all features |
| 10 | Security review + sign-off |

**Explicitly excluded from Phase 2:**

- Instructor role (not needed now)
- Self-enrollment
- Full social platform (feeds, follow, DMs) — profile foundation only
- Certificates, payments, API

### Phase 3: Decouple Quizzes (Epic 1 complete)

> **Status:** Epic 1 shipped. See `docs/phase-3-technical-design-and-tasks.md` and `docs/flows/phase-3-epic-1-decouple-quizzes-sequence.md`.

Free lesson navigation for enrolled users. Completion via video watch. Learner quizzes off by default; question bank and admin quiz CRUD unchanged.

### Success Metrics

- Learners progress via primary completion, not quiz submit (Phase 3).
- Admin can create a course with modules, lessons, and quizzes without code changes.
- Activity logs capture lesson and quiz events for enrolled learners.
- Zero critical security issues (XSS, mass assignment, unauthorized access) in review.
- Core Pest suite passes on every epic completion.
