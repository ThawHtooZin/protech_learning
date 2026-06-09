# Phase 3 Technical Design and Task Breakdown

## Protech Learning System — Phase 3: Decouple Quizzes

### Purpose

Today, **quizzes are the main gate** for lesson completion and course progress (`lesson_progress.quiz_passed`). Phase 3 makes quizzes an **optional, secondary** module. Progression and completion will use a **new primary model** (your idea — confirm before coding).

### Source Inputs

- PRD: `docs/protech-learning-system-prd.md`
- `.cursorrules` — one epic at a time; tests + `docs/flows/` per epic on completion

---

## 1. Current vs Target

| | Now (built) | Phase 3 target |
|---|-------------|----------------|
| Unlock next lesson | Prior lesson quiz submitted | Prior lesson **primary-complete** (new rule) |
| Lesson quiz | Required for progress | **Optional** — never blocks path |
| Lesson without quiz | Blocks course | Can still complete |
| Quiz UI | Primary on lesson page | **Secondary** |
| Question bank & admin | Yes | Keep |

### Code coupled to quizzes today

- `LessonAccessService` — `isLessonCompleteForUser()` → `quiz_passed`
- `QuizGradingService` — sets `quiz_passed` on lesson quiz submit
- Completion %, outline checkmarks, profile progress, module recap access
- `lessons/show.blade.php` — quiz-centric copy and layout

**Do not remove** quizzes, attempts, or question bank. Decouple **gating and progress** only.

---

## 2. Primary Completion Model

> **Awaiting your decision** before implementation. Replace this section when approved.

- What counts as “lesson done” for unlocking the next lesson: **[TBD — your idea]**
- Existing fields that may apply: `lesson_progress.watched`, `last_position_seconds` (Phase 2 video progress)

---

## 3. Epic 1: Decouple Quizzes — **Pending**

| Task | Acceptance Criteria |
|------|---------------------|
| 1.1 Primary completion rule documented in PRD + §2 above | User approved |
| 1.2 `LessonAccessService` — gating uses primary completion, not `quiz_passed` | Sequential unlock works |
| 1.3 `QuizGradingService` — no longer drives path unlock via `quiz_passed` | Quiz submit optional |
| 1.4 Completion %, outline, profile, module recap — aligned with primary completion | No quiz-only blockers |
| 1.5 Lesson page — quiz section secondary; remove “must submit to continue” copy | UI matches optional quizzes |
| 1.6 Pest feature tests (happy + unhappy paths) | Suite green |
| 1.7 Flow doc | `docs/flows/phase-3-epic-1-decouple-quizzes-sequence.md` |

### Phase 3 done when

- [ ] Epic 1 complete and user-approved
- [ ] Learners can progress without submitting quizzes
- [ ] Quizzes remain takeable when attached

---

## 4. AI Workflow

1. **Currently Planning: Phase 3, Epic 1** before any code.
2. Wait for approval of completion model + plan.
3. On finish: **Completed: Phase 3, Epic 1** — then wait before next phase.
