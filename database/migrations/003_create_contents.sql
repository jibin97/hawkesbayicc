-- 003: site content, optionally linked to one image
CREATE TABLE IF NOT EXISTS contents (
    id            INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    slug          VARCHAR(191)  NOT NULL,
    title         VARCHAR(255)  NOT NULL,
    summary       VARCHAR(500)  NULL,
    body          MEDIUMTEXT    NOT NULL,
    image_id      INT UNSIGNED  NULL,
    status        ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at  DATETIME      NULL,
    created_by    INT UNSIGNED  NULL,
    updated_by    INT UNSIGNED  NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_contents_slug (slug),
    KEY idx_contents_status_published (status, published_at),
    KEY idx_contents_image_id (image_id),
    KEY idx_contents_created_by (created_by),
    KEY idx_contents_updated_by (updated_by),
    CONSTRAINT fk_contents_image
        FOREIGN KEY (image_id) REFERENCES images (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_contents_created_by
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_contents_updated_by
        FOREIGN KEY (updated_by) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
