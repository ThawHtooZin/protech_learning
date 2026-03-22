# Sequence diagrams — critical flows

Three flows that are easy to get wrong without an explicit sequence: **mark lesson complete**, **quiz attempt**, **mention**.

These sequences are **server-side and HTTP**; they are the same whether the user arrived via the **learner** or **admin** UI (admin does not change learner progress mechanics).

---

## 1. Mark lesson complete (Udemy-style checkbox)

**Goal:** Learners explicitly mark a lesson done; `lesson_progress.watched` reflects completion (no second-by-second video position tracking).

```mermaid
sequenceDiagram
  participant Browser
  participant LessonPage as Lesson_controller
  participant Complete as Lesson_complete_controller
  participant DB as MySQL
  Browser ->> LessonPage: GET lesson page
  LessonPage ->> DB: load lesson + enrollment + progress
  LessonPage ->> Browser: HTML + checkbox state
  Browser ->> Complete: POST complete completed=0|1
  Complete ->> Complete: auth + enrolled + in-sequence for recording
  Complete ->> DB: update lesson_progress watched, started
  Complete ->> Browser: redirect + flash
```

**Failure behavior:** Invalid lesson or not enrolled → **403**; out of sequence → **redirect** with flash (no `lesson_progress` write). Lesson quiz and gating still use `watched` + `quiz_passed` as before.

---

## 2. Quiz submit and grade

**Goal:** One transactional grading path; unlimited retakes until pass (store each attempt).

```mermaid
sequenceDiagram
  participant User
  participant QuizCtrl as Quiz_controller
  participant GradeSvc as Grading_service
  participant DB as MySQL
  User ->> QuizCtrl: POST quiz_id answers
  QuizCtrl ->> QuizCtrl: auth + can access quiz
  QuizCtrl ->> GradeSvc: gradeAttempt
  GradeSvc ->> DB: begin transaction
  GradeSvc ->> DB: load quiz + questions + options
  GradeSvc ->> DB: insert quiz_attempt
  GradeSvc ->> DB: insert attempt_answers
  GradeSvc ->> GradeSvc: compute score vs threshold
  GradeSvc ->> DB: update lesson_progress quiz_passed if lesson quiz
  GradeSvc ->> DB: commit
  QuizCtrl ->> User: result passed or failed
```

**Unlock interaction:** If this is a **lesson** quiz, `quiz_passed` on `lesson_progress` may gate the **next** lesson or module via **Gating** rules after commit.

---

## 3. Mention on forum post or lesson comment

**Goal:** Persist UGC, then notify mentioned users (in-app only in v1).

```mermaid
sequenceDiagram
  participant User
  participant Ctrl as Forum_or_Comment_controller
  participant Mention as Mention_parser
  participant Notify as Notification_dispatcher
  participant DB as MySQL
  User ->> Ctrl: POST body
  Ctrl ->> Ctrl: auth + rate limit forum if forum
  Ctrl ->> DB: insert post or comment
  Ctrl ->> Mention: extractHandles body
  Mention ->> DB: resolve handles to user ids
  Ctrl ->> Notify: for each mentioned user
  Notify ->> DB: insert notifications rows
  Ctrl ->> User: redirect or JSON ok
```

**Edge cases:** Self-mention (usually ignore or allow per product); invalid handles (skip silently or show validation — product choice). **Instructors** are a subset of users located by `role` or flag when building notification copy.

---

## Flow summary

| Flow | Primary tables | Critical invariant |
|------|----------------|---------------------|
| Mark complete | `lesson_progress` | User must be enrolled; in-sequence to record |
| Quiz | `quiz_attempts`, `attempt_answers` | Grade + progress update in one transaction |
| Mention | `forum_posts` / `lesson_comments`, `notifications` | Create content first, then notifications |
