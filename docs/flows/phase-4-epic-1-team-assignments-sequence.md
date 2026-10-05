# Phase 4, Epic 1 — Team Assignments

## Summary

Microsoft Teams–style file assignments live on a **team**. Instructors (on that team) or admins create assignments; students upload files; instructors review and return with feedback. Instructors manage students and assignments only for teams they belong to — not global users or courses.

SRS: [`docs/srs-team-assignments.md`](../srs-team-assignments.md)

## Sequence

```mermaid
sequenceDiagram
    participant Instructor
    participant Student
    participant Web as TeamWorkspace / AssignmentController
    participant Access as TeamAccessService
    participant Files as AssignmentFileService
    participant DB as assignments / submissions / files
    participant Disk as private storage

    Instructor->>Web: POST /teams/{team}/assignments
    Web->>Access: canManageAssignments?
    Access-->>Web: yes (admin or instructor member)
    Web->>DB: create assignment (+ optional resources)
    Web->>Disk: store resources
    Web-->>Instructor: Redirect assignment show

    Student->>Web: GET /teams/{team}/assignments/{id}
    Web->>Access: canViewTeam?
    Access-->>Web: yes (member)
    Web-->>Student: Instructions + upload form

    Student->>Web: POST .../submit files
    Web->>Access: canSubmit?
    Web->>Files: replaceSubmissionFiles
    Files->>Disk: store under assignments/{id}/submissions/{id}
    Web->>DB: submission status=submitted
    Web-->>Student: Redirect with status

    Instructor->>Web: POST .../submissions/{id}/return
    Web->>Access: canReview?
    Web->>DB: status=returned + feedback
    Web-->>Instructor: Redirect

    Student->>Web: GET assignment
    Web-->>Student: Sees feedback + own files
```

## Manual QA

1. As **admin**, create Team A, add an instructor and two students (admin Teams UI).
2. Sign in as **instructor** → **Teams** → open Team A → **New assignment** (title + optional resource) → save.
3. Sign in as **student on Team A** → see Teams-style **My work** panel → upload any type (e.g. `.py`) → **Turn in**.
4. Sign in as **student not on Team A** → cannot open that assignment (403).
5. As instructor, open assignment → download student file → return with feedback.
6. As student, confirm feedback is visible; replace files while still open → status Submitted again.
7. As instructor, **Close** assignment → student upload is rejected.
8. As instructor, try `/admin/users` and `/admin/courses` → 403; **Add students** on Team A still works.
9. As admin (not required to be a team member), create an assignment on any team and review submissions.

## Data / storage checks

- Tables: `assignments`, `assignment_attachments`, `assignment_submissions`, `assignment_submission_files`, `assignment_activity_logs`.
- Files on private disk (`config('lms.assignments.disk')`, default `local` → `storage/app/private`).
- Downloads only via authorized routes (no public URLs).

## Automated tests

```bash
php artisan test --filter=TeamAssignmentTest
```
