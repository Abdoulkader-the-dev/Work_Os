---
name: render-deployment
description: Deploy a Laravel 13 Docker project to Render using a render.yaml blueprint with managed PostgreSQL
source: auto-skill
extracted_at: '2026-06-09T07:01:58.032Z'
---

## Procedure: Prepare a Laravel Docker project for Render deployment

This skill covers the non-obvious steps discovered while adapting a Laravel 13 Docker project (with nginx + PHP-FPM) for Render.

### Key files to create/modify

1. **`render.yaml`** — Place at project root. Render Blueprint auto-provisions web + database.
2. **`docker/start.sh`** — Must run `php artisan migrate --force` on every startup (Render has no separate migration step).
3. **`docker/nginx.conf`** — Use `${PORT}` placeholder instead of a hardcoded port. Render injects `PORT` env var but nginx doesn't natively expand env vars.
4. **`Dockerfile`** — Install `gettext` package in Alpine for `envsubst`, set `ENV PORT=10000` as default.

### Critical: nginx port templating

Render assigns a dynamic `PORT` env var. Nginx config files are static and do NOT expand shell env vars. The solution is to use `envsubst` in the startup script:

```sh
# In docker/start.sh — BEFORE starting nginx:
envsubst '${PORT}' < /etc/nginx/nginx.conf > /etc/nginx/nginx.conf.tmp && mv /etc/nginx/nginx.conf.tmp /etc/nginx/nginx.conf
```

The nginx config should use `${PORT}` (single-quoted in envsubst to avoid shell interpolation):

```nginx
server {
    listen ${PORT};
    ...
}
```

Install `gettext` in the Dockerfile (Alpine):

```dockerfile
RUN apk add --no-cache gettext ...
```

### Critical: Run migrations in startup script

Render does not have a pre-deploy hook for Docker services. `php artisan migrate --force` must be in `start.sh` before `php-fpm` starts:

```sh
#!/bin/sh
envsubst '${PORT}' < /etc/nginx/nginx.conf > /etc/nginx/nginx.conf.tmp && mv /etc/nginx/nginx.conf.tmp /etc/nginx/nginx.conf
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php-fpm -D
nginx -g "daemon off;"
```

### render.yaml template

```yaml
services:
  - type: web
    name: <service-name>
    runtime: docker
    plan: starter
    healthCheckPath: /up
    envVars:
      - key: APP_ENV
        value: production
      - key: APP_DEBUG
        value: "false"
      - key: APP_URL
        value: https://<service-name>.onrender.com
      - key: APP_KEY
        generateValue: true
      - key: DB_CONNECTION
        value: pgsql
      - key: SESSION_DRIVER
        value: cookie
      - key: CACHE_STORE
        value: array
      - key: QUEUE_CONNECTION
        value: sync
      - key: BROADCAST_CONNECTION
        value: log
      - key: FILESYSTEM_DISK
        value: local
      - key: LOG_CHANNEL
        value: stderr
      - key: LOG_LEVEL
        value: error
    autoDeploy: true

databases:
  - name: <db-name>
    plan: starter
    databaseName: <db-name>
    user: <db-name>
```

### Critical: broadcast driver default must be `log`

`config/broadcasting.php` must default to `log`, not `reverb` or `pusher`. During the Docker build, `composer install` triggers `package:discover`, which instantiates broadcast drivers. Without credentials at build time, the Pusher constructor crashes the entire build:

```php
// ❌ Build crashes: null auth_key at build time
'default' => env('BROADCAST_CONNECTION', 'reverb'),

// ✅ Safe: log driver needs no credentials
'default' => env('BROADCAST_CONNECTION', 'log'),
```

Error message to watch for:
```
Pusher\Pusher::__construct(): Argument #1 ($auth_key) must be of type string, null given
```

### Known limitations to warn about

- **SQLite is not viable** on Render — ephemeral filesystem destroys data on restart. Use managed PostgreSQL from the start.
- **Local file storage** is ephemeral on Render. `FILESYSTEM_DISK=local` works but uploaded files disappear on restart. Use S3 for persistence.
- **Queue**: `QUEUE_CONNECTION=sync` is the safe default. Switch to `database` + separate worker process for production scale.
- **APP_URL** must be updated in `render.yaml` if the service is renamed — it's not auto-derived.
- **Dockerfile `npm run build`** runs at build time so Vite assets are baked in. No dev server needed on Render.

### DB_URL connection string

Render injects the database connection string automatically when using `fromDatabase` in `render.yaml`, or it can be found in the Render dashboard under the database's "Connect" section. For Laravel's `DB_URL` format, use:

```
postgresql://<user>:<password>@<host>:<port>/<database>?sslmode=require
```

### post-deploy checks

After the first deploy:
1. Verify the health check passes (`/up` returns 200)
2. Check logs for migration success
3. Verify `config:cache` didn't fail (would indicate missing env vars)
4. Test the auth flow (registration + login) to confirm DB connectivity
