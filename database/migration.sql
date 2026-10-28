-- migration.sql
-- Use this ONLY if you already created bakery_db from the old schema.sql and
-- want to keep your existing customers and orders. (A fresh install just runs schema.sql.)
USE bakery_db;

CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(50)  NOT NULL,
    last_name     VARCHAR(50)  NOT NULL,
    address       VARCHAR(255) NOT NULL,
    age           TINYINT UNSIGNED NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE customers
    ADD COLUMN added_by     INT NULL,
    ADD COLUMN updated_by   INT NULL,
    ADD COLUMN last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,
    ADD FOREIGN KEY (added_by)   REFERENCES users(user_id) ON DELETE SET NULL,
    ADD FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL;

ALTER TABLE orders
    ADD COLUMN added_by     INT NULL,
    ADD COLUMN updated_by   INT NULL,
    ADD COLUMN last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                            ON UPDATE CURRENT_TIMESTAMP,
    ADD FOREIGN KEY (added_by)   REFERENCES users(user_id) ON DELETE SET NULL,
    ADD FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL;
