-- ══════════════════════════════════════════════════════
--  setup.sql — Experiment 10 : PHP + MySQL CRUD
--  Run this in phpMyAdmin or MySQL CLI before starting
-- ══════════════════════════════════════════════════════

CREATE DATABASE IF NOT EXISTS wt_exp10
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE wt_exp10;

DROP TABLE IF EXISTS products;

CREATE TABLE products (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(120)    NOT NULL,
    category    ENUM('Electronics','Clothing','Food','Books','Other') NOT NULL DEFAULT 'Other',
    price       DECIMAL(10,2)   NOT NULL,
    stock       INT UNSIGNED    NOT NULL DEFAULT 0,
    description TEXT,
    image       VARCHAR(255)    DEFAULT NULL,
    created_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Sample data ──────────────────────────────────────
INSERT INTO products (name, category, price, stock, description) VALUES
    ('iPhone 15 Pro',     'Electronics', 129999.00, 12, 'Apple A17 Pro chip, 48MP camera, titanium build.'),
    ('Samsung Galaxy S24','Electronics',  89999.00, 18, 'Snapdragon 8 Gen 3, AMOLED, 50MP camera.'),
    ('Levi\'s 501 Jeans', 'Clothing',     3499.00, 50, 'Classic straight-fit denim jeans, multiple colors.'),
    ('The Alchemist',     'Books',          349.00, 75, 'Bestseller novel by Paulo Coelho.'),
    ('Basmati Rice 5kg',  'Food',           650.00, 120,'Premium long-grain basmati rice.'),
    ('Nike Air Max 270',  'Clothing',      8999.00, 30, 'Iconic Air Max cushioning in a modern silhouette.');
