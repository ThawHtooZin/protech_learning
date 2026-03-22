# Feature checklist — manual testing (QA)

Use this document to **walk through the app as a tester** (you or someone external). Check items off as you verify behavior.  
**Environment:** run the app locally (`php artisan serve` + `npm run dev` or after `npm run build`).

---

## Two experiences (important)

| Area | Who | What it looks like |
|------|-----|-------------------|
| **Learning site** | Everyone (guests + students + instructors + admins when browsing) | Top nav: logo, **Browse**, **Forum**, **Dashboard** (if logged in), notifications, profile. Course **Library** grid, lesson pages with **series sidebar**, sign-in cards. **No** admin sidebar here. |
| **Admin panel** | Admins only (`/admin`) | **Separate layout**: **left sidebar** — **Overview**, **Courses** (list), **New course**, **Question bank**, **Forum categories**, **Tags**, **Back to learning site**, logout. Main content — **not** the same chrome as students. |

---

## Test accounts (after `php artisan migrate --seed`)

| Role | Email | Password |
|------|--------|----------|
| Admin | `admin@example.com` | `password` |
| Instructor | `instructor@example.com` | `password` |
| Student | `student@example.com` | `password` |

If seed was skipped because users already exist, either use these logins or run `php artisan migrate:fresh --seed` (wipes data).

**Seeded courses (demo student enrolled in both):**

| Course | Slug | Notes |
|--------|------|--------|
| **Laravel Foundations** | `laravel-foundations` | Short sample; modules, lesson quiz, module quiz. |
| **Learn HTML — Step by Step** | `html-complete` | Twelve lessons + [YouTube playlist](https://www.youtube.com/playlist?list=PLGwzwzSIMCmc_aR4lHJcxpNFoVuuc3UHs); W3Schools readings; lesson quizzes on lists / links / tables only; module recaps on modules 2 & 4. |


---

## 1. Accounts & access

| # | What to test | Expected |
|---|----------------|----------|
| 1.1 | Open `/` (home) without logging in | Redirects to **Browse** / course **Library**; published courses visible as cards. |
| 1.2 | Register a **new** user (unique email + handle) | Account created; you land on the **Dashboard** (“Continue learning”); profile exists. |
| 1.3 | **Sign in** / **Join** pages | Card-style forms; link to switch between sign-in and register. |
| 1.4 | Log out, then sign in again | Session works; you reach the dashboard. |
| 1.5 | Log in as **Student** | Learner nav only; **Admin panel** button **hidden**. |
| 1.6 | Log in as **Admin** (learning site) | **Admin panel** appears in the header (amber-style); opens **separate** admin UI with **sidebar**. |

---

## 2. Profiles & identity

| # | What to test | Expected |
|---|----------------|----------|
| 2.1 | Open **Profile** from the header (logged in) | Public profile page loads (`/u/{handle}`). |
| 2.2 | Open **Edit profile** | You can change display name, handle, bio; save succeeds. |
| 2.3 | Visit another user’s profile URL | Their display name and bio show; edit only for your own account. |

---

## 3. Courses & enrollment

| # | What to test | Expected |
|---|----------------|----------|
| 3.1 | **Library** lists seeded courses (**Laravel Foundations**, **Learn HTML — Step by Step**) | Card grid; title, blurb, **Enrolled** pill if applicable. |
| 3.2 | Open a course as **guest** | Hero + description; sign-in to enroll (or enroll if logged in). |
| 3.3 | Log in as **Student**, open course, **Start this course** / enroll | Success message; enrolled state + **completion %**. |
| 3.4 | **Dashboard** (“Continue learning”) | Enrolled courses as cards with **progress bar** and %. |
| 3.5 | Course page (enrolled) | Breadcrumb **Library → course**; modules as sections; **episode-style** lesson rows; **Module quiz** row when configured. |

---

## 4. Lessons (video + docs)

| # | What to test | Expected |
|---|----------------|----------|
| 4.1 | Open **first lesson** while enrolled | Breadcrumb **Library / course / lesson**; **Series** sidebar; YouTube embed; **Open video on YouTube** link if the iframe errors (e.g. bot check on localhost). |
| 4.2 | Markdown docs | Headings, bold, **code blocks**; syntax highlighting. |
| 4.3 | On a lesson **in order**, use **Mark as complete** (Udemy-style checkbox) | Checkbox saves; status shows **Completed**; sidebar / course outline show **✓** for done lessons. |
| 4.4 | Open a **later** lesson **before** completing earlier ones | Page **loads** (not 403). **Amber warning**; **Mark as complete** disabled (no tracking); no lesson quiz until in sequence. |
| 4.5 | Complete earlier lessons in order, then open the next | Checkbox, quizzes, and % progress behave normally. |

---

## 5. Quizzes & progress

| # | What to test | Expected |
|---|----------------|----------|
| 5.1 | **Lesson quiz** from lesson page (when in sequence) | Questions display; submit; grading. |
| 5.2 | Wrong answers | Not passed; can retry (unlimited attempts). |
| 5.3 | Correct answers above pass threshold | Passed; lesson quiz state updates where applicable. |
| 5.4 | **Module quiz** after all lessons in that module are complete | Quiz loads; pass/fail result. |
| 5.5 | Dashboard **completion %** | Updates as you complete steps **in order**. |

---

## 6. Forums

| # | What to test | Expected |
|---|----------------|----------|
| 6.1 | **Forum** in nav → index | Categories (e.g. General); thread counts if seeded. |
| 6.2 | Open a category | Thread list. |
| 6.3 | **New thread** (logged in) | Title + body; optional tags; thread appears. |
| 6.4 | Reply on a thread | Posts in order. |
| 6.5 | **Rate limit:** more than **5 forum posts** in one **calendar day** | Further posts blocked (validation message). |
| 6.6 | Lesson **comments** vs forum posts | Comments do **not** count toward the 5/day **forum** limit. |

---

## 7. Mentions & notifications

| # | What to test | Expected |
|---|----------------|----------|
| 7.1 | In forum or lesson comment, `@mentor` or `@admin` | Post saves. |
| 7.2 | Log in as **mentioned** user | **Notifications** shows an entry. |
| 7.3 | Open notification | Goes to relevant URL / context. |

---

## 8. Admin panel (admin only — separate UI)

| # | What to test | Expected |
|---|----------------|----------|
| 8.1 | From learning site, click **Admin panel** | URL under `/admin`; **sidebar** visible — **not** the same top nav as students. |
| 8.2 | Sidebar: **Overview**, **Courses**, **New course**, **Question bank**, **Forum categories**, **Tags** | Each navigates; active state on current section. |
| 8.3 | **Back to learning site** | Returns to **Browse** / library (`/courses` index). |
| 8.4 | **Overview** | **Counts** (courses, questions) + links to **course list** / **question bank** + forum shortcuts — **not** a duplicate course table (use **Courses** for the list). |
| 8.5 | **Courses** (`/admin/courses`) | Table: edit / delete; **New course**; pagination if many. |
| 8.6 | Create or **edit** a course | Save works; modules / lessons on edit page; optional **Danger zone** delete. |
| 8.7 | Add **module** + **lesson** (YouTube ref, duration, Markdown) | Lesson listed under module. |
| 8.8 | **Question bank** — list with **Edit** / **Delete**; **Add question** | Full CRUD on bank items. |
| 8.9 | Attach **lesson** / **module** quiz | Saves; learners see quizzes when rules allow. |
| 8.10 | **Forum categories** / **Tags** | Create items; they show on the **learning** forum. |
| 8.11 | **Student** or **Instructor** opens `/admin` | **403** (no access). |
| 8.12 | **Narrow / mobile width** | Admin **sidebar** stacks or scrolls; still usable. |

---

## 9. Roles sanity check

| # | What to test | Expected |
|---|----------------|----------|
| 9.1 | **Instructor** | No admin panel; forums/comments; community role only (no course authoring in UI). |
| 9.2 | **Student** | Full learning + forums + profile; **no** `/admin`. |

---

## 10. Edge cases (optional)

| # | What to test | Expected |
|---|----------------|----------|
| 10.1 | `php artisan db:seed` twice | Second run **skips** seed (no duplicate email crash). |
| 10.2 | Invalid login | Friendly error; no stack trace. |
| 10.3 | Mobile: **learning** pages | Bottom nav (Browse / Forum / Home); lesson layout usable. |
| 10.4 | Mobile: **admin** | Sidebar stacks; content readable. |

---

## How to record results

- **Pass / Fail** per section or row.  
- On **Fail**, note: browser, steps, and what you saw (screenshot optional).  
- For bugs, include **URL**, **role**, and whether you were on **learning site** vs **admin panel**.

---

*Aligned with the Technical LMS build: dual learner vs admin UX, auth, profiles, library, lessons, **mark-as-complete** progress (no second-by-second video tracking), quizzes, suggested order (soft gate for progress), forums, comments, mentions, in-app notifications, and admin CMS with course + question bank CRUD.*
