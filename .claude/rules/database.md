---
paths: ["database/**", "src/Database/**"]
---

# Database rules
- PDO only, configured with `PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION`, `PDO::ATTR_EMULATE_PREPARES => false`, `PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC`.
- DSN uses `host=127.0.0.1` and `charset=utf8mb4`; credentials come from environment config.
- Parameterized prepared statements only — never interpolate values into SQL. Whitelist identifiers (columns, sort order) explicitly.
- Schema changes go in reproducible, ordered SQL files in `database/migrations/` (e.g. `001_create_pages.sql`); never edit an applied migration — add a new one.
