-- Migration: insert default services (2025-12-24)
-- Safe create + inserts for manual quick import

SET FOREIGN_KEY_CHECKS=0;

CREATE TABLE IF NOT EXISTS `services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `price` INT NOT NULL,
  `duration` INT NOT NULL, -- duration in minutes
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO services (name, price, duration) VALUES
('Exterior Wash', 5000, 20),
('Full Wash', 10000, 45),
('Engine Wash', 7000, 30),
('Interior Cleaning', 8000, 40),
('Waxing', 15000, 60)
ON DUPLICATE KEY UPDATE name=VALUES(name);

SET FOREIGN_KEY_CHECKS=1;
