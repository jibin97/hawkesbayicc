-- 004: website form submissions (enrolment, band booking, contact)
CREATE TABLE IF NOT EXISTS inquiries (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    type           ENUM('enrolment','booking','contact') NOT NULL,
    status         ENUM('new','read','archived') NOT NULL DEFAULT 'new',
    name           VARCHAR(150)  NOT NULL,   -- person to reply to
    email          VARCHAR(191)  NOT NULL,
    phone          VARCHAR(40)   NULL,
    subject        VARCHAR(255)  NOT NULL,   -- one-line summary for the inbox list
    message        TEXT          NULL,
    details        JSON          NOT NULL,   -- every submitted field, keyed by field name
    ip_hash        CHAR(64)      NULL,       -- keyed SHA-256 of the IP, for rate limiting only
    email_sent_at  DATETIME      NULL,
    email_error    VARCHAR(255)  NULL,
    created_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_inquiries_status_created (status, created_at),
    KEY idx_inquiries_type_created (type, created_at),
    KEY idx_inquiries_ip_created (ip_hash, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
