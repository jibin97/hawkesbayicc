# CLAUDE.md

## Project overview
Static-first PHP 8+ public website with a lightweight MySQL-backed admin panel (content management).

- `public/` — web root (front controller, static assets)
- `views/public/`, `views/admin/` — templates
- `admin/` — admin panel entry points
- `src/` — application code (`src/Database/` for DB access)
- `database/migrations/` — ordered, reproducible SQL migrations

Path-scoped rules in `.claude/rules/` load automatically when matching files are touched.

## Local environment
- Start server: `php -S localhost:8000 -t public`
- Lint all PHP files: `find . -path ./vendor -prune -o -name "*.php" -exec php -l {} +`
- Database: always connect via TCP `127.0.0.1` (never `localhost`, which uses the Unix socket).
- Config comes from `.env` (copy `.env.example`). Never read or print `.env` contents.

## Workflow rules
- Before implementing any multi-file feature, write a concise blueprint to `.ai-scratch/plan.md` and wait for approval.
- Keep dependencies minimal; prefer native PHP 8+ PDO, prepared statements, and standard library features.
- Always run the PHP lint command after editing `.php` files.
- When adding a migration, seed, or setup script, add it to `INSTALL.md` in the correct order.
- Use `/handoff` before `/clear` to persist session state to `.ai-scratch/handoff.md`.
