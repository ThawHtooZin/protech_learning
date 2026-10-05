# Protech LMS — Admin Master Guide

Short guide for anyone running this product as **Admin**. Use it to learn features and smoke-test the system.

---

## 1. Sign in

| | |
| --- | --- |
| URL | `/login` |
| Seed admin | `admin@gmail.com` / `password` (after `php artisan migrate --seed`) |
| Admin home | `/admin` |

Language: use **EN | မြန်** in the header (English / Burmese).

---

## 2. Roles (know these)

| Role | What they can do |
| --- | --- |
| **Admin** | Everything: users, courses, teams, forums setup, monitoring, assignments on any team |
| **Instructor** | Only on **teams they belong to**: manage students, create/review assignments. **No** global users or courses |
| **Student** | After approval: enrolled courses, forums, team assignments (upload / turn in) |

New self-registrations are **Student** and **pending** until an admin approves them.

---

## 3. Feature map (admin)

### Overview — `/admin`
Counts, pending approvals, recent courses/activity. Entry point for ops.

### Teams & users — `/admin/users`
- **Teams** tab: card grid → open a team → add instructors/students, rename/delete, link to team workspace (assignments).
- **All users** tab: list accounts → open a user.

### User detail — `/admin/users/{id}`
Approve / revoke · change role · assign **course enrollments** · reset password · delete (not yourself / not last admin).

### Courses — `/admin/courses`
Create/edit/delete courses. On edit: modules, lessons, drag reorder, publish, YouTube (or other) video, markdown docs.

### Monitoring — `/admin/monitoring`
Lesson / forum / course activity logs. Per-user monitoring from a user page.

### Forum setup
- Categories — `/admin/forums/categories`
- Tags — `/admin/forums/tags`

### Learner site (admin can still use)
Browse courses, forums, **Teams** workspace (`/teams`), notifications, profile. Same chrome as learners.

---

## 4. Day-to-day flows

### A. Onboard a learner
1. User registers (or you create under All users).
2. Approve them on user detail.
3. Assign courses on that same page.
4. (Optional) Add them to a **team** for assignments.

### B. Publish a course
1. **Courses → New course** → title, publish when ready.
2. Add modules → add lessons (video + docs).
3. Drag to reorder.
4. Enroll students (user detail → course access).

### C. Team + assignment (file homework)
1. Create team → add instructor(s) + student(s).
2. Open **Open team workspace** (or `/teams/{id}`).
3. Sidebar: **Assignments** (list with Upcoming / Past due / Completed) and **Channels → General** (members).
4. Instructor/admin: **+** new assignment (+ optional **Resources**).
5. Student: **Attach** files → **Turn in**.
6. Instructor: download → **Return** with feedback → optional **Close** assignment.

### D. Forum
1. Admin sets categories/tags.
2. Approved users post threads/replies; mentions can notify.

---

## 5. Smoke-test checklist

Do these once on a fresh or staging install:

- [ ] Login as seed admin; open `/admin`.
- [ ] Switch language EN ↔ မြန်; UI labels change.
- [ ] Create a course + module + lesson; publish; enroll a student; student opens lesson.
- [ ] Register a new student → appears pending → approve → they reach dashboard.
- [ ] Create a team; add instructor + student; create assignment; student turns in a file; instructor returns with feedback.
- [ ] Instructor **cannot** open `/admin/users` or `/admin/courses` (403).
- [ ] Create forum category; student posts a thread.
- [ ] Open Monitoring; confirm recent lesson/forum events show when users are active.
- [ ] Reset a user’s password from admin; sign in with the new password.

---

## 6. What is out of scope (do not expect)

- Quizzes / question bank (removed)
- Payments, certificates, mobile app
- Numeric grades/rubrics on assignments (feedback text only for now)
- Virus scanning on uploads

---

## 7. Related docs

| Doc | When to open |
| --- | --- |
| [i18n-map.md](./i18n-map.md) | Adding/translating UI strings |
| [srs-team-assignments.md](./srs-team-assignments.md) | Assignment rules in detail |
| [flows/phase-4-epic-1-team-assignments-sequence.md](./flows/phase-4-epic-1-team-assignments-sequence.md) | Assignment QA steps |
| [protech-learning-system-prd.md](./protech-learning-system-prd.md) | Full product requirements |

---

**Keep this file as the first read for new admins.** Update it when a major feature ships.
