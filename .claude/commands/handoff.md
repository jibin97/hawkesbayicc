---
description: Summarize this session into .ai-scratch/handoff.md so it is safe to /clear
---

Write (overwrite) `.ai-scratch/handoff.md` with a concise session handoff. Use these sections:

1. **Completed work** — what was finished this session, in bullet points.
2. **Modified files** — every file created/changed/deleted (use `git status --short` to verify).
3. **Database changes** — new migrations, schema or seed changes, and whether they have been applied locally. Write "None" if none.
4. **Open issues** — known bugs, failing lint, unresolved decisions.
5. **Next steps** — exact, ordered actions for the next session (files, commands, approvals needed).

Keep it under 60 lines. Never include secrets or `.env` values. When done, reply with the file path and a one-line summary.
