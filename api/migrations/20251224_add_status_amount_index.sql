-- Migration: add status and amount columns and index on booking_date
-- Run this in phpMyAdmin or mysql CLI for the 'carwash' database.

ALTER TABLE bookings
  ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER booking_time,
  ADD COLUMN amount DECIMAL(10,2) NULL AFTER status;

CREATE INDEX idx_booking_date ON bookings (booking_date);
-- Optional: index status
CREATE INDEX idx_booking_status ON bookings (status);
