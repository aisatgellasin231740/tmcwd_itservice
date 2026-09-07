# Design — TMCWD IT Request Service System

**Organization:** Trece Martires City Water District (TMCWD)  
**Document Type:** Technical Design Document  
**Version:** 1.0  
**Date:** September 2026

---

## 1. Technology Stack

| Layer | Technology |
|---|---|
| Language | PHP 8.2+ |
| Framework | Laravel 11 (LTS) |
| Frontend | Blade templates + Tailwind CSS 3 |
| Auth | Laravel Breeze (session-based) |
| Authorization | Spatie Laravel-Permission |
| Database | MySQL 8 / MariaDB 10.6+ |
| DB Admin | phpMyAdmin (existing XAMPP setup) |
| File Storage | Laravel Storage (local disk, `storage/app/attachments`) |
| Mail | Laravel Notifications + SMTP (configurable via `.env`) |
| Charts | Chart.js (CDN, Admin dashboard) |
| PDF Export | barryvdh/laravel-dompdf |
| Icons | Heroicons (SVG, inline) |

---

## 2. Project Directory Structure

```
tmcwd_itservice/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/                    # Breeze auth controllers
│   │   │   ├── Admin/
│   │   │   │   ├── UserController.php
│   │   │   │   ├── DepartmentController.php
│   │   │   │   ├── CategoryController.php
│   │   │   │   ├── PriorityController.php
│   │   │   │   ├── ReportController.php
│   │   │   │   └── DashboardController.php
│   │   │   ├── Agent/
│   │   │   │   ├── TicketController.php
│   │   │   │   └── DashboardController.php
│   │   │   ├── Requester/
│   │   │   │   ├── TicketController.php
│   │   │   │   └── DashboardController.php
│   │   │   ├── CommentController.php
│   │   │   ├── AttachmentController.php
│   │   │   └── NotificationController.php
│   │   ├── Requests/
│   │   │   ├── StoreTicketRequest.php
│   │   │   ├── UpdateTicketRequest.php
│   │   │   ├── StoreCommentRequest.php
│   │   │   ├── StoreUserRequest.php
│   │   │   ├── UpdateUserRequest.php
│   │   │   ├── StoreDepartmentRequest.php
│   │   │   └── StoreCategoryRequest.php
│   │   └── Middleware/
│   │       └── EnsureUserIsActive.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Department.php
│   │   ├── Category.php
│   │   ├── Priority.php
│   │   ├── Ticket.php
│   │   ├── Comment.php
│   │   ├── Attachment.php
│   │   └── TicketActivity.php
│   ├── Notifications/
│   │   ├── TicketCreated.php
│   │   ├── TicketStatusUpdated.php
│   │   ├── TicketCommented.php
│   │   ├── TicketAssigned.php
│   │   └── UrgentTicketAlert.php
│   ├── Policies/
│   │   ├── TicketPolicy.php
│   │   └── CommentPolicy.php
│   └── Services/
│       ├── TicketService.php          # Business logic: create, assign, resolve
│       └── SlaService.php             # SLA calculation, pause/resume, breach check
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── DepartmentSeeder.php
│       ├── CategorySeeder.php
│       ├── PrioritySeeder.php
│       ├── RolePermissionSeeder.php
│       ├── UserSeeder.php
│       └── TicketSeeder.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php           # Main authenticated layout
│       │   └── guest.blade.php         # Login/register layout
│       ├── components/
│       │   ├── ticket-badge.blade.php  # Status/priority badges
│       │   ├── sla-indicator.blade.php # SLA countdown/flag
│       │   ├── notification-bell.blade.php
│       │   └── stat-card.blade.php
│       ├── auth/                        # Breeze auth views
│       ├── requester/
│       │   ├── dashboard.blade.php
│       │   ├── tickets/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── show.blade.php
│       ├── agent/
│       │   ├── dashboard.blade.php
│       │   └── tickets/
│       │       ├── index.blade.php
│       │       └── show.blade.php
│       └── admin/
│           ├── dashboard.blade.php
│           ├── users/
│           ├── departments/
│           ├── categories/
│           ├── priorities/
│           ├── tickets/
│           └── reports/
└── routes/
    ├── web.php
    └── auth.php
```

---

## 3. Database Schema

### 3.1 Entity-Relationship Overview

```
departments ──< users ──< tickets >── categories
                              │
                    ┌─────────┼──────────┐
                    │         │          │
                comments  attachments  ticket_activities
                              │
                           priorities
```

### 3.2 Migrations

#### `departments`
```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
name            VARCHAR(100) NOT NULL UNIQUE
is_active       BOOLEAN DEFAULT TRUE
created_at, updated_at
```

#### `priorities`
```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
name            VARCHAR(50) NOT NULL  -- Low, Medium, High, Urgent
sla_hours       DECIMAL(6,2) NOT NULL -- 120, 48, 8, 2
color_code      VARCHAR(20) NOT NULL  -- Tailwind color class or hex
sort_order      TINYINT UNSIGNED DEFAULT 0
created_at, updated_at
```

#### `categories`
```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
name            VARCHAR(100) NOT NULL UNIQUE
is_active       BOOLEAN DEFAULT TRUE
created_at, updated_at
```

#### `users`
```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
name            VARCHAR(150) NOT NULL
email           VARCHAR(255) NOT NULL UNIQUE
password        VARCHAR(255) NOT NULL
department_id   BIGINT UNSIGNED FK → departments.id
is_active       BOOLEAN DEFAULT TRUE
remember_token  VARCHAR(100)
created_at, updated_at
```
> Role assignment handled by `model_has_roles` (Spatie).

#### `tickets`
```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
ticket_number   VARCHAR(20) NOT NULL UNIQUE  -- TMCWD-2026-00001
title           VARCHAR(255) NOT NULL
description     TEXT NOT NULL
requester_id    BIGINT UNSIGNED FK → users.id
department_id   BIGINT UNSIGNED FK → departments.id
category_id     BIGINT UNSIGNED FK → categories.id
priority_id     BIGINT UNSIGNED FK → priorities.id
assigned_to     BIGINT UNSIGNED FK → users.id NULLABLE
status          ENUM('open','in_progress','on_hold','resolved','closed') DEFAULT 'open'
sla_due_at      DATETIME NULLABLE
sla_paused_at   DATETIME NULLABLE
sla_paused_duration INT DEFAULT 0  -- accumulated pause seconds
resolved_at     DATETIME NULLABLE
closed_at       DATETIME NULLABLE
created_at, updated_at
```

#### `comments`
```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
ticket_id       BIGINT UNSIGNED FK → tickets.id CASCADE DELETE
user_id         BIGINT UNSIGNED FK → users.id
body            TEXT NOT NULL
is_internal     BOOLEAN DEFAULT FALSE
created_at, updated_at
```

#### `attachments`
```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
ticket_id       BIGINT UNSIGNED FK → tickets.id CASCADE DELETE
user_id         BIGINT UNSIGNED FK → users.id
original_name   VARCHAR(255) NOT NULL
stored_path     VARCHAR(500) NOT NULL
mime_type       VARCHAR(100)
size            BIGINT UNSIGNED
created_at, updated_at
```

#### `ticket_activities`
```sql
id              BIGINT UNSIGNED PK AUTO_INCREMENT
ticket_id       BIGINT UNSIGNED FK → tickets.id CASCADE DELETE
user_id         BIGINT UNSIGNED FK → users.id NULLABLE
action          VARCHAR(100) NOT NULL  -- 'status_changed', 'assigned', 'priority_changed'
old_value       VARCHAR(255) NULLABLE
new_value       VARCHAR(255) NULLABLE
description     VARCHAR(500) NULLABLE
created_at, updated_at
```

#### `notifications` (Laravel default)
```sql
id              CHAR(36) UUID PK
type            VARCHAR(255)
notifiable_type VARCHAR(255)
notifiable_id   BIGINT UNSIGNED
data            TEXT (JSON)
read_at         TIMESTAMP NULLABLE
created_at, updated_at
```

> Spatie tables: `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`

---

## 4. Eloquent Models & Relationships

### `User`
```php
belongsTo(Department::class)
hasMany(Ticket::class, 'requester_id')
hasMany(Ticket::class, 'assigned_to')    // as agent
hasMany(Comment::class)
// Spatie: HasRoles trait
```

### `Department`
```php
hasMany(User::class)
hasMany(Ticket::class)
```

### `Category`
```php
hasMany(Ticket::class)
```

### `Priority`
```php
hasMany(Ticket::class)
```

### `Ticket`
```php
belongsTo(User::class, 'requester_id')
belongsTo(User::class, 'assigned_to')
belongsTo(Department::class)
belongsTo(Category::class)
belongsTo(Priority::class)
hasMany(Comment::class)
hasMany(Attachment::class)
hasMany(TicketActivity::class)
```

### `Comment`
```php
belongsTo(Ticket::class)
belongsTo(User::class)
```

### `Attachment`
```php
belongsTo(Ticket::class)
belongsTo(User::class)
```

### `TicketActivity`
```php
belongsTo(Ticket::class)
belongsTo(User::class)
```

---

## 5. Routing Design

All routes are under the `/` prefix and guarded by `auth` + `verified` middleware.

```
GET    /                          → redirect to role dashboard

-- AUTH (Breeze) --
GET    /login
POST   /login
POST   /logout
GET    /forgot-password
POST   /forgot-password
GET    /reset-password/{token}
POST   /reset-password

-- REQUESTER (middleware: role:requester|agent|admin) --
GET    /dashboard                 → role-aware dashboard redirect
GET    /tickets                   → requester ticket list
GET    /tickets/create            → create ticket form
POST   /tickets                   → store ticket
GET    /tickets/{ticket}          → view own ticket detail
POST   /tickets/{ticket}/comments → add public reply

-- AGENT (middleware: role:agent|admin) --
GET    /agent/dashboard
GET    /agent/tickets             → full queue with filters
GET    /agent/tickets/{ticket}    → ticket detail with actions
PATCH  /agent/tickets/{ticket}    → update status/assignee
POST   /agent/tickets/{ticket}/comments  → add comment (public or internal)

-- ADMIN (middleware: role:admin) --
GET    /admin/dashboard
GET    /admin/tickets             → all tickets
GET    /admin/tickets/{ticket}
PATCH  /admin/tickets/{ticket}
DELETE /admin/tickets/{ticket}

GET    /admin/users               → user list
GET    /admin/users/create
POST   /admin/users
GET    /admin/users/{user}/edit
PUT    /admin/users/{user}
DELETE /admin/users/{user}

GET    /admin/departments
POST   /admin/departments
PUT    /admin/departments/{department}

GET    /admin/categories
POST   /admin/categories
PUT    /admin/categories/{category}

GET    /admin/priorities
PUT    /admin/priorities/{priority}

GET    /admin/reports             → report index
GET    /admin/reports/export-csv  → download CSV
GET    /admin/reports/export-pdf  → download PDF

-- SHARED --
GET    /attachments/{attachment}  → secure file download
GET    /notifications             → notification list
POST   /notifications/{id}/read   → mark as read
```

---

## 6. Authorization — Laravel Policies

### `TicketPolicy`
| Method | Requester | Agent | Admin |
|---|---|---|---|
| `viewAny` | own only | all | all |
| `view` | own only | all | all |
| `create` | ✅ | ✅ | ✅ |
| `update` | ❌ | ✅ | ✅ |
| `delete` | ❌ | ❌ | ✅ |
| `addComment` | own only (public) | all | all |
| `addInternalNote` | ❌ | ✅ | ✅ |
| `assign` | ❌ | ✅ | ✅ |
| `changeStatus` | ❌ | ✅ | ✅ |

### `CommentPolicy`
| Method | Logic |
|---|---|
| `view` | Requesters cannot see `is_internal = true` comments |
| `create` | Requesters can only post public replies on their own tickets |

---

## 7. Services

### `TicketService`

Handles business logic kept out of controllers:

- `create(array $data, User $requester): Ticket`
  - Generates ticket number (`TMCWD-YYYY-#####`)
  - Sets `sla_due_at` via `SlaService`
  - Dispatches `TicketCreated` notification
  - Dispatches `UrgentTicketAlert` if priority is Urgent

- `updateStatus(Ticket $ticket, string $newStatus, User $agent): void`
  - Records `TicketActivity`
  - Calls `SlaService::pause()` / `resume()` as needed
  - Sets `resolved_at` or `closed_at`
  - Dispatches `TicketStatusUpdated` notification

- `assign(Ticket $ticket, ?int $agentId, User $actor): void`
  - Records activity
  - Dispatches `TicketAssigned` notification

- `addComment(Ticket $ticket, User $user, string $body, bool $isInternal): Comment`
  - If Requester adds comment on Resolved ticket → auto-reopen
  - Dispatches `TicketCommented` notification if public

### `SlaService`

- `calculateDueAt(Ticket $ticket): Carbon`
  - `created_at` + `priority.sla_hours` hours

- `pause(Ticket $ticket): void`
  - Sets `sla_paused_at = now()`

- `resume(Ticket $ticket): void`
  - Adds `(now() - sla_paused_at)` to `sla_paused_duration`
  - Clears `sla_paused_at`
  - Recalculates `sla_due_at` = original due + total paused duration

- `getStatus(Ticket $ticket): string`  → `'ok'`, `'warning'`, `'breached'`
  - If `sla_paused_at` is set → paused (not counting)
  - If `resolved_at` or `closed_at` → check if resolved before `sla_due_at`
  - Warning threshold: less than 20% of total SLA time remaining

---

## 8. Ticket Number Generation

```php
// In TicketService::create()
$year = now()->year;
$lastTicket = Ticket::whereYear('created_at', $year)->lockForUpdate()->latest('id')->first();
$sequence = $lastTicket ? (intval(substr($lastTicket->ticket_number, -5)) + 1) : 1;
$ticketNumber = 'TMCWD-' . $year . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
```
Wrapped in a `DB::transaction()` to prevent race conditions.

---

## 9. Notification Design

Each Laravel Notification class implements `toMail()` and `toDatabase()`.

| Class | Trigger | Recipients |
|---|---|---|
| `TicketCreated` | Ticket submitted | Requester (confirmation) |
| `TicketStatusUpdated` | Status change | Requester |
| `TicketCommented` | Public reply added | Requester (if agent replied), Agent (if requester replied) |
| `TicketAssigned` | Ticket assigned | Assigned Agent |
| `UrgentTicketAlert` | Urgent ticket created | All Admins + All Agents |

In-app notifications use Laravel's built-in database channel (`notifications` table). The notification bell component queries `auth()->user()->unreadNotifications`.

---

## 10. SLA Configuration (Seed Defaults)

| Priority | SLA Hours | Warning at | Color |
|---|---|---|---|
| Urgent | 2 | < 0.4 hrs remaining | Red |
| High | 8 | < 1.6 hrs remaining | Orange |
| Medium | 48 | < 9.6 hrs remaining | Yellow |
| Low | 120 | < 24 hrs remaining | Blue |

---

## 11. UI/UX Design

### 11.1 Color Palette (Tailwind)

| Use | Tailwind Class | Notes |
|---|---|---|
| Primary / Brand | `blue-700` / `blue-800` | Navigation, buttons, headings |
| Primary Light | `blue-50` / `blue-100` | Page backgrounds, card surfaces |
| Accent | `sky-500` | Links, hover states |
| Success | `green-600` | Resolved status, success messages |
| Warning | `yellow-500` | SLA warning, On Hold status |
| Danger | `red-600` | SLA breach, Urgent priority, errors |
| Neutral | `gray-600` / `gray-100` | Text, borders, table rows |

### 11.2 Layout — Authenticated (`layouts/app.blade.php`)

```
┌─────────────────────────────────────────────────────────┐
│  [TMCWD Logo]  IT Request Service        🔔  User ▾     │  ← Top nav (blue-800)
├──────────┬──────────────────────────────────────────────┤
│          │                                              │
│  Sidebar │  Main Content Area                          │
│  (nav    │  (role-aware)                               │
│  links)  │                                              │
│          │                                              │
└──────────┴──────────────────────────────────────────────┘
```

Sidebar links are conditionally rendered per role using `@role('admin')` Blade directives.

### 11.3 Layout — Guest (`layouts/guest.blade.php`)

```
┌──────────────────────────────────────────────────┐
│                                                  │
│        [TMCWD Logo Placeholder]                  │
│   Trece Martires City Water District             │
│         IT Request Service System                │
│                                                  │
│   ┌──────────────────────────────────────┐       │
│   │  Email: ________________________     │       │
│   │  Password: _____________________     │       │
│   │                                      │       │
│   │          [ Log In ]                  │       │
│   └──────────────────────────────────────┘       │
│                                                  │
└──────────────────────────────────────────────────┘
```

### 11.4 Status Badge Colors

| Status | Badge |
|---|---|
| Open | `bg-blue-100 text-blue-800` |
| In Progress | `bg-yellow-100 text-yellow-800` |
| On Hold | `bg-gray-100 text-gray-700` |
| Resolved | `bg-green-100 text-green-800` |
| Closed | `bg-gray-300 text-gray-600` |

### 11.5 Priority Badge Colors

| Priority | Badge |
|---|---|
| Low | `bg-blue-50 text-blue-600` |
| Medium | `bg-yellow-50 text-yellow-600` |
| High | `bg-orange-100 text-orange-700` |
| Urgent | `bg-red-100 text-red-700` |

---

## 12. Seed Data Plan

### Roles & Permissions
- Roles: `admin`, `agent`, `requester`
- Permissions (assigned to roles):
  - `admin`: all
  - `agent`: view-tickets, update-ticket-status, add-comment, assign-ticket
  - `requester`: create-ticket, view-own-tickets, add-public-reply

### Sample Users (one per department per role where applicable)

| Name | Email | Role | Department |
|---|---|---|---|
| IT Admin | admin@tmcwd.gov.ph | admin | IT |
| Juan dela Cruz | juan@tmcwd.gov.ph | agent | IT |
| Maria Santos | maria@tmcwd.gov.ph | agent | IT |
| Ana Reyes | ana@tmcwd.gov.ph | requester | Finance/Billing |
| Pedro Gomez | pedro@tmcwd.gov.ph | requester | Engineering |
| Rosa Mendoza | rosa@tmcwd.gov.ph | requester | Commercial/Customer Service |
| Carlo Bautista | carlo@tmcwd.gov.ph | requester | Administration |
| Liza Torres | liza@tmcwd.gov.ph | requester | Production/Plant Operations |
| Ben Navarro | ben@tmcwd.gov.ph | requester | Meter Reading |
| Joy Flores | joy@tmcwd.gov.ph | requester | Human Resources |

All seeded with password: `password` (bcrypt hashed).

### Sample Tickets
At least 15 sample tickets distributed across:
- All categories
- All priority levels
- All statuses (open, in_progress, on_hold, resolved, closed)
- Multiple departments as requesters
- Some assigned, some unassigned
- Some with SLA breaches (for testing dashboard flags)

---

## 13. Reports Design

### Ticket Volume by Department
- Query: `tickets GROUP BY department_id` with date range filter
- Display: HTML table + Chart.js bar chart
- Export: CSV download

### Average Resolution Time by Category
- Query: `AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at)) GROUP BY category_id`
- Display: HTML table
- Export: CSV download

### Monthly IT Performance PDF
- Uses `barryvdh/laravel-dompdf`
- Includes: period, total tickets, open/resolved/closed counts, avg resolution time, SLA breach count, table of all tickets in period
- Rendered from a Blade view (`admin/reports/pdf.blade.php`)

---

## 14. Security Considerations

- All routes require authentication via `auth` middleware
- Role checks via Spatie middleware (`role:admin`, `role:agent|admin`, etc.)
- Ticket/comment access enforced via Laravel Policies (not just middleware)
- File downloads go through `AttachmentController` which verifies the requesting user owns or can access the ticket
- File storage path: `storage/app/attachments/{ticket_id}/` — not in `public/`
- CSRF protection on all POST/PUT/PATCH/DELETE forms (Laravel default)
- Form inputs sanitized via Laravel Form Requests (validated, not raw `$_POST`)
- Internal notes query-filtered by `is_internal = false` in Requester views — never trust the client to filter sensitive data

---

## 15. Key Architectural Decisions

| Decision | Rationale |
|---|---|
| Blade + Tailwind (no SPA) | Matches team's PHP/Laravel skills; no JS build complexity; server-rendered is simpler for RBAC views |
| Services layer (TicketService, SlaService) | Keeps controllers thin; centralizes business logic that touches multiple models |
| Laravel Policies over gate closures | Easier to test, more explicit, integrates with Blade `@can` directives |
| Database notifications channel | No additional queue/Redis setup needed; simple in-app bell via DB query |
| No Filament (decided against) | Filament adds complexity and a separate admin panel paradigm; custom Blade admin is simpler, gives full control of UI/branding |
| CSV export via PHP | No additional package needed; `fputcsv` is sufficient |
| PDF via dompdf | Lightweight, no Node dependency, pure PHP |
