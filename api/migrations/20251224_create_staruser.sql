-- Migration: create staruser for local dev
-- Run this as a privileged DB user (e.g. root)

CREATE USER IF NOT EXISTS 'staruser'@'127.0.0.1' IDENTIFIED BY 'star123';
GRANT ALL PRIVILEGES ON `carwash`.* TO 'staruser'@'127.0.0.1';

-- Also allow connecting from localhost in case PHP resolves it differently
CREATE USER IF NOT EXISTS 'staruser'@'localhost' IDENTIFIED BY 'star123';
GRANT ALL PRIVILEGES ON `carwash`.* TO 'staruser'@'localhost';

FLUSH PRIVILEGES;
