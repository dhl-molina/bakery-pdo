-- migration_v3.sql
-- Use this ONLY if your database already has the v2 tables (users + customers + orders with
-- added_by / updated_by / last_updated) and you want to keep your data.
-- A fresh install just runs schema.sql.
--
-- What it does, for both customers and orders:
--   1. adds date_added  (timestamp of when the record was created)
--   2. renames updated_by to last_updated_by
-- Records that already exist get the time you run this script as their date_added.
USE bakery_db;

SET FOREIGN_KEY_CHECKS = 0;   -- lets us rename a column that has a foreign key

ALTER TABLE customers
    ADD COLUMN date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER email,
    CHANGE COLUMN updated_by last_updated_by INT NULL;

ALTER TABLE orders
    ADD COLUMN date_added TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER pickup_date,
    CHANGE COLUMN updated_by last_updated_by INT NULL;

SET FOREIGN_KEY_CHECKS = 1;
