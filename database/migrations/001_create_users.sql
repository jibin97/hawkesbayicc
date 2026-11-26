-- 001: admin panel user accounts
CREATE TABLE IF NOT EXISTS users (
    id             INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    email          VARCHAR(191)     NOT NULL,
    password_hash  VARCHAR(255)     NOT NULL,
    name           VARCHAR(100)     NOT NULL,
    role           ENUM('admin','editor') NOT NULL DEFAULT 'admin',
    is_active      TINYINT(1)       NOT NULL DEFAULT 1,
    last_login_at  DATETIME         NULL,
    created_at     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
