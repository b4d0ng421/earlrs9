-- =========================================================
-- RS8 Racing — Orders status migration
-- Run this once if you already imported an older database/rs8.sql
-- whose orders.status column only allowed pending/completed/cancelled.
--
-- phpMyAdmin: select rs8_racing, then run the ALTER TABLE below.
-- CLI:        mysql -u root rs8_racing < database/migrations/2026-10-22_orders_status.sql
-- =========================================================

USE rs8_racing;

ALTER TABLE orders
  MODIFY status ENUM('pending', 'processing', 'shipped', 'completed', 'cancelled')
  NOT NULL DEFAULT 'pending';
