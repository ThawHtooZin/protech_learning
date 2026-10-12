# Phase 4, Epic 5 — Channel posts (Teams-style) + reactions

## Summary

Channel feed matches Microsoft Teams Posts: **Start a post** composer (optional subject + Quill rich body + banner theme Auto/pick/none), colored **announcement header** when a subject is set, conversation cards with avatar / name / time, emoji **reactions on the top-right** of the post, flatter nested **replies** below a separator, and an inline reply bar. Staff can create top-level posts; all team members can reply and react.

## Sequence

```mermaid
sequenceDiagram
    participant Staff as Instructor / Admin
    participant Member as Team member
    participant Web as TeamPostController
    participant Access as TeamAccessService
    participant DB as team_posts / team_post_reactions

    Staff->>Web: POST .../channels/{channel}/posts (subject, body)
    Web->>Access: canPostOnChannel?
    Web->>DB: insert top-level post
    Web-->>Staff: Redirect + #team-post-{id}

    Member->>Web: POST .../posts/{post}/replies (body)
    Web->>Access: canReplyOnChannel?
    Web->>DB: insert reply (parent_id)
    Web-->>Member: Redirect + fragment

    Member->>Web: POST .../posts/{post}/reactions (emoji)
    Web->>Access: canReplyOnChannel?
    alt already reacted with emoji
        Web->>DB: delete reaction
    else new
        Web->>DB: insert reaction
    end
    Web-->>Member: Redirect + fragment
```

## Manual test checklist

1. Open a channel as instructor → **Start a post** → subject + message → **Post**. Card shows subject headline, avatar, timestamp.
2. As student → reply via bottom bar (paper-plane). Reply appears under the post.
3. Click 😀 → pick 👍 / ❤️ → count badge appears; click again to remove.
4. Student cannot see **Start a post**; can still reply + react.
5. Non-member cannot view channel or react (403).

## DB checks

```sql
SELECT id, subject, parent_id, body FROM team_posts ORDER BY id DESC LIMIT 20;
SELECT * FROM team_post_reactions ORDER BY id DESC LIMIT 20;
```
