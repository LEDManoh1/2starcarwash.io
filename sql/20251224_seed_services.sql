-- Idempotent seed for services (2025-12-24)
-- Inserts services only if a service with the same name does not already exist.

START TRANSACTION;

INSERT INTO services (name, price, duration)
SELECT 'Member Card (Monthly)', 99000, 30
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Member Card (Monthly)');

INSERT INTO services (name, price, duration)
SELECT 'Full Body Wash (Large Car)', 30000, 45
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Full Body Wash (Large Car)');

INSERT INTO services (name, price, duration)
SELECT 'Full Body Wash (Small Car)', 20000, 40
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Full Body Wash (Small Car)');

INSERT INTO services (name, price, duration)
SELECT 'Normal Wash', 15000, 30
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Normal Wash');

INSERT INTO services (name, price, duration)
SELECT 'Body Wash Only', 10000, 20
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Body Wash Only');

INSERT INTO services (name, price, duration)
SELECT 'Interior Cleaning', 10000, 25
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Interior Cleaning');

INSERT INTO services (name, price, duration)
SELECT 'Engine Wash', 10000, 25
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Engine Wash');

INSERT INTO services (name, price, duration)
SELECT 'Chassis Wash', 10000, 25
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Chassis Wash');

-- Motorcycle / Bajaji
INSERT INTO services (name, price, duration)
SELECT 'Bajaji Wash', 3000, 10
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Bajaji Wash');

INSERT INTO services (name, price, duration)
SELECT 'Pikipiki Wash', 2000, 8
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Pikipiki Wash');

-- Carpet cleaning
INSERT INTO services (name, price, duration)
SELECT 'Medium Carpet Cleaning', 15000, 40
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Medium Carpet Cleaning');

INSERT INTO services (name, price, duration)
SELECT 'Large Carpet Cleaning', 20000, 60
WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Large Carpet Cleaning');

COMMIT;
