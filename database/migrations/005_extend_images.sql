-- 005: images become admin-managed site photos, each placed in a section of the site.
-- section/item_key are whitelisted in src/Media/Sections.php. `path` = large WebP (≤1600px), `path_sm` = 640px WebP.
-- Not idempotent (MySQL has no ADD COLUMN IF NOT EXISTS): run once, after 002.
ALTER TABLE images
    ADD COLUMN section     VARCHAR(40)  NOT NULL DEFAULT 'unassigned' AFTER id,
    ADD COLUMN item_key    VARCHAR(60)  NULL                          AFTER section,
    ADD COLUMN sort_order  INT          NOT NULL DEFAULT 0            AFTER item_key,
    ADD COLUMN fit         ENUM('cover','contain') NOT NULL DEFAULT 'cover' AFTER alt_text,
    ADD COLUMN path_sm     VARCHAR(255) NULL                          AFTER path,
    ADD COLUMN updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
    ADD KEY idx_images_placement (section, item_key, sort_order);
