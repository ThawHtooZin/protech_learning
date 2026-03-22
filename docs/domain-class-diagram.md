# Domain class diagram (logical)

This diagram shows **domain models** and **key application services**, not every Laravel class (no `Controller`, `Request`, `FormRequest` explosion). PHP/Laravel naming is illustrative.

**Scope:** Domain and services only — **not** learner vs admin **view** classes (those are Blade layouts under `resources/views/layouts/`).

---

## 1. Core learning domain

```mermaid
classDiagram
  class User {
    +id
    +email
    +role
    +authenticate()
  }
  class Profile {
    +userId
    +handle
    +displayName
    +bio
    +avatarPath
    +socialLinks
  }
  class Course {
    +id
    +title
    +slug
    +isPublished
  }
  class Module {
    +id
    +courseId
    +sortOrder
    +title
  }
  class Lesson {
    +id
    +moduleId
    +sortOrder
    +title
    +videoDriver
    +videoRef
    +documentationMarkdown
  }
  class Enrollment {
    +userId
    +courseId
  }
  class LessonProgress {
    +userId
    +lessonId
    +lastPositionSeconds
    +started
    +watched
    +quizPassed
  }
  User "1" --> "0..1" Profile : owns
  User "1" --> "*" Enrollment : enrollments
  Course "1" --> "*" Module : modules
  Module "1" --> "*" Lesson : lessons
  Course "1" --> "*" Enrollment : enrollments
  User "1" --> "*" LessonProgress : progress
  Lesson "1" --> "*" LessonProgress : trackedBy
```

---

## 2. Video abstraction (switch YouTube ↔ R2)

```mermaid
classDiagram
  class VideoDriver {
    <<interface>>
    +playable(Lesson lesson) PlayableVideo
    +validateRef(string ref) bool
  }
  class YoutubeVideoDriver {
    +playable(Lesson)
    +validateRef()
  }
  class R2VideoDriver {
    +playable(Lesson)
    +validateRef()
  }
  class PlayableVideo {
    +kind "embed|html5"
    +embedHtml "nullable"
    +signedUrl "nullable"
    +mimeType "nullable"
  }
  class Lesson {
    +videoDriver
    +videoRef
  }
  VideoDriver <|.. YoutubeVideoDriver
  VideoDriver <|.. R2VideoDriver
  VideoDriver ..> PlayableVideo : returns
  YoutubeVideoDriver ..> Lesson
  R2VideoDriver ..> Lesson
```

- **Resolution:** A small factory reads `config` + `lesson.video_driver` and returns the correct driver.

---

## 3. Assessment domain

```mermaid
classDiagram
  class Question {
    +id
    +technology
    +topic
    +body
    +type
  }
  class QuestionOption {
    +questionId
    +body
    +isCorrect
    +sortOrder
  }
  class Quiz {
    +id
    +lessonId
    +moduleId
    +passThresholdPercent
  }
  class QuizAttempt {
    +userId
    +quizId
    +scorePercent
    +passed
  }
  class User {
    +id
  }
  Question "1" --> "*" QuestionOption : options
  Quiz "*" --> "*" Question : questions
  Quiz "1" --> "*" QuizAttempt : attempts
  User "1" --> "*" QuizAttempt : submits
```

*(Association class `QuizQuestion` / pivot omitted above; use pivot table in DB.)*

---

## 4. Community and notifications

```mermaid
classDiagram
  class ForumCategory {
    +id
    +name
    +slug
  }
  class Tag {
    +id
    +name
    +slug
  }
  class ForumThread {
    +id
    +categoryId
    +userId
    +title
  }
  class ForumPost {
    +id
    +threadId
    +userId
    +body
  }
  class LessonComment {
    +lessonId
    +userId
    +parentId
    +body
  }
  class MentionParser {
    +extractHandles(string body) string[]
  }
  class NotificationDispatcher {
    +notifyMentionedUsers()
  }
  ForumCategory "1" --> "*" ForumThread
  ForumThread "1" --> "*" ForumPost
  ForumThread }o--o{ Tag : tags
  Lesson "1" --> "*" LessonComment
  MentionParser ..> NotificationDispatcher : triggers
  User "1" --> "*" ForumThread : creates
  User "1" --> "*" ForumPost : writes
```

---

## 5. Rate limiting (forum)

```mermaid
classDiagram
  class ForumPostRateLimiter {
    +assertWithinDailyLimit(User) void
    +remaining(User) int
  }
  class ForumPost {
    +id
  }
  ForumPostRateLimiter ..> ForumPost : counts
  ForumPostRateLimiter ..> User
```

---

## Service vs model summary

| Layer | Responsibility |
|-------|------------------|
| **Eloquent models** | Persistence, relationships, casts |
| **VideoDriver** | Hide YouTube vs R2 details from views |
| **MentionParser + NotificationDispatcher** | Parse `@handle`, create Laravel `Notification` records |
| **ForumPostRateLimiter** | Enforce 5 forum posts / user / day |
| **Quiz grading** | Transactional scoring inside an action/service |
