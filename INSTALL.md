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

The website forms also need these keys in `.env` (add any that `.env.example` doesn't list yet):

| Key | Required | What it is |
|---|---|---|
| `APP_KEY` | **yes** | Secret (≥32 chars) that signs form tokens. Generate: `php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'` |
| `APP_URL` | no | Public site URL, e.g. `https://hawkesbayicc.nz`; used for the admin link in alert emails |
| `MAIL_HOST` | no | SMTP server. **Leave empty to disable email alerts**: submissions are still saved and shown in the admin inbox |
| `MAIL_PORT` | no | `587` (STARTTLS, default) or `465` (SSL) |
| `MAIL_ENCRYPTION` | no | `tls` (STARTTLS, default), `ssl`, or `none` (local testing only) |
| `MAIL_USERNAME` / `MAIL_PASSWORD` | no | SMTP login (often an app password) |
| `MAIL_FROM_ADDRESS` | if mail on | Sender address, e.g. `website@hawkesbayicc.nz` (must be allowed by your SMTP provider) |
| `MAIL_FROM_NAME` | no | Sender name, e.g. `HBICC Website` |
| `MAIL_NOTIFY_TO` | if mail on | Who receives alerts; comma-separated, e.g. `info@hawkesbayicc.nz` |

Without `APP_KEY` the forms show a "call or email us" panel instead of the form.

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
Use a dedicated user like this rather than `root`, and a strong password: the app only needs access to its own database.

## 5. Run the database migrations
Migrations live in `database/migrations/` and must run in filename order:

| Order | File | Creates |
|---|---|---|
| 1 | `001_create_users.sql` | `users` (admin accounts) |
| 2 | `002_create_images.sql` | `images` (uploaded image paths) — needs `users` |
| 3 | `003_create_contents.sql` | `contents` (site content) — needs `images`, `users` |
| 4 | `004_create_inquiries.sql` | `inquiries` (enrolment, band booking and contact form submissions) |
| 5 | `005_extend_images.sql` | adds site placement (section, order, display) to `images` |

Run them with the migration runner. It applies each file once, in order, and records it in a `schema_migrations`
table, so it is safe to run again at any time (e.g. after pulling new code):
```sh
php bin/migrate.php            # apply pending migrations
php bin/migrate.php --status   # list applied / pending
```
Expected tables afterwards: `contents`, `images`, `inquiries`, `schema_migrations`, `users`.

Never edit a migration that has already been applied — add a new numbered file instead.
(`005` alters a table, so it must not be piped into `mysql` a second time; the runner prevents that.)

## 5b. Load the starter photos
The photos imported from the old website live in `database/seed-images/` (with `manifest.php` saying where each one
goes). Load them into the database and `public/uploads/`:
```sh
php bin/seed-images.php
```
Places that already have photos are skipped, so it never overwrites what an admin has uploaded. After this, all
photos are managed in the admin at `/admin/images`.

`public/uploads/` is not in git: **back it up together with the database.**

## 6. Create the first admin user
Requires step 3 (`.env` with `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USERNAME`, `DB_PASSWORD`) and step 5 (migrations).
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
- Form submissions: http://localhost:8000/admin/inquiries
- Photos: http://localhost:8000/admin/images

### Photo uploads: PHP limits
PHP's default upload limit (2 MB; Laravel Herd uses the same) is too small for camera photos. The admin accepts
photos up to 10 MB each and up to 20 per upload, so raise these in `php.ini` (find it with `php --ini`; in Herd:
*Settings → PHP → Max file upload size*):
```ini
upload_max_filesize = 10M
post_max_size = 64M
memory_limit = 512M
```
Required PHP extensions: `gd` with WebP support, `fileinfo`, and (for phone photo rotation) `exif`:
```sh
php -r 'var_dump(gd_info()["WebP Support"] ?? false, extension_loaded("fileinfo"), extension_loaded("exif"));'
```
On Apache, `public/uploads/.htaccess` stops scripts running in the uploads folder. On nginx, add the equivalent:
```nginx
location ^~ /uploads/ { location ~* \.(php|phtml|phar)$ { deny all; } }
```

## Starting over (wipe and reinstall)
**This permanently deletes all data in the database**, including users, content and the photo list.
```sh
mysql -h 127.0.0.1 -u root -p -e "DROP DATABASE IF EXISTS app_db;"
rm -rf public/uploads/*/    # uploaded photo files (keeps public/uploads/.htaccess)
```
Then repeat from step 4.
