# TMCWD IT Request Service System

Internal helpdesk and ticketing platform for **Trece Martires City Water District (TMCWD)** employees to submit IT support requests and for IT staff to manage, track, and resolve them.

---

## Technology Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2 / Laravel 12 |
| Frontend | Blade + Tailwind CSS 3 + Alpine.js |
| Auth | Laravel Breeze (session-based) |
| RBAC | Spatie Laravel-Permission 6 |
| Database | MySQL 8 / MariaDB |
| PDF Export | barryvdh/laravel-dompdf |
| Charts | Chart.js 4 (CDN) |

---

## Requirements

- PHP 8.2+ with extensions: pdo_mysql, mbstring, openssl, fileinfo, tokenizer, xml, ctype, json
- MySQL 8 / MariaDB 10.6+
- Composer 2
- Node.js 18+ and npm
- XAMPP (recommended for local development)

---

## Installation

### 1. Place project files

Copy or clone this project to `C:\xampp\htdocs\tmcwd_itservice`.

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install and build frontend assets

```bash
npm install
npm run build
```

### 4. Environment configuration

```bash
copy .env.example .env
php artisan key:generate
```

Edit `.env` with your database and mail credentials:

```env
DB_DATABASE=tmcwd_itservice
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Create the database

In phpMyAdmin or MySQL CLI:

```sql
CREATE DATABASE tmcwd_itservice CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Run migrations and seed

```bash
php artisan migrate --seed
```

This will create all tables and populate:
- 9 departments
- 10 ticket categories
- 4 priority levels with SLA settings
- 3 roles with permissions
- 10 sample users
- 15 sample tickets (various statuses, including SLA-breached)

### 7. Access the application

Open in browser: **http://localhost/tmcwd_itservice/public**

> **XAMPP users:** Ensure Apache and MySQL services are running in the XAMPP Control Panel.

---

## Seeded User Accounts

All accounts use password: **`password`**

| Name | Email | Role | Department |
|---|---|---|---|
| IT Admin | admin@tmcwd.gov.ph | **Admin** | IT |
| Juan dela Cruz | juan@tmcwd.gov.ph | **Agent** | IT |
| Maria Santos | maria@tmcwd.gov.ph | **Agent** | IT |
| Ana Reyes | ana@tmcwd.gov.ph | Requester | Finance/Billing |
| Pedro Gomez | pedro@tmcwd.gov.ph | Requester | Engineering |
| Rosa Mendoza | rosa@tmcwd.gov.ph | Requester | Commercial/Customer Service |
| Carlo Bautista | carlo@tmcwd.gov.ph | Requester | Administration |
| Liza Torres | liza@tmcwd.gov.ph | Requester | Production/Plant Operations |
| Ben Navarro | ben@tmcwd.gov.ph | Requester | Meter Reading |
| Joy Flores | joy@tmcwd.gov.ph | Requester | Human Resources |

---

## User Roles & Access

| Role | Can Do |
|---|---|
| **Requester** | Submit tickets, view own tickets, add public replies |
| **Agent** | View all tickets, assign/update status, add public replies & internal notes |
| **Admin** | Everything above + manage users, departments, categories, SLA settings, reports |

---

## Key Features

- **Ticket Management** — Auto-generated ticket IDs (`TMCWD-YYYY-#####`), full status workflow
- **SLA Tracking** — Per-priority SLA timers with ⚠️ warning and 🔴 breach flags
- **Role-Based Dashboards** — Tailored views for each role
- **Comments** — Public replies (visible to requester) and internal notes (IT staff only)
- **File Attachments** — Secure private downloads through Laravel Storage
- **Notifications** — Email + in-app bell notifications for ticket events
- **Urgent Alerts** — Instant email to all IT staff when Urgent ticket submitted
- **Reports** — Volume by department, avg resolution by category, CSV + PDF export
- **Admin Panel** — Full CRUD for users, departments, categories, SLA settings

---

## Mail Configuration

By default, `MAIL_MAILER=log` — all emails are written to `storage/logs/laravel.log` instead of being sent. This is ideal for development.

To use real email, update `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=yourpassword
MAIL_ENCRYPTION=tls
```

---

## SLA Defaults

| Priority | SLA Target |
|---|---|
| 🔴 Urgent | 2 hours |
| 🟠 High | 8 hours |
| 🟡 Medium | 48 hours (2 days) |
| 🔵 Low | 120 hours (5 days) |

SLA settings can be updated by Admin at **Admin → Priorities / SLA**.

---

## Project Structure

```
app/
├── Http/Controllers/
│   ├── Admin/          # DashboardController, TicketController, UserController,
│   │                   # DepartmentController, CategoryController, PriorityController, ReportController
│   ├── Agent/          # DashboardController, TicketController
│   ├── Requester/      # DashboardController, TicketController
│   ├── AttachmentController.php
│   └── NotificationController.php
├── Http/Requests/      # 7 Form Request classes
├── Models/             # User, Department, Category, Priority, Ticket,
│                       # Comment, Attachment, TicketActivity
├── Notifications/      # 5 notification classes (mail + database)
├── Policies/           # TicketPolicy, CommentPolicy
└── Services/           # SlaService, TicketService
```

---

## Re-seeding

To reset the database completely:

```bash
php artisan migrate:fresh --seed
```

> ⚠️ This drops all tables and data. Use only in development.

---

## Logo

Replace `public/images/tmcwd-logo.png` with the official TMCWD logo (recommended: PNG, 200×80px or similar, transparent background).

---

*Built for internal use by Trece Martires City Water District — IT Department.*
