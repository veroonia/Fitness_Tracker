-- Fitness_Tracker database schema (MySQL/MariaDB)
-- Run this in phpMyAdmin or mysql client.

CREATE DATABASE IF NOT EXISTS fitness_tracker
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE fitness_tracker;

-- 1) Users
CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL,
  email VARCHAR(191) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2) User profile extension (1:1 with users)
CREATE TABLE IF NOT EXISTS user_profiles (
  user_id INT UNSIGNED NOT NULL,
  age INT NULL,
  goal_preference VARCHAR(20) NULL,
  height_cm DECIMAL(5,2) NULL,
  weight_kg DECIMAL(5,2) NULL,
  bmi DECIMAL(5,2) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id),
  CONSTRAINT fk_user_profiles_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Meal logs
CREATE TABLE IF NOT EXISTS meals (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id INT UNSIGNED NOT NULL,
  food_query VARCHAR(255) NOT NULL,
  calories DECIMAL(10,2) NOT NULL DEFAULT 0,
  protein_g DECIMAL(10,2) NOT NULL DEFAULT 0,
  carbs_g DECIMAL(10,2) NOT NULL DEFAULT 0,
  fat_g DECIMAL(10,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_meals_user_id (user_id),
  CONSTRAINT fk_meals_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Backfill profile rows for existing users
INSERT INTO user_profiles (user_id)
SELECT u.id
FROM users u
LEFT JOIN user_profiles up ON up.user_id = u.id
WHERE up.user_id IS NULL;
