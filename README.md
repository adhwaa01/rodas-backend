# Rodas PUPR Backend

REST API for road-damage reporting and handling, built with Laravel.

## Requirements

- PHP 8.3 or later
- Composer
- MySQL
- Node.js and npm (for Laravel's frontend assets)

## Local setup

From this directory, run:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Update the database settings in `.env`, then set unique local credentials for
the seeded admin and surveyor:

```dotenv
SEED_ADMIN_EMAIL=admin@example.test
SEED_ADMIN_PASSWORD=use-a-unique-local-password
SEED_SURVEYOR_EMAIL=surveyor@example.test
SEED_SURVEYOR_PASSWORD=use-another-unique-local-password
```

Do not commit `.env` or use development credentials in production.

Run the migrations and seed the local database:

```powershell
php artisan migrate --seed
php artisan storage:link
```

Start the API:

```powershell
php artisan serve
```

The API is available under `http://127.0.0.1:8000/api`. Sign in through
`POST /api/auth/login` to get a Sanctum token, then send it as a bearer token
for protected routes.

## Main API areas

- `auth`: login, current profile, and logout
- `admin/petugas`: surveyor account management
- `admin/damage-reports` and `petugas/damage-reports`: road-damage reports
- `admin/assignments` and `petugas/assignments`: survey assignments
- `admin/field-surveys` and `petugas/field-surveys`: field survey results
- `admin/road-handlings`: road handling progress and related documents

All routes except login are role-protected. See `routes/api.php` for the
complete endpoint list.

## Checks

```powershell
php artisan test
php vendor\bin\pint --test
php artisan route:list --path=api
```
