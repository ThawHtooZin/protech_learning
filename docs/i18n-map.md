# Internationalization (i18n) Map — English + Burmese (my)

## How it works

| Piece | Location |
| --- | --- |
| Available locales | [`config/lms.php`](../config/lms.php) → `locales.available` = `en`, `my` |
| Session locale | `SetLocale` middleware ([`app/Http/Middleware/SetLocale.php`](../app/Http/Middleware/SetLocale.php)) |
| Switch URL | `GET /locale/{locale}` → [`LocaleController`](../app/Http/Controllers/LocaleController.php) named `locale.switch` |
| UI switcher | [`resources/views/partials/locale-switcher.blade.php`](../resources/views/partials/locale-switcher.blade.php) in learn + admin layouts |
| App strings | [`lang/my.json`](../lang/my.json) — keys are English `__('…')` source strings |
| Framework strings | [`lang/my/auth.php`](../lang/my/auth.php), [`pagination.php`](../lang/my/pagination.php), [`passwords.php`](../lang/my/passwords.php), [`validation.php`](../lang/my/validation.php) |
| Compact Burmese CSS | `html.locale-my` + `[data-compact-my]` in [`resources/css/app.css`](../resources/css/app.css) |

Default locale remains **English** (`config/app.php` → `locale`). Choosing **မြန်** stores `my` in the session.

## Surface map (routes → UI)

### Public / auth
- `/` → courses index  
- `/login`, `/register`, `/approval/pending` — auth copy via `__()`  
- Locale switcher on learn layout (guests + users)

### Learner (auth + approved)
- `/dashboard`, `/courses`, `/courses/{course}`, `/lessons/{lesson}`  
- `/teams`, `/teams/{team}`, `/teams/{team}/assignments/*` (assignments, turn in, resources)  
- `/forums/*`, `/notifications`, `/profiles/*`  
- Nav labels: Browse, Forum, Teams, Dashboard, Notifications, Profile, Log out  

### Admin (`/admin/*`, role admin)
- Overview, Courses, Teams/Users, Monitoring, Forum categories/tags  
- Locale switcher in admin header; sidebar uses `data-compact-my`

## Adding a new string

1. Wrap UI text: `{{ __('English source') }}` or `__('…')` in controllers.  
2. Add the same English key to `lang/my.json` with a **short** Myanmar translation.  
3. Prefer compact words for nav/buttons (e.g. Teams → အဖွဲ့များ, Turn in → တင်မည်).  
4. Rebuild assets if CSS changed: `npm run build`.

## Burmese fit rules

- Translations in `my.json` are intentionally short.  
- When locale is `my`, `html.locale-my` slightly reduces base font and tightens nav/sidebar/dialog/table text.  
- Mark dense chrome with `data-compact-my` if a new nav region needs the same treatment.
