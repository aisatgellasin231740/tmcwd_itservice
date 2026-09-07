# Implementation Task List — TMCWD IT Request Service System

**Version:** 1.0  
**Date:** September 2026  
**Reference:** requirements.md, design.md

Each task is atomic and independently executable. Tasks within a phase can generally be parallelized within their phase; phases must be completed in order.

---

## Phase 0 — Project Bootstrap

### T-001 · Create Laravel Project
- Run `composer create-project laravel/laravel tmcwd_itservice` inside `c:\xampp\htdocs`
- Confirm Laravel 11 installed
- Set `APP_NAME=TMCWD IT Request Service` in `.env`
- Set `APP_URL=http://localhost/tmcwd_itservice/public`
- Configure database credentials in `.env` (`DB_DATABASE=tmcwd_itservice`, `DB_USERNAME`, `DB_PASSWORD`)
- Create the MySQL database `tmcwd_itservice` via phpMyAdmin

### T-002 · Install Core Packages
- `composer require spatie/laravel-permission`
- `composer require barryvdh/laravel-dompdf`
- `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
- Publish dompdf config: `php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"`
- Add `HasRoles` trait to `User` model

### T-003 · Install Laravel Breeze
- `composer require laravel/breeze --dev`
- `php artisan breeze:install blade`
- `npm install && npm run build`
- Verify `/login` route works

### T-004 · Install & Configure Tailwind CSS
- Confirm Tailwind 3 is installed via Breeze
- Extend `tailwind.config.js` with TMCWD brand colors (primary blue palette)
- Add custom component classes to `resources/css/app.css` (badge base styles)
- Run `npm run build` to verify

### T-005 · Configure Mail & Storage
- Set mail driver in `.env` (`MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, etc.)
- Set `MAIL_FROM_ADDRESS=noreply@tmcwd.gov.ph`, `MAIL_FROM_NAME="TMCWD IT Service"`
- Ensure `FILESYSTEM_DISK=local` in `.env`
- Run `php artisan storage:link` (not strictly needed since attachments stay private)
- Create `storage/app/attachments/` directory
- Add `attachments/` path to `.gitignore` storage ignore

---

## Phase 1 — Database & Models

### T-006 · Migration: `departments`
- Create migration for `departments` table (id, name, is_active, timestamps)
- Add unique index on `name`

### T-007 · Migration: `priorities`
- Create migration for `priorities` table (id, name, sla_hours, color_code, sort_order, timestamps)

### T-008 · Migration: `categories`
- Create migration for `categories` table (id, name, is_active, timestamps)
- Add unique index on `name`

### T-009 · Migration: Modify `users` table
- Add columns to existing `users` migration/new migration: `department_id` (FK → departments), `is_active` (boolean, default true)
- Add foreign key constraint: `department_id` references `departments.id`

### T-010 · Migration: `tickets`
- Create migration for `tickets` table per schema in design.md
- Columns: id, ticket_number (unique), title, description, requester_id (FK users), department_id (FK), category_id (FK), priority_id (FK), assigned_to (FK users, nullable), status (enum), sla_due_at, sla_paused_at, sla_paused_duration, resolved_at, closed_at, timestamps
- Add indexes on: status, priority_id, department_id, assigned_to, created_at

### T-011 · Migration: `comments`
- Columns: id, ticket_id (FK cascade delete), user_id (FK), body (text), is_internal (boolean), timestamps
- Add index on: ticket_id, is_internal

### T-012 · Migration: `attachments`
- Columns: id, ticket_id (FK cascade delete), user_id (FK), original_name, stored_path, mime_type, size, timestamps
- Add index on ticket_id

### T-013 · Migration: `ticket_activities`
- Columns: id, ticket_id (FK cascade delete), user_id (FK nullable), action, old_value, new_value, description, timestamps
- Add index on ticket_id

### T-014 · Run Spatie Permission Migration
- `php artisan migrate` (includes Spatie's `roles`, `permissions`, and pivot tables)

### T-015 · Model: `Department`
- `fillable`: name, is_active
- `hasMany(User::class)`
- `hasMany(Ticket::class)`
- Local scope: `scopeActive()`

### T-016 · Model: `Category`
- `fillable`: name, is_active
- `hasMany(Ticket::class)`
- Local scope: `scopeActive()`

### T-017 · Model: `Priority`
- `fillable`: name, sla_hours, color_code, sort_order
- `hasMany(Ticket::class)`
- `defaultOrderBy('sort_order')`

### T-018 · Model: `User`
- Add `HasRoles` (Spatie)
- `fillable`: name, email, password, department_id, is_active
- `hidden`: password, remember_token
- `casts`: is_active → boolean, password → hashed
- `belongsTo(Department::class)`
- `hasMany(Ticket::class, 'requester_id')`
- `hasMany(Ticket::class, 'assigned_to')`
- `hasMany(Comment::class)`

### T-019 · Model: `Ticket`
- `fillable`: all columns except id, ticket_number (set in service)
- `casts`: status → string, sla_due_at/resolved_at/closed_at/sla_paused_at → datetime
- `belongsTo(User::class, 'requester_id')`
- `belongsTo(User::class, 'assigned_to')`
- `belongsTo(Department::class)`
- `belongsTo(Category::class)`
- `belongsTo(Priority::class)`
- `hasMany(Comment::class)`
- `hasMany(Attachment::class)`
- `hasMany(TicketActivity::class)`
- Accessor: `getSlaStatusAttribute()` → delegates to SlaService

### T-020 · Model: `Comment`
- `fillable`: ticket_id, user_id, body, is_internal
- `casts`: is_internal → boolean
- `belongsTo(Ticket::class)`
- `belongsTo(User::class)`
- Local scope: `scopePublic()` (is_internal = false)

### T-021 · Model: `Attachment`
- `fillable`: ticket_id, user_id, original_name, stored_path, mime_type, size
- `belongsTo(Ticket::class)`
- `belongsTo(User::class)`
- Accessor: `getHumanSizeAttribute()` — human-readable file size

### T-022 · Model: `TicketActivity`
- `fillable`: ticket_id, user_id, action, old_value, new_value, description
- `belongsTo(Ticket::class)`
- `belongsTo(User::class)`

---

## Phase 2 — Seeders & Roles

### T-023 · Seeder: `DepartmentSeeder`
- Seed 9 departments: Administration, Finance/Billing, Commercial/Customer Service, Engineering, Production/Plant Operations, Meter Reading, Human Resources, IT, Other

### T-024 · Seeder: `PrioritySeeder`
- Seed 4 priorities with SLA hours: Low (120h), Medium (48h), High (8h), Urgent (2h)
- Include sort_order and color_code per design.md

### T-025 · Seeder: `CategorySeeder`
- Seed 10 categories: Hardware Issue, Software Issue, Network/Internet Issue, Printer/Scanner Issue, Email/Account Access, Billing System Issue, GIS/Mapping System Issue, New Equipment Request, Account Creation/Access Request, Other

### T-026 · Seeder: `RolePermissionSeeder`
- Create roles: `admin`, `agent`, `requester`
- Create permissions: `create-ticket`, `view-own-tickets`, `view-all-tickets`, `update-ticket`, `delete-ticket`, `assign-ticket`, `add-public-reply`, `add-internal-note`, `manage-users`, `manage-settings`, `view-reports`
- Assign permissions to roles per design.md

### T-027 · Seeder: `UserSeeder`
- Seed 10 sample users per design.md seed data plan (admin, 2 agents, 7 requesters across departments)
- All passwords: `password`
- Assign roles using Spatie `$user->assignRole()`

### T-028 · Seeder: `TicketSeeder`
- Seed 15+ sample tickets distributed across all categories, priorities, statuses, and departments
- Some assigned to agents, some unassigned
- Include past `created_at` timestamps (use `Carbon::parse('2026-08-01')` etc.) to simulate history
- Create at least 3 tickets with `sla_due_at` in the past (SLA breached) for dashboard testing
- Add sample comments (public and internal) and at least one attachment record per ticket
- Add `TicketActivity` records for seeded status changes

### T-029 · Wire `DatabaseSeeder`
- Call seeders in order: Department → Priority → Category → RolePermission → User → Ticket
- Test: `php artisan migrate:fresh --seed` runs without errors

---

## Phase 3 — Auth & Middleware

### T-030 · Middleware: `EnsureUserIsActive`
- Check `auth()->user()->is_active`; if false, log out and redirect to `/login` with error message "Your account has been deactivated."
- Register in `bootstrap/app.php` middleware aliases

### T-031 · Apply Middleware to Routes
- Apply `EnsureUserIsActive` globally to all `auth` guarded routes in `web.php`

### T-032 · Role-Based Redirect After Login
- Override `LoginResponse` (or use `redirectTo()` on `AuthenticatedSessionController`) to redirect:
  - `admin` → `/admin/dashboard`
  - `agent` → `/agent/dashboard`
  - `requester` → `/dashboard`

### T-033 · Policies: `TicketPolicy`
- Implement all methods per design.md policy table: `viewAny`, `view`, `create`, `update`, `delete`, `addComment`, `addInternalNote`, `assign`, `changeStatus`
- Register policy in `AuthServiceProvider` (or `AppServiceProvider` in Laravel 11)

### T-034 · Policies: `CommentPolicy`
- `view`: Requesters cannot see internal comments
- `create`: Requesters only on own tickets, agents/admin on all
- Register policy

---

## Phase 4 — Services

### T-035 · `SlaService`
- `calculateDueAt(Priority $priority, Carbon $createdAt): Carbon`
- `pause(Ticket $ticket): void` — sets `sla_paused_at`
- `resume(Ticket $ticket): void` — accumulates `sla_paused_duration`, recalculates `sla_due_at`
- `getStatus(Ticket $ticket): string` → `'ok'` | `'warning'` | `'breached'` | `'paused'` | `'met'`
  - `'met'` when resolved/closed within SLA
  - `'breached'` when resolved/closed after SLA or still open past SLA
  - `'warning'` when < 20% of total SLA hours remaining

### T-036 · `TicketService`
- `generateTicketNumber(): string` — DB transaction, lockForUpdate, zero-pad sequential per year
- `create(array $validated, User $requester): Ticket`
- `updateStatus(Ticket $ticket, string $status, User $actor): void`
- `assign(Ticket $ticket, ?int $agentId, User $actor): void`
- `addComment(Ticket $ticket, User $user, string $body, bool $isInternal): Comment`
  - Auto-reopen logic for Requester reply on Resolved ticket
- `storeAttachments(Ticket $ticket, array $files, User $uploader): void`
  - Store each file to `attachments/{ticket_id}/` using `Storage::putFileAs()`
  - Create `Attachment` record

---

## Phase 5 — Notifications

### T-037 · Notification: `TicketCreated`
- Channels: `mail`, `database`
- `toMail()`: subject "Ticket [TMCWD-YYYY-#####] Created", body with ticket title, category, priority, link to ticket
- `toDatabase()`: type, ticket_id, ticket_number, message

### T-038 · Notification: `TicketStatusUpdated`
- Channels: `mail`, `database`
- Notify Requester
- `toMail()`: subject "Your ticket status has been updated to [Status]"
- Include old status → new status

### T-039 · Notification: `TicketCommented`
- Channels: `mail`, `database`
- Notify Requester (when agent replies) or assigned Agent (when requester replies)
- `toMail()`: subject "New reply on ticket [TMCWD-YYYY-#####]"

### T-040 · Notification: `TicketAssigned`
- Channels: `mail`, `database`
- Notify newly assigned Agent
- `toMail()`: subject "Ticket [TMCWD-YYYY-#####] assigned to you"

### T-041 · Notification: `UrgentTicketAlert`
- Channels: `mail`, `database`
- Recipients: all users with `admin` or `agent` role
- `toMail()`: subject "⚠️ URGENT Ticket Submitted: [title]", with requester, department, description excerpt

---

## Phase 6 — Layouts & Components

### T-042 · Guest Layout (`layouts/guest.blade.php`)
- Centered card design on blue-50 background
- TMCWD logo placeholder (`<img src="{{ asset('images/tmcwd-logo.png') }}" alt="TMCWD Logo">`) with fallback text
- App name subtitle: "IT Request Service System"
- Responsive: works on mobile, tablet, desktop

### T-043 · App Layout (`layouts/app.blade.php`)
- Top navigation bar (blue-800): logo placeholder, app name, notification bell, user avatar/name, logout
- Sidebar (white, shadow): role-aware navigation links using `@role()` Blade directives
  - Requester links: Dashboard, My Tickets, New Ticket
  - Agent links: Dashboard, Ticket Queue, My Assignments
  - Admin links: Dashboard, All Tickets, Users, Departments, Categories, Priorities, Reports
- Main content area with `@yield('content')` or `{{ $slot }}`
- Flash message component (success/error banners)
- Active link highlight in sidebar

### T-044 · Blade Component: `ticket-badge`
- Props: `status` or `priority`
- Renders correct colored badge span per design.md color tables
- Usage: `<x-ticket-badge status="open" />` or `<x-ticket-badge priority="urgent" />`

### T-045 · Blade Component: `sla-indicator`
- Props: `ticket` (Ticket model)
- Renders: ✅ Met / ⚠️ Warning (with time remaining) / 🔴 Breached (with overdue time) / ⏸ Paused
- Uses `$ticket->sla_status` accessor
- Hidden for Requesters (only shown to Agents/Admins)

### T-046 · Blade Component: `notification-bell`
- Fetches `auth()->user()->unreadNotifications->count()`
- Bell icon (Heroicon), red badge with count if > 0
- Dropdown showing latest 5 unread notifications with mark-as-read links

### T-047 · Blade Component: `stat-card`
- Props: label, value, icon, color
- Used on all dashboards for summary statistics

### T-048 · Place TMCWD Logo Asset
- Add placeholder PNG at `public/images/tmcwd-logo.png` (a simple text-based placeholder image or a 200×80 blue rectangle with "TMCWD" text)
- Reference via `asset('images/tmcwd-logo.png')` in layouts

---

## Phase 7 — Requester Features

### T-049 · Requester Dashboard (`requester/DashboardController`)
- Route: `GET /dashboard`
- Query: count of own tickets by status (total, open, resolved, closed)
- Pass to view: stat cards data + recent 5 tickets list

### T-050 · Requester Dashboard View (`requester/dashboard.blade.php`)
- Stat cards: Total Tickets, Open, Resolved
- Recent tickets table: ID, Title, Category, Priority badge, Status badge, Date
- "Submit New Ticket" CTA button

### T-051 · Requester Ticket List View (`requester/tickets/index.blade.php`)
- Filterable by status (select) and date range (two date inputs)
- Paginated table (15 per page)
- Each row: ticket number (link), title, category, priority badge, status badge, submitted date

### T-052 · Create Ticket Form (`requester/tickets/create.blade.php`)
- Fields: Title (text), Description (textarea), Department (select, pre-filled from user), Category (select, active only), Priority (select), Attachments (multiple file input, accept="image/*,.pdf,.doc,.docx,.xls,.xlsx")
- Client-side: show selected file names
- Server-side validation via `StoreTicketRequest`:
  - title: required, max 255
  - description: required
  - category_id: required, exists
  - priority_id: required, exists
  - department_id: required, exists
  - attachments.*: nullable, file, max 10240 (10 MB), mimes allowed list

### T-053 · `StoreTicketRequest` + `Requester\TicketController@store`
- Authorize: any authenticated user
- Call `TicketService::create()` and `TicketService::storeAttachments()`
- Dispatch `TicketCreated` notification to requester
- Dispatch `UrgentTicketAlert` if Urgent priority
- Redirect to `requester.tickets.show` with success message

### T-054 · Requester Ticket Detail View (`requester/tickets/show.blade.php`)
- Header: Ticket number, Title, Status badge, Priority badge, SLA status (hidden from requester — not shown)
- Metadata panel: Department, Category, Submitted on, Last updated
- Description block
- Attachments list with download links (route through `AttachmentController`)
- Activity/comment thread: public replies only (no internal notes shown), chronological, user avatar/name, timestamp
- Reply form at bottom (textarea + submit) — disabled/hidden if status is Closed

### T-055 · `StoreCommentRequest` + Comment store for Requester
- Validate: body required, min 5 chars
- Authorize via `CommentPolicy` (own ticket, not internal)
- Call `TicketService::addComment()` (auto-reopen if resolved)
- Redirect back with success flash

---

## Phase 8 — Agent Features

### T-056 · Agent Dashboard (`agent/DashboardController`)
- Route: `GET /agent/dashboard`
- Queries:
  - Count assigned to me: open, in_progress, on_hold
  - Count resolved by me today
  - Count unassigned open tickets
  - SLA breached tickets assigned to me

### T-057 · Agent Dashboard View (`agent/dashboard.blade.php`)
- Stat cards: Assigned to Me, In Progress, Resolved Today, SLA Breached
- "Unassigned Queue" section: list of top 10 unassigned open tickets (newest first), with assign-to-self button
- "My Tickets" section: list of 10 most urgent assigned tickets with SLA indicator

### T-058 · Agent Ticket Queue View (`agent/tickets/index.blade.php`)
- Filters: Status (multi-select or select), Priority, Category, Department, Assignee (Me / Unassigned / All)
- Sortable columns: Date, Priority (by sort_order), Status, SLA Due
- Paginated table (20 per page)
- SLA indicator column (colored flag: ok/warning/breached)
- Each row links to agent ticket detail

### T-059 · Agent Ticket Detail View (`agent/tickets/show.blade.php`)
- Full ticket metadata with edit controls inline or in sidebar panel:
  - Status dropdown (Open/In Progress/On Hold/Resolved/Closed) + Update button
  - Assignee dropdown (list of active agents) + Assign button
  - Priority display (agents can see, admin can change — or allow agent to change per policy)
- Activity/comment thread: shows ALL comments including internal notes (with "Internal Note" badge)
- Add Comment form: two-button choice "Public Reply" / "Internal Note" (radio or tab toggle)
- Attachments panel with download links

### T-060 · `Agent\TicketController@update`
- Route: `PATCH /agent/tickets/{ticket}`
- Authorize via `TicketPolicy@update` (agent|admin)
- Handle: status change → `TicketService::updateStatus()`; assign → `TicketService::assign()`
- Validate: use `UpdateTicketRequest`
- Redirect back with success flash

### T-061 · `Agent\TicketController@storeComment`
- Route: `POST /agent/tickets/{ticket}/comments`
- Validate: body, is_internal (boolean)
- Authorize via `CommentPolicy`
- Call `TicketService::addComment()`
- Redirect back

---

## Phase 9 — Admin Features

### T-062 · Admin Dashboard (`admin/DashboardController`)
- Route: `GET /admin/dashboard`
- Queries:
  - Total open tickets
  - Avg resolution time (hours) — last 30 days
  - SLA breach count (open tickets past sla_due_at)
  - Tickets by department (for bar chart)
  - Tickets by category (for pie chart)
  - Tickets by status (for overview)

### T-063 · Admin Dashboard View (`admin/dashboard.blade.php`)
- Stat cards: Total Open, Avg Resolution Time, SLA Breaches, Closed This Month
- Bar chart: Tickets by Department (Chart.js)
- Doughnut chart: Tickets by Category (Chart.js)
- Recent urgent tickets table (last 10 urgent, with SLA flag)
- Inline Chart.js scripts, data passed via `@json()` from controller

### T-064 · Admin: All Tickets View (`admin/tickets/index.blade.php`)
- Same filters as Agent queue, plus assignee filter (any user)
- Additional column: Requester name
- "Delete" button on each row (soft confirm via JS)

### T-065 · Admin: Ticket Detail & Delete
- Reuse agent detail view with additional admin actions:
  - Delete ticket button (POST to `DELETE /admin/tickets/{ticket}`) with confirmation modal
  - Change priority (admin only)
- `Admin\TicketController@destroy` → authorize `TicketPolicy@delete`, soft-delete or hard-delete with cascade

### T-066 · Admin: User Management (`admin/UserController`)
- Routes: index, create, store, edit, update, (soft) delete
- `index`: paginated user list with role and department badges, active/inactive indicator, action buttons
- `create/edit` form: name, email, password (with confirmation, optional on edit), department (select), role (select), is_active toggle
- `StoreUserRequest` / `UpdateUserRequest`: validate email unique (ignore self on edit), password confirmed
- On store: `$user->assignRole($request->role)`
- On update: sync role with `$user->syncRoles([$request->role])`

### T-067 · Admin: Department Management (`admin/DepartmentController`)
- Routes: index + inline create/edit (modal or same-page form)
- List all departments with active toggle
- `StoreDepartmentRequest`: name required, unique, max 100
- Cannot delete a department that has associated users or tickets (show validation error)

### T-068 · Admin: Category Management (`admin/CategoryController`)
- Same pattern as departments
- `StoreCategoryRequest`: name required, unique, max 100

### T-069 · Admin: Priority / SLA Settings (`admin/PriorityController`)
- Route: `GET /admin/priorities` (list) + `PUT /admin/priorities/{priority}` (update SLA hours)
- No add/delete (priorities are fixed); only edit SLA hours and color
- `UpdatePriorityRequest`: sla_hours must be positive numeric

---

## Phase 10 — Attachments & File Downloads

### T-070 · `AttachmentController@download`
- Route: `GET /attachments/{attachment}`
- Authorize: user can view the ticket the attachment belongs to (via `TicketPolicy@view`)
- Return `Storage::download($attachment->stored_path, $attachment->original_name)`

### T-071 · File Upload in TicketService
- `storeAttachments()`: iterate `$request->file('attachments')`, store each with `Storage::putFileAs("attachments/{$ticket->id}", $file, $uniqueName)`
- Create `Attachment` model record with original_name, stored_path, mime_type, size
- Delete from storage if ticket is deleted (`Ticket::deleting` observer or manual in controller)

---

## Phase 11 — Notifications & In-App Bell

### T-072 · Wire All Notifications in Services
- Confirm all 5 notification classes are dispatched from the correct `TicketService` methods
- Test each with `php artisan tinker` or a simple test route

### T-073 · Notification Bell & List View
- `NotificationController@index`: `GET /notifications` — list all notifications for auth user, mark all as read
- `NotificationController@markRead`: `POST /notifications/{id}/read`
- Notification bell component (`T-046`) wired to live DB count
- Notification list page: type icon, message, ticket link, time ago, read/unread styling

---

## Phase 12 — Reports

### T-074 · Report: Ticket Volume by Department
- `Admin\ReportController@index`
- Query: tickets grouped by department, optionally filtered by date range (start_date, end_date inputs)
- Return: table of department → ticket count, plus Chart.js bar chart

### T-075 · Report: Avg Resolution Time by Category
- Query: `AVG(TIMESTAMPDIFF(HOUR, created_at, resolved_at))` grouped by category (only resolved/closed tickets)
- Return: table of category → avg hours

### T-076 · CSV Export
- `Admin\ReportController@exportCsv`
- Route: `GET /admin/reports/export-csv` (with same date range filters)
- Use `fputcsv` to stream CSV response with headers:
  - Ticket Volume: Department, Count
  - Resolution Time: Category, Avg Hours
- Use `StreamedResponse` for memory efficiency

### T-077 · PDF Export (Monthly Performance)
- `Admin\ReportController@exportPdf`
- Route: `GET /admin/reports/export-pdf`
- Blade view: `admin/reports/pdf.blade.php`
  - Header: TMCWD logo placeholder, "Monthly IT Performance Report", date range
  - Summary table: total tickets, open, resolved, closed, avg resolution time, SLA breach count
  - Ticket listing table (all tickets in period, sorted by date)
- Return `PDF::loadView(...)->download('tmcwd-it-report.pdf')`

---

## Phase 13 — Polish & Validation

### T-078 · Form Validation Error Display
- Ensure all forms show `@error('field')` messages below inputs
- Add `old()` values to all form fields to preserve input on validation failure
- Style error messages: `text-red-600 text-sm mt-1`

### T-079 · Flash Messages
- Add flash banner in `layouts/app.blade.php` reading `session('success')` and `session('error')`
- Style: green banner for success, red for error, auto-dismiss after 5s (JS)

### T-080 · Pagination Styling
- Publish Tailwind pagination view: `php artisan vendor:publish --tag=laravel-pagination`
- Or use `$tickets->links('vendor.pagination.tailwind')` in all index views

### T-081 · 403 / 404 Error Pages
- Create `resources/views/errors/403.blade.php` — branded "Access Denied" page with back link
- Create `resources/views/errors/404.blade.php` — branded "Page Not Found" page

### T-082 · Responsive Check
- Test all views at 768px (tablet) breakpoint
- Sidebar collapses to hamburger menu on mobile/tablet (Alpine.js toggle or simple CSS)
- Tables use horizontal scroll on small screens (`overflow-x-auto`)

### T-083 · Security Review
- Confirm all routes have appropriate middleware (`auth`, `role:`)
- Confirm all policies are called via `$this->authorize()` in controllers
- Confirm `is_internal` filtering is done server-side in query, not in Blade
- Confirm attachment download goes through `AttachmentController` (no public path)
- Confirm CSRF tokens present on all forms (`@csrf`)

---

## Phase 14 — Testing & Seeding

### T-084 · Run Full Migration + Seed
- `php artisan migrate:fresh --seed`
- Verify: all tables created, all seed data present in phpMyAdmin
- Log in as each role (admin, agent, requester) and verify redirect and access

### T-085 · Manual Smoke Test — Requester Flow
- Log in as `ana@tmcwd.gov.ph` (Requester)
- Create a new ticket (Hardware Issue, High priority, with attachment)
- Verify ticket number generated (TMCWD-2026-#####)
- Verify email notification sent (check mail log or mailtrap)
- View ticket detail; add a public reply
- Verify ticket appears in dashboard list

### T-086 · Manual Smoke Test — Agent Flow
- Log in as `juan@tmcwd.gov.ph` (Agent)
- View unassigned queue; assign ticket to self
- Change status to In Progress; add internal note
- Add public reply; verify requester receives notification
- Resolve ticket; verify resolved_at set and SLA status updated

### T-087 · Manual Smoke Test — Admin Flow
- Log in as `admin@tmcwd.gov.ph` (Admin)
- View admin dashboard — verify charts render with seeded data
- Create a new user (agent role)
- Edit a department name
- View reports page; export CSV; export PDF
- Delete a test ticket

### T-088 · SLA Flag Verification
- Verify at least 3 seeded tickets show red "Breached" SLA badge on agent/admin dashboards
- Verify the SLA pause/resume logic: set a ticket to On Hold, check `sla_paused_at` set in DB; set back to In Progress, verify `sla_due_at` extended

---

## Phase 15 — Final Cleanup

### T-089 · Remove Debug/Unused Code
- Remove any `dd()`, `dump()`, `var_dump()` statements
- Remove unused imports and commented-out code blocks
- Ensure no hardcoded credentials or test emails in source files

### T-090 · `.env.example` Update
- Document all required `.env` variables: APP_*, DB_*, MAIL_*, FILESYSTEM_DISK

### T-091 · `README.md`
- Installation steps: clone, `composer install`, `npm install && npm run build`, copy `.env.example` to `.env`, `php artisan key:generate`, configure DB, `php artisan migrate --seed`
- List of seeded user credentials
- Brief feature overview and role descriptions
- XAMPP-specific note: point virtual host or access via `http://localhost/tmcwd_itservice/public`

---

## Task Summary

| Phase | Tasks | Description |
|---|---|---|
| 0 | T-001 – T-005 | Project Bootstrap |
| 1 | T-006 – T-022 | Database & Models |
| 2 | T-023 – T-029 | Seeders & Roles |
| 3 | T-030 – T-034 | Auth & Middleware |
| 4 | T-035 – T-036 | Services |
| 5 | T-037 – T-041 | Notifications |
| 6 | T-042 – T-048 | Layouts & Components |
| 7 | T-049 – T-055 | Requester Features |
| 8 | T-056 – T-061 | Agent Features |
| 9 | T-062 – T-069 | Admin Features |
| 10 | T-070 – T-071 | Attachments & File Downloads |
| 11 | T-072 – T-073 | Notifications & In-App Bell |
| 12 | T-074 – T-077 | Reports |
| 13 | T-078 – T-083 | Polish & Validation |
| 14 | T-084 – T-088 | Testing & Seeding |
| 15 | T-089 – T-091 | Final Cleanup |
| **Total** | **91 tasks** | |
