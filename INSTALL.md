# Installation

Steps to set up this project from scratch, in order. Run every command from the project root unless noted.

> Keep this file up to date: whenever a migration, seed or setup script is added, add it to the matching step below.

## 1. Prerequisites
- PHP 8.0+ with the `pdo_mysql` extension (`php -m | grep pdo_mysql`)
- MySQL 8.0+ (or compatible), reachable over TCP on `127.0.0.1:3306`
- Git

If `mysql` is not on your `PATH`, use its full path (e.g. `/usr/local/mysql/bin/mysql` for the macOS installer, `/opt/homebrew/bin/mysql` for Homebrew).

## 2. Get the code
```sh
git clone git@github.com:jibin97/hawkesbayicc.git
cd hawkesbayicc
```

## 3. Configure the environment
```sh
cp .env.example .env
```
Edit `.env` and fill in the database host (`127.0.0.1`), port, database name, user and password chosen in step 4.
Never commit `.env`.

## 4. Create the database and its user
Connect as a MySQL administrator (always over TCP, `-h 127.0.0.1`):
```sh
mysql -h 127.0.0.1 -u root -p
```
Then run, replacing the name and password with your own (they must match `.env`):
```sql
CREATE DATABASE IF NOT EXISTS app_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'app_user'@'127.0.0.1' IDENTIFIED BY 'change-me';
CREATE USER IF NOT EXISTS 'app_user'@'localhost' IDENTIFIED BY 'change-me';
GRANT ALL PRIVILEGES ON app_db.* TO 'app_user'@'127.0.0.1';
GRANT ALL PRIVILEGES ON app_db.* TO 'app_user'@'localhost';
```
(Both host entries are created because MySQL may see a `127.0.0.1` connection as either, depending on name resolution.)

## 5. Run the database migrations
Migrations live in `database/migrations/` and must run in filename order:

| Order | File | Creates |
|---|---|---|
| 1 | `001_create_users.sql` | `users` (admin accounts) |
| 2 | `002_create_images.sql` | `images` (uploaded image paths) — needs `users` |
| 3 | `003_create_contents.sql` | `contents` (site content) — needs `images`, `users` |

Run them all, in order, with a single password prompt (the shell sorts the glob by name; `mysql` stops at the first error):
```sh
cat database/migrations/*.sql | mysql -h 127.0.0.1 -u app_user -p app_db
```
Verify:
```sh
mysql -h 127.0.0.1 -u app_user -p app_db -e "SHOW TABLES;"
```
Expected: `contents`, `images`, `users`.

Each migration uses `CREATE TABLE IF NOT EXISTS`, so re-running on an existing database is harmless. Never edit a migration that has already been applied — add a new numbered file instead.

## 6. Create the first admin user
Requires step 3 (`.env` with `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`) and step 5 (migrations).
```sh
php bin/create-admin.php
```
It prompts for email, name and password (hidden, entered twice, min 12 characters). Email and name can also be
passed as flags; the password is always prompted so it never ends up in shell history:
```sh
php bin/create-admin.php --email=admin@example.com --name="Site Admin"
```
On success it prints the new user's id. Run it again to add more admins; an email that already exists is rejected.

## 7. Run locally
```sh
php -S localhost:8000 -t public
```
- Site: http://localhost:8000
- Admin: http://localhost:8000/admin

## Starting over (wipe and reinstall)
**This permanently deletes all data in the database**, including users and content. Uploaded image files on disk are not removed by this.
```sh
mysql -h 127.0.0.1 -u root -p -e "DROP DATABASE IF EXISTS app_db;"
```
Then repeat from step 4.
