# Phase 1, Epic - Teams Admin UX (Teams-like layout)

## Goal
Make admin team management easier by mirroring Microsoft Teams interaction patterns: pick a team from a left rail, manage only that team in a detail pane, add members via search dialog, and remove members one at a time.

## Sequence

```mermaid
sequenceDiagram
    actor Admin
    participant UI as Admin Users / Teams tab
    participant UsersCtrl as UserAdminController
    participant TeamsCtrl as TeamAdminController
    participant DB as teams / team_user

    Admin->>UI: Open /admin/users (default tab = teams)
    UI->>UsersCtrl: index(?tab=teams&team=?)
    UsersCtrl->>DB: Load teams + members
    UsersCtrl-->>UI: Rail list + selected team detail

    Admin->>UI: Click + Create
    UI->>TeamsCtrl: POST /admin/teams {name}
    TeamsCtrl->>DB: Insert team
    TeamsCtrl-->>UI: Redirect ?tab=teams&team={id}

    Admin->>UI: Click team in rail
    UI->>UsersCtrl: GET ?tab=teams&team={id}
    UsersCtrl-->>UI: Focus that team detail

    Admin->>UI: Add members (search + select)
    UI->>TeamsCtrl: POST /admin/teams/{team}/members
    TeamsCtrl->>DB: syncWithoutDetaching(instructor/student ids)
    TeamsCtrl-->>UI: Redirect focused team

    Admin->>UI: Remove member row
    UI->>TeamsCtrl: DELETE /admin/teams/{team}/members/{user}
    TeamsCtrl->>DB: detach user
    TeamsCtrl-->>UI: Redirect focused team

    Admin->>UI: Manage → rename / delete
    UI->>TeamsCtrl: PUT or DELETE team
    TeamsCtrl->>DB: update or delete
    TeamsCtrl-->>UI: Redirect teams tab
```

## Manual test checklist

1. Sign in as admin and open **Users**.
2. Confirm **Teams** is the default tab and shows a left team list + right detail panel (not stacked cards).
3. Click **+ Create**, enter a team name, submit — new team appears selected in the rail.
4. Open **Add members**, search an instructor and a student, add them — they appear in the Members list with role badges.
5. Use **Filter members** to narrow the list.
6. Click **Remove** on one member — they leave the team but remain in **All users**.
7. Use **Manage → Save name** to rename; confirm rail label updates.
8. Delete a team; confirm members still exist under **All users**.
9. Open **All users** and confirm each person’s Teams column still lists memberships.
10. Sign in as a non-admin and confirm team create/add/remove routes are forbidden.

## Notes
- Role still comes from the user account (Instructor / Student), not from the team pivot.
- Attach validates role fields (`instructor_ids` / `student_ids`) the same way as before.
- Commerce, course-to-team mapping, and learner-facing Teams UI remain out of scope for this epic.
