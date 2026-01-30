-- SQL script to create database and contacts table for XAMPP (MySQL)
-- Run this in phpMyAdmin or mysql client

CREATE DATABASE IF NOT EXISTS `hai_au_english` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `hai_au_english`;

CREATE TABLE IF NOT EXISTS `contacts` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(50) NOT NULL,
  `course` VARCHAR(100) NOT NULL,
  `level` VARCHAR(50) DEFAULT NULL,
  `message` TEXT,
  `agreement` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX (`email`),
  INDEX (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
