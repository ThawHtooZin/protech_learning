# Protech LMS — Master Test Cases

**Single source of truth for QA.** Role-by-role cases + detailed course-access/commerce cases.

| | |
| --- | --- |
| Mark | **P** Pass · **F** Fail · **N/A** blocked / not set up |
| App | Local / staging / [https://lms.protechmm.com](https://lms.protechmm.com) |
| Related | [admin-master-guide.md](./admin-master-guide.md) (ops) · flow docs under `docs/flows/` (sequences) |

**Merged from:** `tester-manual-en.md`, `admin-master-guide` smoke list, `phase-4-epic-2-3-course-access-test-cases.md`, assignment / General / quiz-decouple checklists.

---

## 0. Product snapshot

- Admins publish **courses** (modules → lessons: video + docs).
- Courses are **public** (bank-slip buy → admin approve) or **private** (grant person/team only).
- Students must be **approved**. Access = admin **or** enrollment **or** team linked via `team_course`.
- Teams: assignments (any file type) + General posts (`@handle` / `@all`).
- Language: **EN | မြန်**.
- Alerts: `ProtechAlert` toasts/confirms.

**Out of scope (not bugs):** quizzes/question bank, auto payment gateway, trailers/preview unlock, certificates, coupons, grades/rubrics, mobile app, virus scan, self-service password reset, “contact admin” copy.

---

## 1. Setup

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| SETUP-01 | `php artisan migrate` · `php artisan storage:link` | Schema OK; covers via `/storage` | |
| SETUP-02 | `npm run build` (or `npm run dev`) | Grant modal, Buy modal, ProtechAlert work | |
| SETUP-03 | Seed or create Admin | `admin@gmail.com` / `password` (change if public) | |
| SETUP-04 | Create Instructor + Student; approve both | Both can log in | |
| SETUP-05 | 1 team with instructor + student | Team workspace usable | |
| SETUP-06 | 1 public published course (≥2 lessons) + price + bank account | Buy path ready | |
| SETUP-07 | 1 private listed course + 1 private unlisted course | Catalog/access edges ready | |
| SETUP-08 | 1 forum category (+ optional tag) | Forums ready | |

**Recommended accounts**

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@gmail.com` | `password` |
| Instructor | `instructor@test.local` | `password` |
| Student | `student@test.local` | `password` |

---

## 2. Automated tests (quick)

```bash
php artisan test --filter='CourseAccessModelTest|CourseCommerceUxTest|TeamAssignmentTest|TeamGeneralPostTest|LessonSequenceGatingTest|LocaleSwitchTest'
```

| Suite | Area |
| --- | --- |
| `CourseAccessModelTest` | Public/private, purchase, team grant, unlisted 404 |
| `CourseCommerceUxTest` | Banks in Buy modal, cover, grant/revoke notis, catch-up |
| `TeamAssignmentTest` | Assignments |
| `TeamGeneralPostTest` | General posts / mentions |
| `LessonSequenceGatingTest` | Enrollment required for lessons |
| `LocaleSwitchTest` | EN/MY switch |

---

## 3. Guest / shared

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| G1 | Open `/` | Redirect → `/courses` | |
| G2 | Catalog logged out | Published public + listed private only | |
| G3 | Open published public course | Outline locked; **Sign in to buy** | |
| G4 | Open listed private course | Outline + Private; **no** Buy / contact copy | |
| G5 | Open unlisted private course | **404** | |
| G6 | `/login`, `/register` | Forms load | |
| G7 | EN → မြန် | Labels switch | |
| G8 | Wrong password | Error; stay logged out | |
| G9 | Register unique student | Approval pending; no dashboard | |
| G10 | Logout | Guest nav | |

---

## 4. Admin — shell & users

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| A1 | Open `/admin` | Counts, pending, links | |
| A2 | Non-admin → `/admin` | **403** | |
| A3 | Sidebar: Overview, Courses, Purchases, Bank accounts, Teams, Monitoring, Forum categories, Tags | All open | |
| A4 | Admin EN ↔ မြန် | Labels change | |
| A5 | Back to learning site | Learner chrome | |
| A10 | Teams → All users | User list | |
| A11 | Create Student (dialog) | Created; listed | |
| A12 | Create Instructor | Role Instructor | |
| A13 | Approve pending user | Can use dashboard | |
| A14 | Revoke student | Learner blocked; enrollments cleared | |
| A15 | Re-approve | Access restored (re-grant courses if needed) | |
| A16 | Change role Student ↔ Instructor | Updates | |
| A17 | User page has **no** Course access checkboxes | Grant only on course | |
| A18 | Reset password → login as user | Works | |
| A19 | Delete non-admin user | Gone | |
| A20 | Delete self | Blocked | |
| A21 | Delete last admin | Blocked / safe | |

---

## 5. Admin — teams CMS

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| A30 | Create team | Detail opens | |
| A31 | Add instructors + students | Grouped correctly | |
| A32 | Filter members | Filters | |
| A33 | Remove student | Detached | |
| A34 | Rename team | Updates | |
| A35 | Open team workspace | `/teams/{id}` | |
| A36 | Delete test team | Removed | |

---

## 6. Admin — courses CMS (structure + media)

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| A40 | Create unpublished course | Not on catalog as published | |
| A41 | Access Private (default) | Saves without price | |
| A42 | Access Public, empty price → save | Validation error | |
| A43 | Public + price + publish | On catalog with MMK | |
| A44 | Switch Public→Private → save | Price cleared; no price on learner UI | |
| A45 | Private + Listed off | Unlisted (404 strangers) | |
| A46 | Quill description: bold + list → save | HTML renders on show | |
| A47 | Paste `<script>…</script>` in description | Script stripped | |
| A48 | Upload cover (JPG/PNG/WebP ≤5MB) | Show/catalog/dashboard | |
| A49 | Remove cover | Gone | |
| A50 | Cover wrong type / too large | Validation error | |
| A51 | Add module + 2 YouTube lessons + docs | Saved | |
| A52 | Drag reorder modules/lessons | Persists after refresh | |
| A53 | Edit / delete lesson | Updates; order OK | |
| A54 | Delete empty module | Gone | |
| A55 | Unpublish | Hidden from catalog | |
| A56 | Delete course | Removed | |
| A57 | Form has no team checkbox list | Grant via modal only | |

---

## 7. Admin — bank accounts

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| A60 | `/admin/banks` add KBZPay | Created; toast | |
| A61 | Add second bank (Wave) | Both active | |
| A62 | Edit account number | Saved | |
| A63 | Deactivate one bank | Not in Buy modal | |
| A64 | Sort order 0 then 2 | Lower sort first in Buy | |
| A65 | Delete bank (+ confirm) | Removed | |
| A66 | Empty required fields | Validation | |
| A67 | Instructor → `/admin/banks` | **403** | |

---

## 8. Admin — Grant access modal

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| A70 | Courses index → **Grant access** | Modal; course title | |
| A71 | Course edit → **Grant access** | Same | |
| A72 | Search + select user → Save | Enrollment; grant noti | |
| A73 | Select team → Save | `team_course`; members can open lessons; team grant noti | |
| A74 | Add new student to granted team later | Live access (no re-save) | |
| A75 | Remove user chip → Save | Enrollment gone; **revoke** noti | |
| A76 | Remove team → Save | Detached; team **revoke** noti | |
| A77 | Search filters list | Matches only | |
| A78 | Long list scrolls inside modal | Footer Save always usable | |
| A79 | Keyboard ↑↓ + Enter | Highlight / add | |
| A80 | Many chips | Chip area scrolls | |
| A81 | Admins / unapproved not in picker | Hidden | |
| A82 | Clear all → Save | Access list empty for course | |
| A83 | Cancel after changes | No DB change | |
| A84 | Instructor POST access URL | **403** | |

---

## 9. Admin — purchases

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| A90 | Student submitted slip → `/admin/purchases` | Pending listed | |
| A91 | View slip | File opens/downloads | |
| A92 | Approve | Enrollment + student unlock + approve noti | |
| A93 | Reject other pending | Student locked; can buy again | |
| A94 | Approve twice | Already reviewed / safe | |
| A95 | Non-admin purchases UI | **403** | |

---

## 10. Admin — forums & monitoring

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| A100 | Create / edit / reorder forum category | Persists; learner forums show | |
| A101 | Create / edit / delete tag | Works | |
| A102 | Delete category with threads | Blocked or safe | |
| A110 | Student opens lesson → Monitoring Lessons | Event | |
| A111 | Forum activity → Monitoring Forums | Event | |
| A112 | Monitoring Courses | Events if any | |
| A113 | Per-user monitoring | Visible | |

---

## 11. Admin as learner

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| A120 | Open any published lesson without enrollment | OK (admin bypass) | |
| A121 | Manage any team assignments / General | OK | |
| A122 | Notifications / profile | Work | |
| A123 | Flash save → ProtechAlert toast | Toast (not only old banner) | |

---

## 12. Catalog & course show (all roles)

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| C1 | Public published on catalog | Price badge if set | |
| C2 | Private listed on catalog | **Private** badge; no price | |
| C3 | Private unlisted | Not on catalog | |
| C4 | Draft | Not on catalog | |
| C5 | Cover on catalog card | Shows | |
| C6 | Logged-in with access | Access indicator on card | |
| C7 | Episode · minutes meta | Correct | |
| C8 | No marketing blurb under Library | Title-focused | |
| C10 | Public no access: locked outline | Titles + lock; not clickable | |
| C11 | Public + access: links + completion % | Playable | |
| C12 | Public Buy → modal | Banks + amount + slip upload | |
| C13 | Guest public | Sign in to buy only | |
| C14 | Private no access | No Buy; no contact-admin copy | |
| C15 | HTML description renders | Bold/lists | |
| C16 | Unlisted + access | 200 unlocked | |
| C17 | Unlisted stranger | 404 | |
| C18 | No trailer / free preview | Locked stays locked | |
| C19 | Empty module | Empty state | |

---

## 13. Student — access, buy, lessons, dashboard

Sign in as **approved Student**.

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| S1 | Unapproved temp account | Pending; no dashboard | |
| S2 | No access → lesson URL | **403** | |
| S3 | After personal grant → any lesson order | OK (no quiz lock) | |
| S4 | `/admin` | **403** | |
| S5 | Team not member | **403** | |
| S10 | Public Buy → upload valid slip | Purchase pending | |
| S11 | Pending → lesson | **403**; pending badge; no second Buy | |
| S12 | After admin approve | Lessons unlock; noti | |
| S13 | Invalid slip type/size | Validation | |
| S14 | Private course POST purchase | **404** | |
| S15 | Double pending | Still one pending | |
| S16 | Already has access → purchase | No new pending | |
| S20 | Team-granted course | Lessons OK; on dashboard | |
| S21 | Access revoked (person) | Lesson 403; revoke noti | |
| S22 | Team revoked but personal enroll remains | Still access | |
| S23 | Personal revoked but team remains | Still access | |
| S30 | Video plays | YouTube visible | |
| S31 | Progress / watched threshold | Saves; completion updates | |
| S32 | Markdown docs | Readable | |
| S33 | Lesson comment + `@handle` | Comment + noti | |
| S34 | No quiz UI / `/quizzes` | Hidden/404 | |
| S40 | Dashboard courses | Enrolled + team-granted | |
| S41 | Dashboard has **no** Course access block | Notis only under Notifications | |
| S42 | Empty dashboard | Browse CTA | |
| S50 | Notifications list | Mentions + access grant/revoke | |
| S51 | Open notification | Goes to course/source | |
| S52 | Mark all read | Badge clears | |
| S53 | New session + unread | Catch-up toast once; same session no repeat | |
| S54 | Logout/login still unread | Catch-up can show again | |
| S60 | Edit profile + avatar | Saves; `/u/{handle}` | |
| S61 | Bad avatar | Validation | |
| S62 | Change password | Re-login works | |

---

## 14. Instructor

Must be on test team.

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| I1 | `/admin`, `/admin/users`, `/admin/courses`, `/admin/banks`, `/admin/purchases` | **403** | |
| I2 | Learner nav | Browse, Forum, Teams, Dashboard, Notifications, Profile | |
| I3 | Teams index | Only own teams | |
| I4 | Other team | **403** | |
| I10 | Team sidebar | Assignments + Channels → General | |
| I20 | Create assignment (+ optional resources) | Created | |
| I21 | Tabs Upcoming / Past due / Completed | Filter OK | |
| I22 | Edit assignment | Updates | |
| I23 | Student turns in → download | Works | |
| I24 | Return + feedback | Student sees feedback | |
| I25 | Close | Student cannot upload | |
| I26 | Delete assignment | Removed | |
| I27 | Create on team not member | **403** | |
| I30 | General → add/remove students | Own team only | |
| I40 | General post | Appears | |
| I41 | `@all` | Members notified; author not | |
| I42 | `@studentHandle` | That member notified; outsider not | |
| I43 | Student top-level post | Not allowed | |

---

## 15. Student — teams (assignments + General)

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| T1 | Assignments list | Sees open work | |
| T2 | Download resources | OK | |
| T3 | Attach any type (e.g. `.zip`, `.py`) → Turn in | Submitted | |
| T4 | Replace while open | Allowed | |
| T5 | After return | Feedback + own files | |
| T6 | After close | Upload blocked | |
| T7 | Other student’s file URL | **403** | |
| T8 | Create assignment | No / **403** | |
| T10 | Read General | Visible | |
| T11 | Reply | Appears | |
| T12 | Reply `@instructor` / `@all` | Notifies | |
| T13 | Top-level post | Not allowed | |
| T14 | Full navbar on team pages | Present | |

---

## 16. Forums (student)

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| F1 | Forums → category | Threads | |
| F2 | Create thread (+ tag) | Created | |
| F3 | Reply | Shows | |
| F4 | Mention `@handle` | Notification | |
| F5 | Daily post limit (if configured low) | Blocked after limit | |

---

## 17. Cross-role matrix

| Action | Admin | Instructor (on team) | Student (on team) |
| --- | --- | --- | --- |
| Admin CMS | Yes | No | No |
| Create course / banks / purchases | Yes | No | No |
| Grant course access (modal) | Yes | No | No |
| Approve users | Yes | No | No |
| Create team / add any role | Yes | Add students on own team | No |
| Create assignment | Any team | Own team | No |
| Submit assignment | No* | No | Yes |
| Return assignment | Yes | Own team | No |
| Top-level General post | Yes | Own team | No |
| Reply General | Yes | Yes | Yes |
| Open lesson | Always (published) | If access | If access |
| Buy public course | N/A | If student role + no access | Yes |

\*Admin may use learner UI but assignments are student-facing.

---

## 18. Security / edge quick list

| # | Steps | Expected | P/F |
| --- | --- | --- | --- |
| X1 | Slip storage URL guessed | Not public | |
| X2 | Cover URL | Public `/storage` | |
| X3 | Purchase on unpublished | 404 | |
| X4 | HTML XSS in description | No script execution | |
| X5 | ProtechAlert confirm Cancel on delete | Aborts | |

---

## 19. Smoke path (30 min)

Use when time is short; still mark full IDs when doing a release.

1. [ ] Admin login · locale switch · `/admin`
2. [ ] Create/publish public course + cover + rich desc + bank
3. [ ] Grant private course to person + team; revoke one → notis
4. [ ] Student buy slip → admin approve → lesson unlock
5. [ ] Unlisted private 404 for stranger
6. [ ] Team assignment turn-in → return → close
7. [ ] General `@all` noti
8. [ ] Forum thread + mention
9. [ ] Instructor 403 on `/admin/courses`
10. [ ] Session catch-up toast once with unread
11. [ ] Dashboard shows courses; **no** Course access block

---

## 20. Bug report template

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

## 21. Sign-off

| | |
| --- | --- |
| Tester name | |
| Date | |
| Build / URL | |
| Automated suite | ☐ Green ☐ Failures (list) |
| Result | ☐ Ready ☐ Not ready (list Fail IDs) |

---

## 22. Doc map (detail elsewhere)

| Doc | Use for |
| --- | --- |
| [flows/phase-4-epic-2-course-access-sequence.md](./flows/phase-4-epic-2-course-access-sequence.md) | Access sequence diagrams |
| [flows/phase-4-epic-3-course-commerce-ux-sequence.md](./flows/phase-4-epic-3-course-commerce-ux-sequence.md) | Commerce UX sequence |
| [flows/phase-4-epic-1-team-assignments-sequence.md](./flows/phase-4-epic-1-team-assignments-sequence.md) | Assignment sequence |
| [flows/phase-4-epic-2-team-general-posts-sequence.md](./flows/phase-4-epic-2-team-general-posts-sequence.md) | General posts sequence |
| [admin-master-guide.md](./admin-master-guide.md) | How to operate as admin |

Detailed CA-* IDs from the old course-access-only sheet are folded into sections **6–9, 12–13, 18–19** above.
