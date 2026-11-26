---
paths: ["admin/**", "views/admin/**"]
---

# Admin panel rules
- Every admin page must verify an authenticated session before doing anything else; redirect/401 otherwise.
- Every POST action must validate a CSRF token (`hash_equals` against the session token) before processing.
- Regenerate the session ID on login (`session_regenerate_id(true)`); use secure, HttpOnly, SameSite cookies.
- Strictly escape all output with `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')`.
- Validate and normalise all input (type, length, allowed values) server-side; reject rather than repair.
- Passwords: `password_hash()` / `password_verify()` only.
