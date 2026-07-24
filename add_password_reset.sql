-- Adds email (required going forward, used to identify the account when
-- resetting a forgotten password) and the reset-code fields.
-- Existing accounts created before this migration will have a NULL email
-- until updated — they just won't be able to use "Forgot password" until then.
ALTER TABLE users
  ADD COLUMN email VARCHAR(255) NULL UNIQUE AFTER username,
  ADD COLUMN reset_code VARCHAR(6) NULL,
  ADD COLUMN reset_code_expires DATETIME NULL;
