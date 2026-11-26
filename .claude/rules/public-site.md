---
paths: ["public/**", "views/public/**"]
---

# Public site rules
- Static-first: serve pre-rendered or cacheable HTML; avoid per-request work that isn't needed.
- Semantic, accessible HTML5 (landmarks, headings in order, alt text, labelled forms).
- Minimal JavaScript: progressive enhancement only; the page must work without JS.
- Zero admin/DB exposure: no admin links, credentials, stack traces, debug output, or raw DB errors in public responses.
- Escape all dynamic output with `htmlspecialchars($v, ENT_QUOTES, 'UTF-8')`.
