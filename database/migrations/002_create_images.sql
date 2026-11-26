-- 002: uploaded images (file stored on disk; path relative to web root)
CREATE TABLE IF NOT EXISTS images (
    id             INT UNSIGNED       NOT NULL AUTO_INCREMENT,
    path           VARCHAR(255)       NOT NULL,
    original_name  VARCHAR(255)       NULL,
    alt_text       VARCHAR(255)       NOT NULL DEFAULT '',
    mime_type      VARCHAR(50)        NOT NULL,
    size_bytes     INT UNSIGNED       NULL,
    width          SMALLINT UNSIGNED  NULL,
    height         SMALLINT UNSIGNED  NULL,
    uploaded_by    INT UNSIGNED       NULL,
    created_at     DATETIME           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_images_path (path),
    KEY idx_images_uploaded_by (uploaded_by),
    CONSTRAINT fk_images_uploaded_by
        FOREIGN KEY (uploaded_by) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
