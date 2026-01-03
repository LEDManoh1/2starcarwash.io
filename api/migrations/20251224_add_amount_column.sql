-- Migration: add amount column to bookings
-- Adds an `amount` column used for service price / payments. Safe to run multiple times.

ALTER TABLE bookings
  ADD COLUMN IF NOT EXISTS amount DECIMAL(10,2) NOT NULL DEFAULT 0;
