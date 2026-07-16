-- TPMC IT Concern Desk — database schema
-- Import this once in phpMyAdmin (or via mysql CLI) on the host PC only.

CREATE DATABASE IF NOT EXISTS tpmc_ticketing
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE tpmc_ticketing;

-- ========== USERS ==========
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  fullname      VARCHAR(120)  NOT NULL,
  username      VARCHAR(60)   NOT NULL UNIQUE,
  password_hash VARCHAR(255)  NOT NULL,
  department    VARCHAR(80)   NOT NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========== TICKETS ==========
CREATE TABLE IF NOT EXISTS tickets (
  id            VARCHAR(20)   PRIMARY KEY,          -- e.g. IT-2026-0001
  requester     VARCHAR(120)  NOT NULL,
  department    VARCHAR(80)   NOT NULL,
  category      VARCHAR(60)   NOT NULL,
  priority      ENUM('Low','Medium','High','Urgent') NOT NULL,
  description   TEXT          NOT NULL,
  status        ENUM('Open','In progress','Resolved') NOT NULL DEFAULT 'Open',
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at    DATETIME      NULL,
  due_date      DATETIME      NULL,
  resolved_at   DATETIME      NULL,
  resolved_by   VARCHAR(120)  NULL
) ENGINE=InnoDB;

-- ========== TICKET ATTACHMENTS ==========
CREATE TABLE IF NOT EXISTS ticket_attachments (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id     VARCHAR(20)   NOT NULL,
  file_name     VARCHAR(255)  NOT NULL,
  file_path     VARCHAR(255)  NOT NULL,
  file_type     VARCHAR(100)  NULL,
  uploaded_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ========== TICKET COMMENTS / IT REPLIES ==========
CREATE TABLE IF NOT EXISTS ticket_comments (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id     VARCHAR(20)   NOT NULL,
  author        VARCHAR(120)  NOT NULL,
  message       TEXT          NOT NULL,
  created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB;
