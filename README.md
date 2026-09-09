# CodeIgniter 4 Base Project

A reusable CodeIgniter 4 API starter with **Shield JWT authentication** and **Collab keylink login**, extracted from the outgoing exchange management backend.

Use this as the starting point for new CI4 projects instead of copying auth code each time.

## What's included

- CodeIgniter 4.7 + Shield + CORS + Tasks
- Staff JWT auth (`POST /api/auth/token`, `GET /api/auth/user`)
- Collab keylink → username resolution (production)
- Dev username bypass (non-production)
- `AppJwtAuth` filter for protected routes
- `BaseController` with common CRUD helpers
- `BaseModel`, `UserModel`, `CollabModel`
- Shield migrations + custom `users` columns migration
- Scheduled tasks via `codeigniter4/tasks` (`app/Config/Tasks.php`)

## What's NOT included

- Student auth
- Domain-specific controllers, services, or business logic
- Email templates or notification services

## Quick start

### 1. Copy environment file

```bash
copy env .env
```

Edit `.env` with your database credentials, Collab connection, JWT secret, and dev username.

Generate a JWT secret:

```bash
php -r "echo bin2hex(random_bytes(32));"
```

### 2. Install dependencies

```bash
composer install
```

### 3. Run migrations

Create your database, then:

```bash
php spark migrate --all
```

This creates Shield auth tables, Settings tables (required by Tasks), and adds `display_name` / `user_email` to `users`.

### 4. Create a dev user

```bash
php spark shield:user create -n devuser -e dev@example.com
```

Set `auth.devUsername = devuser` in `.env` to match.

### 5. Start the server

```bash
php spark serve
```

### 6. Test auth

**Get token (development):**

```bash
curl -X POST http://localhost:8080/api/auth/token -H "Content-Type: application/json" -d "{}"
```

**Get current user:**

```bash
curl http://localhost:8080/api/auth/user -H "Authorization: Bearer YOUR_TOKEN"
```

## Starting a new project from this base

1. Copy the entire folder (or clone this repo) to your new project path
2. Update `composer.json` name/description
3. Configure `.env` for the new project's database
4. Add your routes inside the `app-jwt` group in `app/Config/Routes.php`
5. Customize `app/Config/AuthGroups.php` for your roles
6. Register scheduled jobs in `app/Config/Tasks.php`

## Scheduled tasks

Tasks are configured in `app/Config/Tasks.php`. Add jobs in the `init()` method:

```php
public function init(Scheduler $schedule): void
{
    $schedule->command('my:command')->daily();
}
```

List scheduled tasks:

```bash
php spark tasks:list
```

Run due tasks manually:

```bash
php spark tasks:run
```

On production, add a cron job to run every minute:

```bash
* * * * * cd /path-to-project && php spark tasks:run >> /dev/null 2>&1
```

## Auth flow

```
Frontend keylink
  → POST /api/auth/token
    → [production] CollabModel resolves keylink → username
    → [development] auth.devUsername from .env
    → UserModel finds active Shield user
    → JWT issued via Shield JWTManager

Protected request
  → Authorization: Bearer <jwt>
    → AppJwtAuth filter
    → auth()->user() available in controllers
```

## API routes

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/api/auth/token` | Public | Exchange keylink for JWT |
| GET | `/api/auth/user` | Bearer token | Return current user |
| * | `/api/*` (your routes) | JWT filter | Add protected routes here |

## Project structure

```
app/
  Config/
    Auth.php, AuthJWT.php, AuthGroups.php
    Cors.php, Filters.php, Routes.php, Tasks.php
  Controllers/
    AuthController.php, BaseController.php
  Filters/
    AppJwtAuth.php
  Models/
    BaseModel.php, UserModel.php, CollabModel.php
  Database/Migrations/
    2026-05-19-084414_UpdateUsersTable.php
```

## Is a base project better than a Composer package?

**Yes, for your case.** A base project is simpler to maintain than an internal package when:

- You have a small number of projects (2–5)
- Auth changes are infrequent
- Each project needs freedom to customize auth groups, routes, and models

**Consider a package later** if you find yourself applying the same auth fix across many projects.

## Updating the base project

When you improve auth in one project:

1. Apply the same change to this base project
2. Manually merge into other active projects (or re-copy auth files)

Keep a short changelog in git commits on this repo to track auth-related changes.
