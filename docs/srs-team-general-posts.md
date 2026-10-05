# Software Requirements Specification — Team General Posts

## Phase 4, Epic 2 — Team General Posts (SRS)

**Status:** Implemented. See [`docs/flows/phase-4-epic-2-team-general-posts-sequence.md`](./flows/phase-4-epic-2-team-general-posts-sequence.md).

---

## 1. Purpose

Give each team a **General** channel feed (Microsoft Teams–style) and restore the **global learner navbar** on team workspace pages.

---

## 2. Scope

### In scope

- Shared learner navbar on `layouts.team` (Browse, Forum, Teams, Dashboard, Notifications, Profile)
- Top-level posts by **admin** or **instructor on that team**
- Replies by any **team member** or **admin**
- `@handle` and `@all` notifications, limited to team members
- Existing members / add-students UI on General remains

### Out of scope

- Real-time chat, file attachments on posts, mention typeahead
- Student-created top-level posts
- Edit/delete posts
- Mentions on assignment pages

---

## 3. Functional requirements

- **FR-TG1:** Team workspace uses the same learner header as the rest of the LMS.
- **FR-TG2:** Instructor members and admins may create top-level posts on General.
- **FR-TG3:** Students on the team may reply to top-level posts; they may not create top-level posts.
- **FR-TG4:** `@all` notifies every other team member (not the author).
- **FR-TG5:** `@handle` notifies that user only if they are on the team.
- **FR-TG6:** Non-members cannot view General or reply (admins may).
