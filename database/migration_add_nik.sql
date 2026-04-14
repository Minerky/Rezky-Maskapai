-- Migration: Tambah kolom NIK ke tabel users
-- Jalankan lewat phpMyAdmin atau: mysql -u root rezky_maskapai < database/migration_add_nik.sql

USE rezky_maskapai;

-- Tambah kolom nik
ALTER TABLE users ADD COLUMN nik VARCHAR(20) DEFAULT NULL AFTER phone;

-- Tambah unique index untuk nik (opsional, jika ingin NIK unik)
ALTER TABLE users ADD UNIQUE INDEX uq_users_nik (nik);
