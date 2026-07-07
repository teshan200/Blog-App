-- ============================================================
-- Blog Application - Database Schema
-- For XAMPP MySQL (MariaDB). Run via phpMyAdmin or mysql CLI.
-- ============================================================

CREATE DATABASE IF NOT EXISTS blog_app
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE blog_app;

-- ----------------------------------------------------------
-- Table: user
-- Holds registered users. role is kept for future use;
-- in v1 every user can only manage their own posts.
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS user (
    id        INT AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(50)  NOT NULL UNIQUE,
    email     VARCHAR(100) NOT NULL UNIQUE,
    password  VARCHAR(255) NOT NULL,           -- bcrypt hash from password_hash()
    role             ENUM('admin','author') NOT NULL DEFAULT 'author',
    email_verified   TINYINT(1) NOT NULL DEFAULT 0,
    verification_token VARCHAR(64) DEFAULT NULL,
    created_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Table: blogPost
-- Each post belongs to one user (author) via user_id.
-- ----------------------------------------------------------
CREATE TABLE IF NOT EXISTS blogPost (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    title           VARCHAR(150) NOT NULL,
    content         TEXT NOT NULL,
    featured_image  VARCHAR(255) DEFAULT NULL,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_post_user
        FOREIGN KEY (user_id) REFERENCES user(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_post_user (user_id),
    INDEX idx_post_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------
-- Seed: one demo author account so you can log in right away.
-- Username: demo      Password: demo123
-- (Hash below was generated with password_hash('demo123', PASSWORD_DEFAULT))
-- ----------------------------------------------------------
INSERT INTO user (username, email, password, role) VALUES
('demo', 'demo@example.com', '$2y$10$4spG2oef3FOIDfgMmPBxwubNm88vftulYyM1LPh4jsVPi93cEvh2u', 'admin')
ON DUPLICATE KEY UPDATE username = username;

-- A sample post by the demo user (id = 1 assumed; safe because of ON DUPLICATE above).
-- Content is written in Markdown and will be rendered by Parsedown.
INSERT INTO blogPost (user_id, title, content) VALUES
(1, 'Welcome to my blog',
'This is the first post on the demo blog. Edit or delete it, or create your own!

## Features

- **Markdown editing** with the EasyMDE editor — drag & drop images to upload
- **Responsive layout** using CSS Grid and Flexbox
- **Secure** prepared statements, password hashing, CSRF protection

### Adding images

You can upload images by dragging & dropping them into the editor,
pasting from your clipboard, or clicking the image button in the toolbar.
They will be stored in the `uploads/` folder and embedded automatically:

```
![alt text](uploads/your-image.jpg)
```

> Happy blogging!');
