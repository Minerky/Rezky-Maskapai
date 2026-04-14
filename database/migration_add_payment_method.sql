-- Jalankan sekali jika database lama belum punya kolom payment_method
-- mysql -u root rezky_maskapai < database/migration_add_payment_method.sql

USE rezky_maskapai;

ALTER TABLE payments
  ADD COLUMN payment_method VARCHAR(32) NOT NULL DEFAULT 'bank_transfer' AFTER amount;
