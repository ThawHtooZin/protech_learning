# Protech LMS — Tester Manual (English)

**Give this file to the tester.** Short. Role-by-role. Every feature. Mark Pass / Fail.

---

## 0. What this product is

Protech LMS is a **web learning platform**:

- Admins publish **courses** (modules → lessons with video + docs).
- Students must be **approved**, then **enrolled** in courses by admin.
- Students watch lessons (any order once enrolled), comment, use forums.
- **Teams**: instructors/admins give **file assignments**; students upload; staff return feedback.
- Team **General** channel: staff post, students reply; `@handle` / `@all` notify team members.
- Language switch: **EN | မြန်**.

**Not in product (do not test as missing bugs):** quizzes/question bank, payments, certificates, grades/rubrics, mobile app, virus scan on uploads, self-service password reset (admin sets passwords).

---



## 1. Before testing (you / owner)

1. App URL: [https://lms.protechmm.com](https://lms.protechmm.com) (e.g. `http://localhost` or staging).
2. DB seeded or admin exists: `php artisan migrate --seed` if needed.
3. Create **3 accounts** (recommended):


| Role       | Email                   | Password   | Notes                                                                     |
| ---------- | ----------------------- | ---------- | ------------------------------------------------------------------------- |
| Admin      | `admin@gmail.com`       | `password` | Seed default. Change after test if public.                                |
| Instructor | `instructor@test.local` | `password` | Create in Admin → Teams/Users. Approve. Put on a team.                    |
| Student    | `student@test.local`    | `password` | Create or register → **Approve** → enroll in a course → add to same team. |


1. Prep data once as Admin: **1 published course** with ≥2 lessons (YouTube OK), **1 forum category**, **1 team** with instructor + student, optional assignment.

Mark each row: **P** = Pass, **F** = Fail, **N/A** = blocked / not set up. Write notes under Fail.

---



## 2. Shared / guest


| #   | Steps                                          | Expected                                                           | P/F |
| --- | ---------------------------------------------- | ------------------------------------------------------------------ | --- |
| G1  | Open `/`                                       | Redirects to course catalog `/courses`                             |     |
| G2  | Open `/courses` logged out                     | Published courses list (or empty)                                  |     |
| G3  | Open a published course                        | Detail + outline; no lesson access without login/enroll            |     |
| G4  | Open `/login`, `/register`                     | Forms load                                                         |     |
| G5  | Click **EN** then **မြန်** in header           | Labels switch language                                             |     |
| G6  | Wrong password on login                        | Error; stay logged out                                             |     |
| G7  | Register new student (unique email + username) | Account created; **approval pending** screen; cannot use dashboard |     |
| G8  | Logout                                         | Session ends; guest nav (Sign in / Join)                           |     |


---



## 3. Admin account

Sign in as **Admin**. Prefer Admin panel `/admin` for CMS; learner nav still works.

### 3.1 Admin shell & locale


| #   | Steps                                                                 | Expected                                                     | P/F |
| --- | --------------------------------------------------------------------- | ------------------------------------------------------------ | --- |
| A1  | Open `/admin`                                                         | Dashboard: counts, pending users, links                      |     |
| A2  | Non-admin opens `/admin`                                              | **403**                                                      |     |
| A3  | Sidebar: Overview, Courses, Teams, Monitoring, Forum categories, Tags | All open                                                     |     |
| A4  | Switch EN ↔ မြန် on admin                                             | Labels change                                                |     |
| A5  | **Back to learning site**                                             | Learner site with full nav (Browse, Forum, Teams, Dashboard) |     |




### 3.2 Users


| #   | Steps                                                                        | Expected                                                 | P/F |
| --- | ---------------------------------------------------------------------------- | -------------------------------------------------------- | --- |
| A10 | Teams → **All users** tab                                                    | User list                                                |     |
| A11 | Create account (dialog): role Student, email, password, display name, handle | User created & approved if form says so; appears in list |     |
| A12 | Create Instructor account                                                    | Role Instructor                                          |     |
| A13 | Open pending registered user → **Approve**                                   | Can log in to dashboard                                  |     |
| A14 | **Revoke** approval on a student                                             | Cannot use learner features; enrollments cleared         |     |
| A15 | Re-approve same user                                                         | Access restored                                          |     |
| A16 | Change role Student ↔ Instructor                                             | Role updates                                             |     |
| A17 | Assign **course enrollments** on user page                                   | Courses checked; student can open those lessons          |     |
| A18 | Uncheck a course                                                             | Student loses access to that course’s lessons            |     |
| A19 | **Reset password** for a user → log in as them with new password             | Works                                                    |     |
| A20 | Delete another non-admin user                                                | User gone                                                |     |
| A21 | Try delete **yourself**                                                      | Blocked                                                  |     |
| A22 | Try delete last/only admin (if applicable)                                   | Blocked or safe rule                                     |     |




### 3.3 Teams (admin CMS)


| #   | Steps                                               | Expected                               | P/F |
| --- | --------------------------------------------------- | -------------------------------------- | --- |
| A30 | Teams tab: create team                              | Opens team detail                      |     |
| A31 | Add instructors + students                          | Members grouped Instructors / Students |     |
| A32 | Filter members                                      | List filters                           |     |
| A33 | Remove a student member                             | Removed from team                      |     |
| A34 | Rename team (settings)                              | Name updates                           |     |
| A35 | Open team workspace from admin                      | `/teams/{id}` assignments              |     |
| A36 | Delete empty/test team                              | Team removed                           |     |
| A37 | Instructor opens `/admin/users` or `/admin/courses` | **403**                                |     |




### 3.4 Courses CMS


| #   | Steps                                        | Expected                                              | P/F |
| --- | -------------------------------------------- | ----------------------------------------------------- | --- |
| A40 | Courses → create course (unpublished)        | Exists; not on public catalog for guests as published |     |
| A41 | Publish course                               | Shows on `/courses`                                   |     |
| A42 | Add module                                   | Module on course edit                                 |     |
| A43 | Add lesson (YouTube URL/ref + markdown docs) | Lesson saved                                          |     |
| A44 | Add second lesson                            | Both listed                                           |     |
| A45 | Drag reorder modules/lessons                 | Order persists after refresh                          |     |
| A46 | Edit lesson title/video/docs                 | Updates                                               |     |
| A47 | Delete a lesson                              | Gone; remaining order OK                              |     |
| A48 | Delete empty module                          | Gone                                                  |     |
| A49 | Unpublish course                             | Hidden from public catalog as published               |     |
| A50 | Delete test course                           | Removed                                               |     |




### 3.5 Forum setup


| #   | Steps                        | Expected                            | P/F |
| --- | ---------------------------- | ----------------------------------- | --- |
| A60 | Create forum category        | Appears in admin + learner forums   |     |
| A61 | Edit / reorder categories    | Persists                            |     |
| A62 | Create tag                   | Listed                              |     |
| A63 | Edit / delete unused tag     | Works                               |     |
| A64 | Delete category with threads | Blocked or warned (no orphan crash) |     |




### 3.6 Monitoring


| #   | Steps                                              | Expected                                   | P/F |
| --- | -------------------------------------------------- | ------------------------------------------ | --- |
| A70 | After student opens a lesson: Monitoring → Lessons | `lesson_opened` (or similar) for that user |     |
| A71 | After forum activity: Monitoring → Forums          | Event listed                               |     |
| A72 | Monitoring → Courses                               | Course events if any                       |     |
| A73 | User page → monitoring / per-user view             | User activity visible                      |     |




### 3.7 Admin as learner (same account)


| #   | Steps                                       | Expected                       | P/F |
| --- | ------------------------------------------- | ------------------------------ | --- |
| A80 | Browse → open any lesson without enrollment | Admin can open (bypass)        |     |
| A81 | Teams → any team workspace                  | Can manage assignments / posts |     |
| A82 | Notifications / Profile                     | Work                           |     |


---



## 4. Instructor account

Sign in as **Instructor**. Must be **on the test team**.

### 4.1 Access limits


| #   | Steps                                           | Expected                                                  | P/F |
| --- | ----------------------------------------------- | --------------------------------------------------------- | --- |
| I1  | Open `/admin`, `/admin/users`, `/admin/courses` | **403**                                                   |     |
| I2  | Full learner nav                                | Browse, Forum, Teams, Dashboard, Notifications, Profile   |     |
| I3  | Teams index                                     | Sees only teams they belong to (not other teams’ content) |     |
| I4  | Open another team they are **not** on           | **403**                                                   |     |




### 4.2 Team workspace chrome


| #   | Steps                                                                 | Expected                                                              | P/F |
| --- | --------------------------------------------------------------------- | --------------------------------------------------------------------- | --- |
| I10 | Open team                                                             | Full-height gray **sidebar** (rail); Assignments + Channels → General |     |
| I11 | Navbar still shows Browse / Forum / Teams / Dashboard / Notifications | Present                                                               |     |




### 4.3 Assignments (staff)


| #   | Steps                                                                                   | Expected                               | P/F |
| --- | --------------------------------------------------------------------------------------- | -------------------------------------- | --- |
| I20 | Assignments → **+** create (title, instructions, optional due, optional resource files) | Assignment created; opens detail       |     |
| I21 | Tabs Upcoming / Past due / Completed                                                    | Filter correctly                       |     |
| I22 | Edit assignment                                                                         | Updates                                |     |
| I23 | Student (other account) turns in → instructor downloads file                            | Download works                         |     |
| I24 | **Return** with feedback text                                                           | Student sees feedback; status returned |     |
| I25 | **Close** assignment                                                                    | Student cannot upload/replace          |     |
| I26 | Delete assignment (test)                                                                | Removed with files                     |     |
| I27 | Instructor **not** on team tries create                                                 | **403**                                |     |




### 4.4 Members (instructor)


| #   | Steps                      | Expected                          | P/F |
| --- | -------------------------- | --------------------------------- | --- |
| I30 | General → Add students     | Can add students to **this** team |     |
| I31 | Remove a student           | Works                             |     |
| I32 | Cannot manage global users | No admin user CMS                 |     |




### 4.5 General posts (staff)


| #   | Steps                        | Expected                                                                                 | P/F |
| --- | ---------------------------- | ---------------------------------------------------------------------------------------- | --- |
| I40 | Post on General              | Post appears                                                                             |     |
| I41 | Post with `@all`             | Other team members get notification; author does not                                     |     |
| I42 | Post with `@studentHandle`   | That student notified; outsider with same typed handle but not on team: **not** notified |     |
| I43 | Student tries top-level post | **403** / no composer                                                                    |     |


---



## 5. Student account

Sign in as **approved Student**. Enrolled in test course. On test team.

### 5.1 Access & enrollment


| #   | Steps                                                             | Expected                     | P/F |
| --- | ----------------------------------------------------------------- | ---------------------------- | --- |
| S1  | Unapproved student (register temp)                                | Pending screen; no dashboard |     |
| S2  | Approved but **not** enrolled: open lesson URL                    | **403**                      |     |
| S3  | Enrolled: open course → any lesson (lesson 2 without finishing 1) | **OK** — no quiz/order lock  |     |
| S4  | `/admin`                                                          | **403**                      |     |
| S5  | Team not a member of                                              | **403**                      |     |




### 5.2 Lessons


| #   | Steps                                             | Expected                                             | P/F |
| --- | ------------------------------------------------- | ---------------------------------------------------- | --- |
| S10 | Lesson video plays (YouTube)                      | Player visible                                       |     |
| S11 | Watch far enough / progress saves                 | Progress/resume or watched checkmark after threshold |     |
| S12 | Markdown docs render                              | Readable                                             |     |
| S13 | Course outline: all lessons clickable             | No “Locked”                                          |     |
| S14 | Post lesson comment                               | Appears                                              |     |
| S15 | Comment with `@otherHandle` (other enrolled user) | Other gets notification                              |     |
| S16 | No quiz take UI / `/quizzes/...`                  | Hidden or 404 (quizzes disabled)                     |     |




### 5.3 Profile & password


| #   | Steps                                                   | Expected                            | P/F |
| --- | ------------------------------------------------------- | ----------------------------------- | --- |
| S20 | Edit profile: display name, bio, location, social links | Saves; public `/u/{handle}` updates |     |
| S21 | Upload valid avatar                                     | Shows                               |     |
| S22 | Invalid avatar (huge/wrong type)                        | Validation error                    |     |
| S23 | Change own password (current + new) → re-login          | Works                               |     |
| S24 | Wrong current password                                  | Rejected                            |     |




### 5.4 Forums


| #   | Steps                                     | Expected                  | P/F |
| --- | ----------------------------------------- | ------------------------- | --- |
| S30 | Forums index → category                   | Threads list              |     |
| S31 | Create thread (+ tag if available)        | Thread created            |     |
| S32 | Reply on thread                           | Reply shows               |     |
| S33 | Mention `@handle` in reply                | Notification to that user |     |
| S34 | Hit daily post rate limit (if low config) | Blocked after limit       |     |




### 5.5 Notifications


| #   | Steps              | Expected                           | P/F |
| --- | ------------------ | ---------------------------------- | --- |
| S40 | Open Notifications | List of mentions                   |     |
| S41 | Click notification | Goes to source (forum/lesson/team) |     |
| S42 | Mark all read      | Badge clears                       |     |




### 5.6 Teams — assignments (student)


| #   | Steps                                                | Expected                                    | P/F |
| --- | ---------------------------------------------------- | ------------------------------------------- | --- |
| S50 | Teams → own team → Assignments                       | Sees open assignments                       |     |
| S51 | Open assignment; download staff resources if any     | Works                                       |     |
| S52 | Attach file(s) including `.zip` / code → **Turn in** | Success; status submitted                   |     |
| S53 | Replace submission while open                        | Allowed                                     |     |
| S54 | After instructor returns                             | Sees feedback; can still download own files |     |
| S55 | After closed: turn in again                          | Blocked                                     |     |
| S56 | Open other student’s submission download URL         | **403**                                     |     |
| S57 | Create assignment                                    | **403** / no + button                       |     |




### 5.7 Teams — General (student)


| #   | Steps                                    | Expected                     | P/F |
| --- | ---------------------------------------- | ---------------------------- | --- |
| S60 | Read posts                               | Visible                      |     |
| S61 | Reply to a post                          | Reply appears                |     |
| S62 | Reply with `@instructorHandle` or `@all` | Mentions notify team members |     |
| S63 | Create top-level post                    | Not allowed                  |     |
| S64 | Full navbar on team pages                | Same as rest of site         |     |




### 5.8 Dashboard


| #   | Steps          | Expected                               | P/F |
| --- | -------------- | -------------------------------------- | --- |
| S70 | Open Dashboard | Loads; enrolled courses / links useful |     |


---



## 6. Cross-role matrix (quick)


| Action                     | Admin            | Instructor (on team)          | Student (on team) |
| -------------------------- | ---------------- | ----------------------------- | ----------------- |
| Admin CMS                  | Yes              | No                            | No                |
| Create course/lesson       | Yes              | No                            | No                |
| Approve users / enroll     | Yes              | No                            | No                |
| Create team / add any role | Yes              | Add students only on own team | No                |
| Create assignment          | Yes (any team)   | Own team                      | No                |
| Submit assignment          | No (not student) | No                            | Yes               |
| Return assignment          | Yes              | Own team                      | No                |
| Top-level General post     | Yes              | Own team                      | No                |
| Reply on General           | Yes              | Yes                           | Yes               |
| Open any lesson            | Yes              | If enrolled                   | If enrolled       |


---



## 7. Bug report template

```
ID:
Role / account:
URL:
Steps:
Expected:
Actual:
Screenshot:
Severity: Blocker / Major / Minor
```

---



## 8. Sign-off


|             |                                     |
| ----------- | ----------------------------------- |
| Tester name |                                     |
| Date        |                                     |
| Build / URL |                                     |
| Result      | ☐ Ready ☐ Not ready (list Fail IDs) |


---

**Docs index:** [docs/README.md](./README.md) · Burmese: [tester-manual-my.md](./tester-manual-my.md)