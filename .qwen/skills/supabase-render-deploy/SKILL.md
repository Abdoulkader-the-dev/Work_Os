---
name: supabase-render-deploy
description: Deploy a Laravel Docker project to Render using Supabase PostgreSQL instead of Render managed DB
source: auto-skill
extracted_at: '2026-06-09T14:42:02.723Z'
---

## Procedure: Deploy Laravel on Render with Supabase PostgreSQL

This skill covers the non-obvious issues discovered when deploying a Laravel Docker project to Render while using Supabase (not Render's managed PostgreSQL).

### Architecture

- **Hosting**: Render (Docker runtime)
- **Database**: Supabase PostgreSQL
- **Broadcast**: `log` driver (no Pusher/Reverb needed)
- **Session**: `cookie` driver (works within 4KB limit for basic flash data)

### Critical: Supabase DB_URL must be set as a Render env var

Unlike Render's managed PostgreSQL (which auto-injects connection strings), Supabase requires you to manually provide the connection string.

In the Render dashboard, add an env var to your web service:

```
DB_URL=postgresql://postgres:[PASSWORD]@db.[PROJECT-REF].supabase.co:5432/postgres?sslmode=require
```

**Do NOT put secrets in `render.yaml`** — use the Render dashboard's "Environment" section for secrets.

Without this, every page that touches the database (including the dashboard) returns a 500 error with no helpful message in production (`APP_DEBUG=false`).

### Critical: BROADCAST_CONNECTION must default to `log`

In `config/broadcasting.php`, the default connection fallback must be `log`, not `reverb`:

```php
// ✅ Safe for build-time and runtime
'default' => env('BROADCAST_CONNECTION', 'log'),

// ❌ Crashes during `composer install` on Render (no Pusher creds at build time)
'default' => env('BROADCAST_CONNECTION', 'reverb'),
```

During the Docker build, `composer install` triggers `package:discover`, which instantiates broadcast drivers. If the default is `reverb` or `pusher` and no credentials exist, the Pusher constructor throws a fatal error (`Argument #1 ($auth_key) must be of type string, null given`).

### Critical: Schema::hasColumn calls can crash on broken DB connections

The `User` model's `shouldShowOnboarding()` and `markOnboardingCompleted()` methods call `Schema::hasColumn()` as guards. If the database is unreachable (e.g., Supabase not configured), these queries throw exceptions that manifest as 500 errors on every authenticated page.

**Fix**: Wrap in try/catch or ensure DB is always reachable before these are called. At minimum, verify Supabase connectivity immediately after deploy.

### Critical: render.yaml DB_CONNECTION without DB_URL

Setting `DB_CONNECTION: pgsql` in `render.yaml` without providing `DB_URL` or individual `DB_HOST`/`DB_PORT`/`DB_DATABASE` vars means Laravel will try to connect to `127.0.0.1:5432` — which doesn't exist on Render's Docker runtime.

Either:
1. Set `DB_URL` in the Render dashboard (recommended for Supabase), OR
2. Use Render's managed PostgreSQL and reference it with `fromDatabase` in `render.yaml`

### Post-deploy verification checklist

1. **Health check**: `curl https://<service>.onrender.com/up` → 200
2. **Registration**: Create an account → confirms DB write works
3. **Login + Dashboard**: Sign in → dashboard renders (confirms DB read + Livewire works)
4. **Logs**: Check Render logs for `migrate --force` success
5. **Supabase**: Verify tables exist in Supabase dashboard after first deploy

### Breeze auth security baseline

All Breeze forms in this project already include:
- `@csrf` on every POST form ✅
- Rate limiting on login (5 attempts per email+ip) ✅
- Session regeneration on login/logout ✅
- Password hashing via `Hash::make()` ✅
- `confirmed` validation on registration ✅

No additional CSRF work needed unless you add API routes or custom form endpoints.
