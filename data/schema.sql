-- =====================================================================
-- Secure User Registration System Database Schema
-- Database Name: user_registration_db
-- Storage Engine: InnoDB
-- Charset: utf8mb4
-- Collation: utf8mb4_unicode_ci
-- =====================================================================

-- 1. Create database if it does not exist
CREATE DATABASE IF NOT EXISTS `user_registration_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

-- 2. Select database
USE `user_registration_db`;

-- 3. Create users table with UNIQUE constraints
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `full_name` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `uq_users_username` UNIQUE (`username`),
    CONSTRAINT `uq_users_email` UNIQUE (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Lookup indices
CREATE INDEX `idx_users_email` ON `users` (`email`);
CREATE INDEX `idx_users_username` ON `users` (`username`);
