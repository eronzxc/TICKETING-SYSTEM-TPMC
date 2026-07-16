-- migration_03_reply_author.sql
-- Run this in phpMyAdmin (SQL tab, with the tpmc_ticketing database selected)
-- after migration_01 and migration_02.
--
-- Purpose: link each reply/comment to the ACTUAL account (user id) that
-- wrote it, so we know exactly who is allowed to edit that reply
-- (accountability — even IT cannot modify someone else's reply once sent).

USE tpmc_ticketing;

ALTER TABLE ticket_comments
  ADD COLUMN author_id INT NULL AFTER author,
  ADD COLUMN edited_at DATETIME NULL AFTER created_at,
  ADD CONSTRAINT fk_comments_author_id
    FOREIGN KEY (author_id) REFERENCES users(id)
    ON DELETE SET NULL;

-- Note: OLD replies (created before this migration was applied) will have
-- author_id = NULL, so those cannot be edited (no matching account).
-- New replies from now on can be edited by whoever wrote them.
