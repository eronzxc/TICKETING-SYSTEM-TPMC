-- migration_04_ticket_remarks.sql
-- Patakbuhin sa phpMyAdmin (SQL tab, habang naka-select yung tpmc_ticketing database)
-- pagkatapos ng migration_01, migration_02, at migration_03.
--
-- Layunin: magdagdag ng "remarks" field na pwedeng gamitin ng IT para sa
-- internal notes tungkol sa ticket (hal. root cause, parts palitan, atbp.),
-- hiwalay sa reply threadttr na nakikita ng requester.

USE tpmc_ticketing;

ALTER TABLE tickets
  ADD COLUMN remarks TEXT NULL AFTER resolved_by;
