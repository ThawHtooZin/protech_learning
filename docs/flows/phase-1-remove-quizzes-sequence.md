# Phase 1, Epic - Remove Quizzes & Question Bank

## Goal
Remove quizzes, question bank, and related learner/admin UI so the product focuses on courses, lessons, teams, and users.

## What was removed
- Learner quiz take/result routes and views
- Admin quiz builders (lesson + module)
- Question bank CRUD (routes, controllers, views)
- Quiz monitoring tab and quiz activity logging helpers
- Quiz/Question/Attempt models and grading service
- Quiz seeders (`HtmlLessonQuizzesSeeder`)
- Quiz feature tests

## What remains (intentionally)
- Existing assessment DB tables/migrations (historical schema; not used by app code)
- `lesson_progress.quiz_passed` column (unused; safe to ignore)

## Manual checklist
1. Admin sidebar has no Question bank / New course clutter (New course lives on Courses page).
2. Course edit has modules/lessons only — no Add quiz links.
3. Lesson page has video + docs + discussion only.
4. Course show has no module quiz row / accuracy %.
5. Monitoring has All / Lessons / Forums / Courses (no Quizzes).
6. Visiting old `/admin/questions` or `/quizzes/{id}` returns 404.
