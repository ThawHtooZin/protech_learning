# Component diagram (logical)

High-level **logical components** of the Technical LMS monolith and their **dependencies**. Deployment is a **single Laravel application** (one deployable); components are **bounded contexts** inside the codebase.

**UI split (implementation):** learners and guests use the **learner shell** (course library, lessons, forums, auth). **Admins** use a **separate admin shell** (sidebar navigation under `/admin`) for CMS work. Both are Blade + Tailwind; they share routes/controllers and DB — only layouts and navigation differ.

---

## 1. System context (external actors)

```mermaid
flowchart LR
  subgraph actors [Actors]
    Student[Student]
    Instructor[Instructor]
    Admin[Admin]
    Guest[Guest]
  end
  subgraph system [Technical LMS]
    WebApp[Laravel Web App]
  end
  subgraph external [External systems]
    YouTube[YouTube Embed]
    MySQL[(MySQL)]
  end
  Student --> WebApp
  Instructor --> WebApp
  Admin --> WebApp
  Guest --> WebApp
  WebApp --> YouTube
  WebApp --> MySQL
```

---

## 2. Internal components and dependencies

```mermaid
flowchart TB
  subgraph presentation [Presentation]
    LearnUI[Learner layout Blade + Tailwind]
    AdminUI[Admin layout Blade + sidebar]
    Alpine[Alpine.js widgets]
  end
  subgraph http [HTTP Layer]
    WebRoutes[Web routes + middleware]
    Controllers[Controllers]
  end
  subgraph identity [Identity and Access]
    Auth[Auth + sessions]
    RBAC[Roles + policies]
  end
  subgraph learning [Learning]
    CourseCatalog[Course catalog + enrollment]
    LessonDelivery[Lesson page + Markdown + video driver]
    ProgressEngine[Progress + checkpoints]
    Gating[Module gating rules]
  end
  subgraph assessment [Assessment]
    QuestionBank[Question bank admin]
    QuizEngine[Quizzes + attempts + grading]
  end
  subgraph community [Community]
    Forums[Forums + categories + tags]
    LessonComments[Lesson comments]
    RateLimit[Forum daily rate limit]
  end
  subgraph social [Social]
    Profiles[User profiles + handles]
    Mentions[Mention parse + notify]
    NotifInApp[In-app notifications]
  end
  subgraph infra [Infrastructure]
    DB[(MySQL)]
    Cache[Cache optional]
  end
  LearnUI --> WebRoutes
  AdminUI --> WebRoutes
  Alpine --> WebRoutes
  WebRoutes --> Controllers
  Controllers --> Auth
  Controllers --> RBAC
  Controllers --> CourseCatalog
  Controllers --> LessonDelivery
  Controllers --> ProgressEngine
  Controllers --> Gating
  Controllers --> QuestionBank
  Controllers --> QuizEngine
  Controllers --> Forums
  Controllers --> LessonComments
  Controllers --> RateLimit
  Controllers --> Profiles
  Controllers --> Mentions
  Controllers --> NotifInApp
  CourseCatalog --> DB
  LessonDelivery --> DB
  ProgressEngine --> DB
  Gating --> DB
  QuestionBank --> DB
  QuizEngine --> DB
  Forums --> DB
  LessonComments --> DB
  Profiles --> DB
  NotifInApp --> DB
  Mentions --> NotifInApp
  Forums --> RateLimit
  LessonDelivery --> YouTubeEmbed[YouTube API surface]
  Gating --> ProgressEngine
  Gating --> QuizEngine
```

**Notes:**

- **Learner vs admin:** `LearnUI` serves public and student flows; `AdminUI` serves `role:admin` routes only. Same **Controllers** behind both.
- **YouTube** is not a first-class “component” inside the repo; **LessonDelivery** uses **VideoDriver** (see domain class diagram) which calls out to YouTube embed (v1).
- **R2** appears later as another **VideoDriver** implementation; **LessonDelivery** stays stable.

---

## 3. Dependency direction (rules of thumb)

| From | To | Rationale |
|------|-----|-----------|
| Controllers | Domain services / actions | Thin HTTP layer |
| Gating | Progress + Quiz outcomes | Unlock uses completion state |
| Forums | RateLimit | Posts must pass limit before persist |
| Mentions | NotifInApp | Mentions create notification rows |
| All data components | MySQL | Single source of truth |

---

## 4. Optional future components (not v1)

- **Email / queue** for notifications.
- **Certificate generator** (PDF).
- **DRM / transcoding** pipeline for video.
