-- migration_v4.sql
-- Use this ONLY if your database already has the v3 tables and you want to keep your data.
-- A fresh install just runs schema.sql.
--
-- What it does to the orders table:
--   1. image_path : the image attached to the order (one image per order)
--   2. deleted_at : when the order was moved to the recycle bin (NULL = not deleted)
--   3. deleted_by : the user who moved it there
-- Existing orders stay active (deleted_at is NULL) and have no image yet.
USE bakery_db;

ALTER TABLE orders
    ADD COLUMN image_path VARCHAR(255) NULL,
    ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL,
    ADD COLUMN deleted_by INT NULL,
    ADD FOREIGN KEY (deleted_by) REFERENCES users(user_id) ON DELETE SET NULL;
