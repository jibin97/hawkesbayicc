---
paths: ["public/assets/**", "views/**", "admin/**", "public/**"]
---

# Responsive / mobile-first rules
Many users (public and admin) are on phones. Every page and component must work from 320px wide upward.

- Mobile-first CSS: base styles target small screens; add `min-width` media queries for larger ones.
- Every page layout includes `<meta name="viewport" content="width=device-width, initial-scale=1">`.
- No horizontal scrolling at 320px: use fluid widths (`%`, `max-width`, `minmax()`, `flex-wrap`), never fixed px widths
  for containers; long text (emails, URLs) must wrap (`overflow-wrap: anywhere`); wide tables scroll inside their own wrapper.
- Sizes, spacing, colours and fonts come from `theme.css` tokens; large text uses the fluid `clamp()` sizes.
- Touch targets (buttons, links in nav, form controls) at least 44×44px, with space between them.
- Form inputs use at least 16px text (prevents iOS auto-zoom) and the correct `type` / `inputmode` / `autocomplete`.
- Images: `max-width: 100%; height: auto`, with `width`/`height` attributes to avoid layout shift.
- Navigation and admin tables/forms must stay usable one-handed on a phone; stack columns on narrow screens.
- Don't rely on hover alone for any action or information.
- Check new or changed pages at roughly 360px, 768px and 1280px wide before calling them done.
