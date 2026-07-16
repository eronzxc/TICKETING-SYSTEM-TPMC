-- migration_02_ticket_owner.sql
-- Run this in phpMyAdmin (SQL tab, with the tpmc_ticketing database selected)
-- after schema.sql and migration_01_attachments.sql have already been applied.
--
-- Purpose: link each ticket to the ACTUAL account (user id) that created it,
-- so we know exactly who is allowed to reply to the ticket (owner-only reply).

USE tpmc_ticketing;

ALTER TABLE tickets
  ADD COLUMN created_by INT NULL AFTER resolved_by,
  ADD CONSTRAINT fk_tickets_created_by
    FOREIGN KEY (created_by) REFERENCES users(id)
    ON DELETE SET NULL;

-- Note: OLD tickets (created before this migration was applied) will have
-- created_by = NULL, since there was no linked account back then. That
-- means for those old tickets, only IT will be able to reply to them
-- (no matching owner). New tickets from now on will have the correct owner.
