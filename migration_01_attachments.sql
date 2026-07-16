-- migration_01_attachments.sql
-- Run this in phpMyAdmin (SQL tab, with the tpmc_ticketing database selected)
-- since you already imported schema.sql before this was added.

USE tpmc_ticketing;

ALTER TABLE tickets ADD COLUMN attachments_json LONGTEXT NULL AFTER due_date;
