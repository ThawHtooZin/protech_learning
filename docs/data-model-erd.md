# Data model (conceptual ERD)

**Notation:** Mermaid `erDiagram`. Cardinalities use Crow’s-foot style labels. This is the **target logical model** for v1; column names may vary slightly in migrations.

This document is **only persistence** (tables, FKs). It does **not** describe UI. The learner vs admin **layout split** does not change this ERD.

---

## 1. Identity, roles, profiles

```mermaid
erDiagram
  users ||--o| profiles : "has"
  users {
    bigint id PK
    string email UK
    string password
    string role "admin|instructor|student"
    timestamps
  }
  profiles {
    bigint id PK
    bigint user_id FK "UK"
    string handle UK "for @mentions"
    string display_name
    text bio "nullable"
    string avatar_path "nullable"
    json social_links "nullable"
    timestamps
  }
```

- **Role** is stored on `users` (or a normalized `roles` table if you prefer; one role per user matches current plan).

---

## 2. Course structure and enrollment

```mermaid
erDiagram
  courses ||--o{ modules : contains
  modules ||--o{ lessons : contains
  users ||--o{ enrollments : "enrolls"
  courses ||--o{ enrollments : "has"
  courses {
    bigint id PK
    string title
    string slug UK
    text description "nullable"
    boolean is_published
    timestamps
  }
  modules {
    bigint id PK
    bigint course_id FK
    int sort_order
    string title
    timestamps
  }
  lessons {
    bigint id PK
    bigint module_id FK
    int sort_order
    string title
    string slug
    string video_driver "youtube|r2"
    string video_ref "youtube id or object key"
    longtext documentation_markdown
    timestamps
  }
  enrollments {
    bigint id PK
    bigint user_id FK
    bigint course_id FK
    unique user_course "user_id+course_id"
    timestamps
  }
```

- **Suggested lesson order:** the app may show lessons in order; **progress and lesson quizzes** apply only when earlier lessons in that order are complete. Learners can still **open** later lessons (preview); that does not write `lesson_progress` until they catch up in sequence.

---

## 3. Progress and checkpoints

```mermaid
erDiagram
  users ||--o{ lesson_progress : "tracks"
  lessons ||--o{ lesson_progress : "has"
  lesson_progress {
    bigint id PK
    bigint user_id FK
    bigint lesson_id FK
    int last_position_seconds "default 0"
    boolean started
    boolean watched
    boolean quiz_passed
    timestamp last_checkpoint_at "nullable"
    unique user_lesson "user_id+lesson_id"
    timestamps
  }
```

- **Completion:** learners set `watched` via **Mark as complete** (and lesson quizzes may set `quiz_passed`). `last_position_seconds` / `last_checkpoint_at` are legacy columns and are not driven by periodic video checkpoints in the current UI.

---

## 4. Assessment (question bank, quizzes, attempts)

```mermaid
erDiagram
  questions ||--o{ question_options : "has"
  quizzes ||--o{ quiz_questions : includes
  questions ||--o{ quiz_questions : "referenced_by"
  quizzes ||--o{ quiz_attempts : "taken"
  users ||--o{ quiz_attempts : "submits"
  quiz_attempts ||--o{ attempt_answers : "contains"
  questions ||--o{ attempt_answers : "answered"
  lessons ||--o| quizzes : "video_quiz_optional"
  modules ||--o| quizzes : "module_quiz_optional"
  questions {
    bigint id PK
    string technology
    string topic
    text body
    string type "mcq|..."
    timestamps
  }
  question_options {
    bigint id PK
    bigint question_id FK
    text body
    boolean is_correct
    int sort_order
  }
  quizzes {
    bigint id PK
    bigint lesson_id FK "nullable"
    bigint module_id FK "nullable"
    string title
    int pass_threshold_percent
    timestamps
  }
  quiz_questions {
    bigint id PK
    bigint quiz_id FK
    bigint question_id FK
    int sort_order
  }
  quiz_attempts {
    bigint id PK
    bigint user_id FK
    bigint quiz_id FK
    int score_percent
    boolean passed
    timestamps
  }
  attempt_answers {
    bigint id PK
    bigint quiz_attempt_id FK
    bigint question_id FK
    bigint selected_option_id FK "nullable"
    boolean is_correct
  }
```

- **Quiz scope:** Either `lesson_id` set (video-level quiz) **or** `module_id` set (module quiz), not both; enforce in app validation.

---

## 5. Community: forums

```mermaid
erDiagram
  forum_categories ||--o{ forum_threads : "contains"
  users ||--o{ forum_threads : "creates"
  forum_threads ||--o{ forum_posts : "has"
  users ||--o{ forum_posts : "writes"
  forum_threads }o--o{ tags : "tagged"
  forum_categories {
    bigint id PK
    string name
    string slug UK
    int sort_order
    timestamps
  }
  tags {
    bigint id PK
    string name
    string slug UK
    timestamps
  }
  forum_thread_tag {
    bigint forum_thread_id FK
    bigint tag_id FK
  }
  forum_threads {
    bigint id PK
    bigint forum_category_id FK
    bigint user_id FK
    string title
    string slug
    timestamps
  }
  forum_posts {
    bigint id PK
    bigint forum_thread_id FK
    bigint user_id FK
    longtext body
    timestamps
  }
```

- **Rate limit:** 5 **forum posts** per user per day — count rows in `forum_posts` (or a dedicated `forum_post_quota` log) per rolling or calendar window (implementation choice).

---

## 6. Lesson comments

```mermaid
erDiagram
  lessons ||--o{ lesson_comments : "has"
  users ||--o{ lesson_comments : "writes"
  lesson_comments ||--o{ lesson_comments : "replies_to"
  lesson_comments {
    bigint id PK
    bigint lesson_id FK
    bigint user_id FK
    bigint parent_id FK "nullable"
    longtext body
    timestamps
  }
```

---

## 7. Notifications (Laravel database channel)

```mermaid
erDiagram
  users ||--o{ notifications : "receives"
  notifications {
    uuid id PK
    string type "class name"
    morphs notifiable
    text data
    timestamp read_at "nullable"
    timestamps
  }
```

- **Mentions:** Store structured payload in `data` (e.g. `mentioned_user_id`, `source_type`, `source_id`, `actor_id`).

---

## Entity summary

| Area | Core entities |
|------|----------------|
| Identity | `users`, `profiles` |
| Learning | `courses`, `modules`, `lessons`, `enrollments`, `lesson_progress` |
| Assessment | `questions`, `question_options`, `quizzes`, `quiz_questions`, `quiz_attempts`, `attempt_answers` |
| Forums | `forum_categories`, `tags`, `forum_threads`, `forum_posts`, `forum_thread_tag` |
| UGC | `lesson_comments` |
| System | `notifications` |
