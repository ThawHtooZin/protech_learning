# Phase 3 Technical Design and Task Breakdown

## Protech Learning System — Phase 3: Decouple Quizzes

### Purpose

Quizzes were the main gate for lesson completion (`lesson_progress.quiz_passed`). Phase 3 removes that gate. Enrolled learners can open **any lesson** in a published course. Completion tracking uses **video watch** (`lesson_progress.watched`). Learner quiz UI is **disabled by default** via config; admin quiz tooling stays for a future phase.

### Source Inputs

- PRD: `docs/protech-learning-system-prd.md`
- `.cursorrules` — one epic at a time; tests + `docs/flows/` per epic on completion

---

## 1. Current vs Target

| | Before Phase 3 | After Phase 3 (Epic 1) |
|---|-------------|------------------------|
| Unlock next lesson | Prior lesson quiz submitted | **Enrollment only** — all lessons open |
| Lesson complete | `quiz_passed` | `watched` (video progress endpoint) |
| Lesson without quiz | Blocked forever | Fully accessible |
| Learner quiz UI | Primary on lesson page | Hidden when `LMS_QUIZZES_LEARNER_ENABLED=false` |
| Quiz take routes | Active | **404** when learner quizzes disabled |
| Question bank & admin | Yes | Unchanged |

---

## 2. Primary Completion Model

**Approved and implemented in Epic 1:**

- **Lesson done** = `lesson_progress.watched = true` (set by `POST /lessons/{lesson}/progress` when watch % ≥ `LMS_WATCH_COMPLETED_PERCENT`).
- **Course progress %** = watched lessons ÷ total lessons.
- **No sequential lock** — enrollment is the only learner gate.

Config:

```env
LMS_QUIZZES_LEARNER_ENABLED=false   # default — learner quiz routes return 404
```

Set `true` later to re-enable optional learner quizzes without changing gating.

---

## 3. Epic 1: Decouple Quizzes — **Complete**

| Task | Status |
|------|--------|
| 1.1 Primary completion rule in PRD + §2 | Done |
| 1.2 `LessonAccessService` — enrollment-only view; completion via `watched` | Done |
| 1.3 `QuizGradingService` — `quiz_passed` only when learner quizzes enabled | Done |
| 1.4 Course outline, profile progress — `watched`-based | Done |
| 1.5 Lesson/course pages — no locks; quiz UI hidden when disabled | Done |
| 1.6 Pest feature tests updated | Done |
| 1.7 Flow doc | `docs/flows/phase-3-epic-1-decouple-quizzes-sequence.md` |

### Phase 3 Epic 1 done when

- [x] Enrolled learners can open any lesson without quiz or order gate
- [x] Learner quiz routes 404 when `LMS_QUIZZES_LEARNER_ENABLED=false`
- [x] Admin question bank and quiz CRUD unchanged
- [x] Test suite green

---

## 4. Future (not in Epic 1)

- Re-enable optional learner quizzes (`LMS_QUIZZES_LEARNER_ENABLED=true`) with practice-only UX
- Optional sequential mode (if product wants it back as a setting)

---

**Completed: Phase 3, Epic 1**
