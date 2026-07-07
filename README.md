# My Blog

A feature-rich blog built with vanilla PHP, MySQL, HTML/CSS, and JavaScript — no frameworks. Created as a student project to demonstrate secure web development practices.

## Features

- **Markdown editor** — EasyMDE with drag‑and‑drop / paste image uploads
- **Image uploads** — inline in posts + featured images, stored in `uploads/`
- **User authentication** — register, login, logout; password hashing with `password_hash()`
- **Email verification** — new users must verify their email before logging in (PHPMailer / SMTP)
- **Role‑based access** — `admin` and `author` roles
- **Admin panel** — dashboard, manage all posts, manage all users
- **CRUD** — authors can create, edit, and delete their own posts; admins can manage any post or user
- **Security** — PDO prepared statements, CSRF tokens, XSS‑safe output, secure password storage
- **Responsive layout** — CSS Grid / Flexbox, works on mobile and desktop

## Requirements

- PHP 7.4+
- MySQL / MariaDB (XAMPP, MAMP, or similar)
- A web server (Apache built into XAMPP, or nginx)
- Composer _(optional — all dependencies are included as files)_

## Installation

1. **Clone the repository** into your web server's document root (e.g. `htdocs/`):
   ```
   git clone https://github.com/teshan200/Blog-App.git Blog
   ```

2. **Create the database** — open phpMyAdmin or run:
   ```bash
   mysql -u root < database.sql
   ```
   This creates the `blog_app` database, tables, and a demo user.

3. **Configure database connection** — edit `config/db.php` if your MySQL credentials differ from the XAMPP defaults (`root` / empty password).

4. **Configure email verification** (optional) — copy the example config and fill in your Gmail SMTP credentials:
   ```bash
   cp config/mail.local.example.php config/mail.local.php
   ```
   Then edit `config/mail.local.php` with your real Gmail App Password:
   ```php
   $MAIL_USERNAME = 'your-email@gmail.com';
   $MAIL_PASSWORD = 'your-gmail-app-password';
   ```
   You need a [Gmail App Password](https://support.google.com/accounts/answer/185833).  
   **Note:** `config/mail.local.php` is in `.gitignore` — your credentials will never be committed.
   
   If you skip this step, registration still works but the verification email won't be sent (the admin can manually verify users).

6. **Point your browser** to `http://localhost/Blog/` (or whatever path you used).

## Demo Account

| Username | Password | Role  |
|----------|----------|-------|
| `demo`   | `demo123`| admin |

The demo account is pre‑verified. Use it to explore the admin panel and create/edit posts.

## File Structure

```
Blog/
├── admin/              # Admin panel pages
│   ├── index.php       # Dashboard
│   ├── posts.php       # Manage all posts
│   ├── users.php       # Manage all users
│   └── delete-user.php # Delete user handler
├── api/
│   └── upload.php      # Image upload endpoint (called by EasyMDE)
├── assets/
│   ├── css/
│   │   └── style.css   # All styling
│   └── js/
│       └── main.js     # EasyMDE initialization + image upload handler
├── config/
│   ├── db.php                 # Database connection (PDO)
│   ├── db.local.example.php   # Local DB config template (gitignore the .local copy)
│   ├── mail.php               # SMTP / PHPMailer configuration
│   └── mail.local.example.php # Mail credentials template (gitignore the .local copy)
├── includes/
│   ├── auth.php        # Authentication helpers (is_logged_in, require_admin, etc.)
│   ├── footer.php      # Shared footer
│   ├── functions.php   # Utility functions (e, redirect, flash, CSRF, image upload)
│   └── header.php      # Shared header + navbar
├── lib/
│   ├── Parsedown.php   # Markdown‑to‑HTML parser
│   └── phpmailer/      # PHPMailer library (PHPMailer.php, SMTP.php, Exception.php)
├── uploads/            # User‑uploaded images
│   └── .htaccess       # Deny PHP execution in uploads
├── create.php          # Create a new post
├── database.sql        # Database schema + seed data
├── delete.php          # Delete a post
├── edit.php            # Edit a post
├── index.php           # Home page (lists all posts)
├── login.php           # Login page
├── logout.php          # Logout handler
├── migrate_verify.php  # One‑time migration script for existing databases
├── post.php            # View a single post
├── register.php        # Registration page
└── verify.php          # Email verification handler
```

## CI/CD with GitHub Actions

The repo includes `.github/workflows/deploy.yml` for automatic deployment to InfinityFree via FTP on every push to `main`.

### Required GitHub Secrets

Go to your repo → **Settings → Secrets and variables → Actions → New repository secret** and add:

| Secret | Description |
|--------|-------------|
| `FTP_USERNAME` | InfinityFree FTP username (e.g. `epiz_12345678`) |
| `FTP_PASSWORD` | InfinityFree FTP password |
| `DB_HOST` | InfinityFree MySQL host from the control panel (e.g. `sql123.epizy.com`) |
| `DB_NAME` | InfinityFree database name (e.g. `epiz_12345678_blog_app`) |
| `DB_USER` | InfinityFree MySQL username |
| `DB_PASS` | InfinityFree MySQL password |
| `MAIL_USERNAME` | _(optional)_ Gmail address for sending verification emails |
| `MAIL_PASSWORD` | _(optional)_ Gmail App Password |
| `MAIL_FROM` | _(optional)_ Sender address (same as username typically) |

If `MAIL_*` secrets are omitted, users will register without receiving a verification email. An admin can verify them manually via phpMyAdmin.

## Security

- **SQL injection** — all database queries use PDO prepared statements with real parameter binding (`EMULATE_PREPARES` is off)
- **CSRF** — every form includes a hidden CSRF token verified server‑side
- **XSS** — all user output goes through `htmlspecialchars()`; Markdown is rendered with Parsedown's safe mode enabled
- **Passwords** — hashed with `password_hash(PASSWORD_DEFAULT)` (bcrypt)
- **File uploads** — MIME type verification, size limit (5 MB), integrity check via `getimagesize()`, PHP execution disabled in `uploads/`
- **Session security** — session ID regenerated on login; no session fixation

## License

MIT — feel free to use, modify, and share.
