# Phase 2 Technical Design and Task Breakdown

## Protech Learning System — Phase 2: Complete V1 & Platform Polish

> **Note:** Quiz-based gating built in Phases 1–2 will be **replaced in Phase 3**. See `docs/phase-3-technical-design-and-tasks.md`.

### Purpose

Phase 2 finishes all remaining gaps from Version 1, adds admin UX improvements, learner video progress, social-ready profiles, full test coverage, and a security review.

**Goal:** Close the platform to a shippable baseline in one focused pass. Extra features (full social platform, payments, certificates, etc.) come **after** Phase 2.

### Source Inputs

- PRD: `docs/protech-learning-system-prd.md` (Phase 2 section)
- Phase 1 status: `docs/phase-1-technical-design-and-tasks.md`
- AI rules: `.cursorrules`, `skills/pest-testing.md`, `skills/security-checking.md`

### Explicitly Out of Phase 2

| Item | Decision |
|------|----------|
| Instructor role | **Not needed.** Enum may stay in DB; no UI, routes, or permissions. |
| Self-enrollment | Deferred to post–Phase 2 unless product adds it later. |
| Full social platform (feeds, follow, DMs) | Deferred. Phase 2 only lays **profile foundation**. |
| Certificates, payments, API | Deferred. |

---

## 1. Technical Design

### 1.1 Architecture Goals

- Keep each epic small enough for one code review.
- Every epic ships with Pest feature tests (happy + unhappy paths).
- Every epic gets a `docs/flows/phase-2-epic-N-*.md` sequence doc on completion.
- Reorder and delete operations must preserve `sort_order` integrity in one transaction.
- Video progress saves are debounced; gating still uses `quiz_passed` (watch progress is UX + analytics, not a gate).
- Avatar uploads use Laravel storage (`public` disk); validate mime and size.
### 1.2 What Is `StudyEvent`? (Cleanup Epic)

Early experiment before `ActivityLogger` was finalized. The app now logs to:

- `lesson_activity_logs`
- `quiz_activity_logs`
- `forum_activity_logs`
- `course_activity_logs`

`StudyEvent` model + `StudyEventLogger` have **no migration and no callers**. Phase 2 **deletes** them. Do not rebuild.

### 1.3 Lesson Delete Design

**Route:** `DELETE /admin/courses/{course}/modules/{module}/lessons/{lesson}`

**Cascade rules (transaction):**

1. Delete lesson quiz (if any) → attempts → attempt answers.
2. Delete `lesson_progress` rows for this lesson.
3. Delete `lesson_comments` (and replies).
4. Delete `lesson_activity_logs` referencing this lesson.
5. Delete the lesson row.
6. Renumber `sort_order` for remaining lessons in the module (1..N).

**Guards:**

- Confirm dialog in UI (“Deletes quiz, progress, and comments”).
- Admin only.

### 1.4 Module & Lesson Reorder Design

**YouTube-style UX on admin course edit:**

- Vertical draggable list of modules.
- Within each module, draggable lesson rows (grip handle, title, quiz badge).
- Visual feedback while dragging (highlight drop target).
- Save via AJAX or form POST with ordered ID arrays.

**Implementation:**

- Library: [SortableJS](https://sortablejs.github.io/Sortable/) (lightweight, works with Alpine).
- Endpoints:
  - `PUT /admin/courses/{course}/modules/reorder` — body: `{ module_ids: [3,1,2] }`
  - `PUT /admin/courses/{course}/modules/{module}/lessons/reorder` — body: `{ lesson_ids: [5,4,6] }`
- Service: `CourseStructureReorderService` updates `sort_order` in a transaction.

**Learner sidebar** (`lessons/show.blade.php`) already reads `sort_order`; no learner change needed beyond optional “X of Y” progress indicator.

### 1.5 Video Watch Progress Design

**Fields (existing on `lesson_progress`):**

- `last_position_seconds`
- `started`, `watched`
- `last_checkpoint_at`

**New endpoint:**

- `POST /lessons/{lesson}/progress` (auth + approved + enrolled)
- Body: `{ position_seconds: int }`
- Debounce client-side (e.g. every 10–15s + on pause/leave page).
- Set `started = true` on first save; `watched = true` when position ≥ 90% of `duration_seconds` (or configurable threshold in `config/lms.php`).

**Players:**

| Driver | Approach |
|--------|----------|
| HTML5 (`r2`) | `timeupdate` + `pause` events on `<video>` |
| YouTube embed | YouTube IFrame API (`postMessage` / `YT.Player`) for `getCurrentTime()` |

**Resume:** On lesson load, if `last_position_seconds > 0` and lesson not complete, show “Resume from MM:SS” or auto-seek.

**Gating unchanged:** Next lesson still unlocks on quiz submit only.

### 1.6 Social Profile Foundation Design

Phase 2 extends profiles toward a future social layer. Not a full social network yet.

**New / exposed fields:**

| Field | Source | Phase 2 |
|-------|--------|---------|
| `avatar_path` | `profiles` | Upload + display |
| `social_links` | `profiles` JSON | Edit UI (GitHub, LinkedIn, X, website) |
| `display_name`, `bio`, `handle` | existing | Keep |
| Course progress | computed | Show on public profile |

**Optional new columns** (one migration per table if added):

- `profiles.location` (string, nullable)
- `profiles.website` (string, nullable) — or keep in `social_links` only

**Avatar rules:**

- Max 2 MB; jpeg, png, webp.
- Store on `public` disk: `avatars/{user_id}/{uuid}.webp`.
- Delete old file on replace.

**Public profile `/u/{handle}` shows:**

- Avatar, display name, handle, bio, social link icons.
- Enrolled courses with completion % (only published courses; respect privacy: show only if user opts in — default **show progress** for Phase 2).

### 1.7 Forum Admin Design

Extend `ForumSetupController`:

| Action | Route |
|--------|-------|
| Edit category | `GET/PUT /admin/forums/categories/{category}` |
| Delete category | `DELETE` — block if threads exist (or cascade with confirm) |
| Reorder categories | `PUT /admin/forums/categories/reorder` |
| Edit tag | `GET/PUT /admin/forums/tags/{tag}` |
| Delete tag | `DELETE` — detach from threads |

### 1.8 Module Quiz Admin (V1 Carryover)

Lesson quizzes have full CRUD; module quizzes only have create. Phase 2 adds:

- `editModuleQuiz`, `updateModuleQuiz`, `destroyModuleQuiz`
- Mirror lesson quiz patterns; destroying resets no lesson progress (module quiz is separate).

### 1.9 Password Reset — Removed

Self-service forgot-password was removed. **Admins** reset user passwords from `/admin/users/{user}` (existing feature). Logged-in users can still change their own password on the profile edit page.

### 1.10 Testing Strategy (Phase 2)

Per `skills/pest-testing.md` — **every epic** includes:

- Feature test(s) for main flow.
- At least one unhappy path (403, validation error, blocked delete).

**Coverage map by epic** (see §2). Final Epic 10 runs full suite + security checklist.

### 1.11 Security Review Checklist (Epic 10)

Per `skills/security-checking.md`:

- [ ] CSRF on all new forms
- [ ] Mass assignment on new fields
- [ ] File upload validation (avatar)
- [ ] Authorization: admin-only reorder/delete; enrolled-only progress
- [ ] Blade escaping on profile and forum admin
- [ ] Rate limit on progress endpoint (optional throttle)
---

## 2. Phase 2 Task Breakdown

> **Build order:** Execute epics 1 → 10 sequentially. Wait for user approval before each epic per `.cursorrules`.

### Epic 1: Dead Code Cleanup — **Complete**

| Task | Acceptance Criteria |
|------|---------------------|
| 1.1 Delete `StudyEvent` model | File removed |
| 1.2 Delete `StudyEventLogger` service | File removed |
| 1.3 Grep confirms no references | `php artisan test` still passes |

**Tests:** None required (no behavior). Smoke: existing test suite green.

---

### Epic 2: Lesson Delete — **Complete**

| Task | Acceptance Criteria |
|------|---------------------|
| 2.1 `LessonAdminController@destroy` | Transactional cascade per §1.3 |
| 2.2 Route + DELETE button on course edit | Confirm dialog |
| 2.3 Renumber `sort_order` after delete | Gaps closed |
| 2.4 Pest: delete lesson with quiz + progress | Related rows removed |
| 2.5 Pest: 404 when lesson not in module | Unhappy path |
| 2.6 Flow doc | `docs/flows/phase-2-epic-2-lesson-delete-sequence.md` |

---

### Epic 3: Module & Lesson Reorder — **Complete**

| Task | Acceptance Criteria |
|------|---------------------|
| 3.1 `CourseStructureReorderService` | Atomic sort_order updates |
| 3.2 Module reorder endpoint + SortableJS UI | Drag modules on course edit |
| 3.3 Lesson reorder endpoint + SortableJS UI | Drag lessons within module; grip handles |
| 3.4 Mobile-friendly fallback | Up/down buttons if drag unavailable |
| 3.5 Pest: reorder modules and lessons | DB order matches payload |
| 3.6 Pest: non-admin forbidden | 403 |
| 3.7 Flow doc | `docs/flows/phase-2-epic-3-reorder-sequence.md` |

---

### Epic 4: Module Quiz Admin CRUD — **Complete**

| Task | Acceptance Criteria |
|------|---------------------|
| 4.1 Edit/update module quiz | Same question picker as lesson quiz |
| 4.2 Destroy module quiz | Confirm dialog; attempts removed |
| 4.3 Admin UI links on course edit | Edit/delete next to module quiz |
| 4.4 Pest: CRUD module quiz | Happy + destroy |
| 4.5 Flow doc | `docs/flows/phase-2-epic-4-module-quiz-admin-sequence.md` |

---

### Epic 5: Video Watch Progress — **Complete**

| Task | Acceptance Criteria |
|------|---------------------|
| 5.1 `config/lms.php` watch threshold | e.g. `watch.completed_percent = 90` |
| 5.2 `LessonProgressController@store` or action on `LessonController` | Validates position, updates row |
| 5.3 HTML5 player JS | Debounced saves, resume on load |
| 5.4 YouTube IFrame API integration | Progress for embed lessons |
| 5.5 UI: resume prompt or auto-seek | Shows last position |
| 5.6 Activity log optional `lesson_progress_saved` | Or reuse `lesson_opened` only — document choice |
| 5.7 Pest: save progress enrolled user | `last_position_seconds` updated |
| 5.8 Pest: guest / not enrolled forbidden | 403 |
| 5.9 Flow doc | `docs/flows/phase-2-epic-5-video-progress-sequence.md` |

---

### Epic 6: Social Profile Foundation — **Complete**

| Task | Acceptance Criteria |
|------|---------------------|
| 6.1 Avatar upload on profile edit | Validation, storage, delete old |
| 6.2 Social links form | GitHub, LinkedIn, X, website keys in JSON |
| 6.3 Optional: `location` migration + field | One migration file if added |
| 6.4 Public profile shows avatar, links, course progress | `/u/{handle}` |
| 6.5 Nav/header shows small avatar | Learn layout |
| 6.6 Pest: upload avatar, update links | Happy path |
| 6.7 Pest: invalid file rejected | Unhappy path |
| 6.8 Flow doc | `docs/flows/phase-2-epic-6-social-profile-sequence.md` |

---

### Epic 7: Forum Admin CRUD — **Complete**

| Task | Acceptance Criteria |
|------|---------------------|
| 7.1 Edit/update/delete categories | Block delete if threads exist (with message) |
| 7.2 Category reorder | Sortable or sort_order field |
| 7.3 Edit/update/delete tags | Detach on delete |
| 7.4 Admin UI updates | Edit/delete buttons on list pages |
| 7.5 Pest: CRUD category and tag | Happy paths |
| 7.6 Pest: cannot delete category with threads | Unhappy path |
| 7.7 Flow doc | `docs/flows/phase-2-epic-7-forum-admin-sequence.md` |

---

### Epic 8: Password Reset — **Removed**

Self-service forgot-password removed. Admins use **Set password** on the user admin page.

---

### Epic 9: Full Test Coverage (V1 + Phase 2 Gaps) — **Complete**

Add tests for features that lack coverage:

| Area | Test file (suggested) |
|------|---------------------|
| Admin approval | `ApprovalWorkflowTest` |
| Course enrollment | `AdminEnrollmentTest` |
| Forums (create thread/post) | `ForumTest` |
| Lesson comments + mentions | `LessonCommentTest` |
| Notifications | `NotificationTest` |
| Course admin CRUD | `CourseAdminTest` |
| Catalog (public) | `CourseCatalogTest` |
| Monitoring logs | Extend `StudyMonitoringTest` |

**Acceptance:** `php artisan test` passes; every major route group has at least one feature test.

---

### Epic 10: Security Review & Phase 2 Sign-off — **Complete**

| Task | Acceptance Criteria |
|------|---------------------|
| 10.1 Run security checklist §1.11 | Document findings in `docs/phase-2-security-review.md` |
| 10.2 Fix critical/high issues | Before sign-off |
| 10.3 Update PRD Phase 2 status | Mark complete |
| 10.4 Run full Pest suite + Pint | Green |
| 10.5 Flow doc | `docs/flows/phase-2-epic-10-security-signoff-sequence.md` |

---

## 3. Phase 2 Completion Criteria

Phase 2 is complete when:

- [x] Admins can delete lessons with safe cascade.
- [x] Admins can reorder modules and lessons (drag-and-drop).
- [x] Module quizzes have full admin CRUD.
- [x] Learners get saved video position and resume playback.
- [x] Profiles support avatar, social links, and public progress display.
- [x] Admins can edit/delete forum categories and tags.
- [x] Password reset via admin only (self-service flow removed).
- [x] `StudyEvent` dead code removed.
- [x] Pest covers every major feature area.
- [x] Security review documented and critical issues fixed.
- [x] Instructor role not expanded (explicitly out of scope).

---

## 4. Recommended Build Order

```text
Epic 1  → StudyEvent cleanup
Epic 2  → Lesson delete
Epic 3  → Reorder (YouTube-style)
Epic 4  → Module quiz admin
Epic 5  → Video watch progress
Epic 6  → Social profiles
Epic 7  → Forum admin
Epic 8  → (removed — admin password reset only)
Epic 9  → Remaining test coverage
Epic 10 → Security review + sign-off
```

---

## 5. AI Workflow Reference

1. State **"Currently Planning: Phase 2, Epic N"** at plan start.
2. Read PRD + this file + relevant existing code.
3. Wait for user approval before coding.
4. One migration per table; separate seeders per model.
5. On completion: **"Completed: Phase 2, Epic N"** + flow doc + tests green.
6. Proceed to next epic only after user approval.
