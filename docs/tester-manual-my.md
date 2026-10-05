# Protech LMS — စမ်းသပ်သူလမ်းညွှန် (မြန်မာ)

**ဒီဖိုင်ကို Tester ကို ပေးပါ။** တိုတိုရှင်းရှင်း။ Role အလိုက်။ Feature အားလုံး။ Pass / Fail ခြစ်ပါ။

---

## 0. ဒီစနစ်က ဘာလဲ

Protech LMS သည် **ဝက်ဘ်သင်ယူမှုစနစ် (LMS)** ဖြစ်သည်။

- Admin က **သင်တန်း (courses)** ထုတ်သည် (module → lesson၊ ဗီဒီယို + စာရွက်)။
- Student ကို Admin က **အတည်ပြု (approve)** ပြီး သင်တန်းမှာ **စာရင်းသွင်း (enroll)** ရမည်။
- Enrollment ရပြီးပါက lesson များကို **အစဉ်အတိုင်း မလို** — ကြည့်နိုင်သည်။
- **Teams**: Instructor/Admin က **ဖိုင် assignment** ပေးသည်။ Student တင်သည်။ Staff ပြန်ပေး (feedback)။
- Team **General**: Staff ပို့စ်တင်၊ Student ပြန်စာရေး။ `@handle` / `@all` က team အဖွဲ့ဝင်ကို အသိပေးသည်။
- ဘာသာစကား: **EN | မြန်**။

**မပါဝင် (မရှိလို့ bug မတင်ရ):** Quiz / မေးခွန်းဘဏ်၊ ငွေပေးချေမှု၊ လက်မှတ်၊ အမှတ်/rubric၊ မိုဘိုင်းအက်ပ်၊ ဖိုင်ဗိုင်းရပ်စစ်ဆေးမှု၊ ကိုယ်တိုင် password reset (Admin က password သတ်မှတ်ပေးသည်)။

---



## 1. စမ်းသပ်မီ ပြင်ဆင်ရန် (ပိုင်ရှင် / သင်)

1. App URL: [https://lms.protechmm.com](https://lms.protechmm.com)
2. လိုအပ်လျှင်: `php artisan migrate --seed`
3. **အကောင့် ၃ ခု** (အကြံပြု):


| Role       | Email                   | Password   | မှတ်ချက်                                                   |
| ---------- | ----------------------- | ---------- | ---------------------------------------------------------- |
| Admin      | `admin@gmail.com`       | `password` | Seed မူရင်း။ Public ဆို ပြောင်းပါ။                         |
| Instructor | `instructor@test.local` | `password` | Admin က ဖန်တီး → Approve → Team ထည့်။                      |
| Student    | `student@test.local`    | `password` | ဖန်တီး/Register → **Approve** → Course enroll → Team ထည့်။ |


1. Admin ဖြင့် တစ်ခါ ပြင်: **Published course** (≥2 lessons), **forum category ၁ ခု**, **team ၁ ခု** (instructor + student)။

အမှတ်: **P** = Pass, **F** = Fail, **N/A** = မရသေး။ Fail မှာ မှတ်ချက်ရေးပါ။

---



## 2. Guest / မျှဝေ


| #   | လုပ်ရမည့်အဆင့်               | မျှော်မှန်းရလဒ်                             | P/F |
| --- | ---------------------------- | ------------------------------------------- | --- |
| G1  | `/` ဖွင့်                    | `/courses` သို့ redirect                    |     |
| G2  | Logout နဲ့ `/courses`        | Published courses (သို့ အလွတ်)              |     |
| G3  | Published course ဖွင့်       | အသေးစိတ် + outline; enroll မရှိဘဲ lesson မရ |     |
| G4  | `/login`, `/register`        | Form ပေါ်                                   |     |
| G5  | Header မှာ **EN** / **မြန်** | UI စာသား ပြောင်း                            |     |
| G6  | Password မှား login          | Error; မဝင်ရ                                |     |
| G7  | Student အသစ် register        | Pending approval; dashboard မရ              |     |
| G8  | Logout                       | Guest nav (Sign in / Join)                  |     |


---



## 3. Admin အကောင့်

**Admin** ဖြင့် ဝင်။ CMS အတွက် `/admin`။ Learner nav လည်း သုံးနိုင်။

### 3.1 Admin shell နှင့် ဘာသာ


| #   | လုပ်ရမည့်အဆင့်         | မျှော်မှန်းရလဒ်                                      | P/F |
| --- | ---------------------- | ---------------------------------------------------- | --- |
| A1  | `/admin`               | Dashboard (counts, pending)                          |     |
| A2  | Admin မဟုတ်သူ `/admin` | **403**                                              |     |
| A3  | Sidebar လင့်များ       | အားလုံး ဖွင့်ရ                                       |     |
| A4  | EN ↔ မြန်              | စာသား ပြောင်း                                        |     |
| A5  | Back to learning site  | Learner nav အပြည့် (Browse, Forum, Teams, Dashboard) |     |




### 3.2 Users


| #   | လုပ်ရမည့်အဆင့်                 | မျှော်မှန်းရလဒ်                  | P/F |
| --- | ------------------------------ | -------------------------------- | --- |
| A10 | Teams → All users              | User စာရင်း                      |     |
| A11 | Account ဖန်တီး (Student)       | စာရင်းထဲ ပေါ်                    |     |
| A12 | Instructor ဖန်တီး              | Role = Instructor                |     |
| A13 | Pending user → **Approve**     | Dashboard ဝင်နိုင်               |     |
| A14 | **Revoke**                     | Learner မသုံးနိုင်; enroll ရှင်း |     |
| A15 | ပြန် Approve                   | အသုံးပြန်ရ                       |     |
| A16 | Role ပြောင်း                   | Update ဖြစ်                      |     |
| A17 | Course enroll ပေး              | Student က lesson ဖွင့်နိုင်      |     |
| A18 | Course ဖြုတ်                   | Lesson ဝင်ခွင့် ပျောက်           |     |
| A19 | Password reset → အသစ်နဲ့ login | အောင်မြင်                        |     |
| A20 | အခြား user ဖျက်                | ပျောက်                           |     |
| A21 | ကိုယ်ကိုယ်တိုင် ဖျက်           | တား                              |     |
| A22 | Admin တစ်ဦးတည်း ဖျက်ကြိုး      | တား / ဘေးကင်း                    |     |




### 3.3 Teams (Admin CMS)


| #   | လုပ်ရမည့်အဆင့်                                    | မျှော်မှန်းရလဒ်  | P/F |
| --- | ------------------------------------------------- | ---------------- | --- |
| A30 | Team ဖန်တီး                                       | Detail စာမျက်နှာ |     |
| A31 | Instructor + Student ထည့်                         | အုပ်စုခွဲ ပြ     |     |
| A32 | Member filter                                     | စစ်ထုတ်ရ         |     |
| A33 | Student ဖယ်                                       | Team က ထွက်      |     |
| A34 | Team အမည်ပြောင်း                                  | Update           |     |
| A35 | Team workspace ဖွင့်                              | Assignments      |     |
| A36 | Test team ဖျက်                                    | ပျောက်           |     |
| A37 | Instructor က `/admin/users` သို့ `/admin/courses` | **403**          |     |




### 3.4 Courses CMS


| #   | လုပ်ရမည့်အဆင့်                   | မျှော်မှန်းရလဒ်             | P/F |
| --- | -------------------------------- | --------------------------- | --- |
| A40 | Course ဖန်တီး (unpublished)      | ရှိ; public published မဟုတ် |     |
| A41 | Publish                          | `/courses` မှာ ပေါ်         |     |
| A42 | Module ထည့်                      | ရှိ                         |     |
| A43 | Lesson ထည့် (YouTube + markdown) | သိမ်း                       |     |
| A44 | Lesson ၂ခုမြောက်                 | စာရင်းမှာ နှစ်ခု            |     |
| A45 | Drag reorder                     | Refresh ပြီး အစဉ် တည်       |     |
| A46 | Lesson ပြင်                      | Update                      |     |
| A47 | Lesson ဖျက်                      | ပျောက်                      |     |
| A48 | Module အလွတ် ဖျက်                | ပျောက်                      |     |
| A49 | Unpublish                        | Catalog က ပျောက်            |     |
| A50 | Test course ဖျက်                 | ပျောက်                      |     |




### 3.5 Forum setup


| #   | လုပ်ရမည့်အဆင့်           | မျှော်မှန်းရလဒ်          | P/F |
| --- | ------------------------ | ------------------------ | --- |
| A60 | Category ဖန်တီး          | Admin + learner မှာ ပေါ် |     |
| A61 | Edit / reorder           | တည်                      |     |
| A62 | Tag ဖန်တီး               | စာရင်း                   |     |
| A63 | Tag ပြင်/ဖျက်            | အလုပ်လုပ်                |     |
| A64 | Thread ရှိ category ဖျက် | တား သို့ သတိပေး          |     |




### 3.6 Monitoring


| #   | လုပ်ရမည့်အဆင့်                                | မျှော်မှန်းရလဒ်        | P/F |
| --- | --------------------------------------------- | ---------------------- | --- |
| A70 | Student lesson ဖွင့်ပြီး → Monitoring Lessons | Event ပေါ်             |     |
| A71 | Forum လှုပ်ရှားမှု → Forums                   | Event ပေါ်             |     |
| A72 | Monitoring Courses                            | Event (ရှိလျှင်)       |     |
| A73 | User monitoring                               | User လှုပ်ရှားမှု မြင် |     |




### 3.7 Admin = learner


| #   | လုပ်ရမည့်အဆင့်             | မျှော်မှန်းရလဒ်           | P/F |
| --- | -------------------------- | ------------------------- | --- |
| A80 | Enroll မရှိဘဲ lesson ဖွင့် | Admin ဖွင့်နိုင်          |     |
| A81 | မည်သည့် team workspace     | Assignment/post စီမံနိုင် |     |
| A82 | Notifications / Profile    | အလုပ်လုပ်                 |     |


---



## 4. Instructor အကောင့်

**Instructor** ဖြင့် ဝင်။ Test team ထဲ ပါရမည်။

### 4.1 ဝင်ခွင့် ကန့်သတ်


| #   | လုပ်ရမည့်အဆင့်                             | မျှော်မှန်းရလဒ်                                         | P/F |
| --- | ------------------------------------------ | ------------------------------------------------------- | --- |
| I1  | `/admin`, `/admin/users`, `/admin/courses` | **403**                                                 |     |
| I2  | Learner nav အပြည့်                         | Browse, Forum, Teams, Dashboard, Notifications, Profile |     |
| I3  | Teams စာရင်း                               | ကိုယ်ပါသည့် team သာ                                     |     |
| I4  | မပါသည့် team ဖွင့်                         | **403**                                                 |     |




### 4.2 Team workspace UI


| #   | လုပ်ရမည့်အဆင့် | မျှော်မှန်းရလဒ်                                             | P/F |
| --- | -------------- | ----------------------------------------------------------- | --- |
| I10 | Team ဖွင့်     | Sidebar မီးခိုး အရောင်၊ အမြင့် ပြည့်; Assignments + General |     |
| I11 | Navbar         | Browse / Forum / Teams / Dashboard / Notifications ရှိ      |     |




### 4.3 Assignments (Staff)


| #   | လုပ်ရမည့်အဆင့်                       | မျှော်မှန်းရလဒ်        | P/F |
| --- | ------------------------------------ | ---------------------- | --- |
| I20 | **+** assignment ဖန်တီး (+ resource) | Detail ဖွင့်           |     |
| I21 | Upcoming / Past due / Completed      | Filter မှန်            |     |
| I22 | Edit                                 | Update                 |     |
| I23 | Student တင်ပြီး → ဖိုင် download     | ရ                      |     |
| I24 | **Return** + feedback                | Student မြင်; returned |     |
| I25 | **Close**                            | Student ပြန်တင် မရ     |     |
| I26 | Assignment ဖျက်                      | ပျောက်                 |     |
| I27 | Team မပါ Instructor ဖန်တီးကြိုး      | **403**                |     |




### 4.4 Members


| #   | လုပ်ရမည့်အဆင့်         | မျှော်မှန်းရလဒ်         | P/F |
| --- | ---------------------- | ----------------------- | --- |
| I30 | General → Add students | ဒီ team မှာသာ ထည့်နိုင် |     |
| I31 | Student ဖယ်            | အောင်မြင်               |     |
| I32 | Global user CMS        | မရှိ                    |     |




### 4.5 General ပို့စ်


| #   | လုပ်ရမည့်အဆင့်             | မျှော်မှန်းရလဒ်                     | P/F |
| --- | -------------------------- | ----------------------------------- | --- |
| I40 | ပို့စ်တင်                  | ပေါ်                                |     |
| I41 | `@all`                     | အခြားအဖွဲ့ဝင် notification; ကိုယ်မရ |     |
| I42 | `@studentHandle`           | အဲဒီ student ရ; team မပါသူ မရ       |     |
| I43 | Student က top-level ပို့စ် | မရ / **403**                        |     |


---



## 5. Student အကောင့်

**Approved Student**။ Course enroll ပြီး။ Test team မှာ ပါ။

### 5.1 Access & enrollment


| #   | လုပ်ရမည့်အဆင့်                      | မျှော်မှန်းရလဒ်              | P/F |
| --- | ----------------------------------- | ---------------------------- | --- |
| S1  | မ Approve သေး                       | Pending; dashboard မရ        |     |
| S2  | Approve ပြီး enroll မရှိ → lesson   | **403**                      |     |
| S3  | Enroll ပြီး lesson ၂ ကို တိုက်ရိုက် | **OK** — quiz/အစဉ် lock မရှိ |     |
| S4  | `/admin`                            | **403**                      |     |
| S5  | မပါသည့် team                        | **403**                      |     |




### 5.2 Lessons


| #   | လုပ်ရမည့်အဆင့်           | မျှော်မှန်းရလဒ်   | P/F |
| --- | ------------------------ | ----------------- | --- |
| S10 | ဗီဒီယို                  | Player ပေါ်       |     |
| S11 | Progress / watch         | သိမ်း / checkmark |     |
| S12 | Markdown docs            | ဖတ်ရ              |     |
| S13 | Outline အားလုံး နှိပ်ရ   | Locked မရှိ       |     |
| S14 | Comment                  | ပေါ်              |     |
| S15 | `@handle` comment        | Notification      |     |
| S16 | Quiz UI / `/quizzes/...` | မရှိ သို့ 404     |     |




### 5.3 Profile & password


| #   | လုပ်ရမည့်အဆင့်             | မျှော်မှန်းရလဒ်      | P/F |
| --- | -------------------------- | -------------------- | --- |
| S20 | Profile ပြင်               | `/u/{handle}` update |     |
| S21 | Avatar မှန်ကန်             | ပေါ်                 |     |
| S22 | Avatar မှား                | Validation           |     |
| S23 | Password ပြောင်း → ပြန်ဝင် | အောင်မြင်            |     |
| S24 | Current password မှား      | ငြင်း                |     |




### 5.4 Forums


| #   | လုပ်ရမည့်အဆင့်    | မျှော်မှန်းရလဒ်    | P/F |
| --- | ----------------- | ------------------ | --- |
| S30 | Forums → category | Thread စာရင်း      |     |
| S31 | Thread ဖန်တီး     | ဖန်တီးရ            |     |
| S32 | Reply             | ပေါ်               |     |
| S33 | `@handle`         | Notification       |     |
| S34 | Daily post limit  | Limit ကျော်ရင် တား |     |




### 5.5 Notifications


| #   | လုပ်ရမည့်အဆင့်      | မျှော်မှန်းရလဒ် | P/F |
| --- | ------------------- | --------------- | --- |
| S40 | Notifications ဖွင့် | စာရင်း          |     |
| S41 | Notification နှိပ်  | အရင်းသို့ သွား  |     |
| S42 | Mark all read       | Badge ရှင်း     |     |




### 5.6 Teams — Assignments


| #   | လုပ်ရမည့်အဆင့်          | မျှော်မှန်းရလဒ်                       | P/F |
| --- | ----------------------- | ------------------------------------- | --- |
| S50 | Assignments             | Open များ မြင်                        |     |
| S51 | Resource download       | ရ                                     |     |
| S52 | ဖိုင်/zip တင် → Turn in | Submitted                             |     |
| S53 | Open နေစဉ် ပြန်တင်      | ရ                                     |     |
| S54 | Returned ပြီး           | Feedback မြင်; ကိုယ့်ဖိုင် download ရ |     |
| S55 | Closed ပြီး ပြန်တင်     | မရ                                    |     |
| S56 | အခြား student ဖိုင် URL | **403**                               |     |
| S57 | Assignment ဖန်တီး       | မရ                                    |     |




### 5.7 Teams — General


| #   | လုပ်ရမည့်အဆင့်        | မျှော်မှန်းရလဒ် | P/F |
| --- | --------------------- | --------------- | --- |
| S60 | ပို့စ်ဖတ်             | မြင်            |     |
| S61 | Reply                 | ပေါ်            |     |
| S62 | `@handle` / `@all`    | Team ကို notify |     |
| S63 | Top-level ပို့စ်      | မရ              |     |
| S64 | Team စာမျက်နှာ navbar | Site နဲ့ တူ     |     |




### 5.8 Dashboard


| #   | လုပ်ရမည့်အဆင့် | မျှော်မှန်းရလဒ်          | P/F |
| --- | -------------- | ------------------------ | --- |
| S70 | Dashboard      | ဖွင့်ရ; enrolled courses |     |


---



## 6. Role နှိုင်းယှဉ် (အမြန်)


| လုပ်ဆောင်ချက်                  | Admin               | Instructor (team ပါ)   | Student (team ပါ) |
| ------------------------------ | ------------------- | ---------------------- | ----------------- |
| Admin CMS                      | ဟုတ်                | မဟုတ်                  | မဟုတ်             |
| Course/lesson ဖန်တီး           | ဟုတ်                | မဟုတ်                  | မဟုတ်             |
| Approve / enroll               | ဟုတ်                | မဟုတ်                  | မဟုတ်             |
| Team ဖန်တီး / role အားလုံးထည့် | ဟုတ်                | ကိုယ့် team student သာ | မဟုတ်             |
| Assignment ဖန်တီး              | ဟုတ် (မည်သည့် team) | ကိုယ့် team            | မဟုတ်             |
| Assignment တင်                 | မဟုတ်               | မဟုတ်                  | ဟုတ်              |
| Return                         | ဟုတ်                | ကိုယ့် team            | မဟုတ်             |
| General top post               | ဟုတ်                | ကိုယ့် team            | မဟုတ်             |
| General reply                  | ဟုတ်                | ဟုတ်                   | ဟုတ်              |
| Lesson ဖွင့်                   | အားလုံး             | Enroll ရှိမှ           | Enroll ရှိမှ      |


---



## 7. Bug တင်ပုံ

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


|             |                                    |
| ----------- | ---------------------------------- |
| Tester အမည် |                                    |
| ရက်စွဲ      |                                    |
| Build / URL |                                    |
| ရလဒ်        | ☐ Ready ☐ Not ready (Fail ID များ) |


---

**Docs စာရင်း:** [docs/README.md](./README.md) · English: [tester-manual-en.md](./tester-manual-en.md)