# Requirements — TMCWD IT Request Service System

**Organization:** Trece Martires City Water District (TMCWD)  
**Document Type:** Software Requirements Specification (SRS)  
**Version:** 1.0  
**Date:** September 2026

---

## 1. Introduction

### 1.1 Purpose
This document specifies the functional and non-functional requirements for the TMCWD IT Request Service System — an internal helpdesk and ticketing platform that enables TMCWD employees to submit IT support requests and allows IT staff to manage, track, and resolve those requests.

### 1.2 Scope
The system is an internal web application accessible to all TMCWD staff via a browser on the organization's network. It replaces informal request channels (verbal, email, messaging apps) with a structured, trackable workflow. It is not customer-facing.

### 1.3 Definitions
| Term | Meaning |
|---|---|
| Ticket | A support request submitted by an employee |
| Requester | An employee who submits a ticket |
| Agent | An IT staff member or technician who resolves tickets |
| Admin | The IT Head or Supervisor who manages the system |
| SLA | Service Level Agreement — target time to resolve a ticket |
| SLA Breach | When a ticket is not resolved within its SLA time |
| Internal Note | A comment on a ticket visible only to Agents and Admins |
| Public Reply | A comment on a ticket visible to the Requester |

---

## 2. Stakeholders

| Role | Responsibilities |
|---|---|
| Requester (Employee) | Submits tickets, tracks progress, communicates with IT |
| IT Agent (Technician) | Triages, works, and resolves tickets |
| Admin (IT Head) | Manages system configuration, users, reports |

---

## 3. Functional Requirements

### 3.1 Authentication & User Management

| ID | Requirement |
|---|---|
| AUTH-01 | The system shall provide email/password login using Laravel Breeze session-based authentication. |
| AUTH-02 | Each user account shall have: full name, email, password, department, role, and active/inactive status. |
| AUTH-03 | The system shall enforce role-based access control with three roles: **Requester**, **Agent**, **Admin** (managed via Spatie Laravel-Permission). |
| AUTH-04 | A user can hold only one role at a time. |
| AUTH-05 | Admin shall be able to create, edit, deactivate, and delete user accounts. |
| AUTH-06 | Admin shall be able to assign or change a user's role and department. |
| AUTH-07 | Inactive users shall not be able to log in. |
| AUTH-08 | Password reset via email link shall be supported. |

### 3.2 Departments

| ID | Requirement |
|---|---|
| DEPT-01 | The system shall maintain a list of TMCWD departments, pre-seeded with: Administration, Finance/Billing, Commercial/Customer Service, Engineering, Production/Plant Operations, Meter Reading, Human Resources, IT, Other. |
| DEPT-02 | Admin shall be able to add, edit, and deactivate departments. |
| DEPT-03 | Each user account shall be associated with exactly one department. |

### 3.3 Ticket Categories

| ID | Requirement |
|---|---|
| CAT-01 | The system shall maintain a list of ticket categories, pre-seeded with: Hardware Issue, Software Issue, Network/Internet Issue, Printer/Scanner Issue, Email/Account Access, Billing System Issue, GIS/Mapping System Issue, New Equipment Request, Account Creation/Access Request, Other. |
| CAT-02 | Admin shall be able to add, edit, and deactivate categories. |

### 3.4 Priority Levels

| ID | Requirement |
|---|---|
| PRI-01 | The system shall support four priority levels: **Low**, **Medium**, **High**, **Urgent**. |
| PRI-02 | Each priority level shall have a configured SLA resolution target time (default: Urgent = 2 hrs, High = 8 hrs, Medium = 2 days, Low = 5 days). |
| PRI-03 | Admin shall be able to edit SLA target times per priority. |

### 3.5 Ticket Submission (Requester)

| ID | Requirement |
|---|---|
| TICK-01 | Authenticated Requesters shall be able to create a new ticket with the following fields: title (required), description (required, rich text), department (pre-filled from user profile, editable), category (required, from list), priority (required), and file attachments (optional). |
| TICK-02 | The system shall auto-generate a unique ticket ID in the format `TMCWD-YYYY-#####` (e.g., `TMCWD-2026-00001`), where YYYY is the current year and ##### is a zero-padded sequential number that resets annually. |
| TICK-03 | File attachments shall support common formats (images, PDF, Office documents) and enforce a maximum file size of 10 MB per file. |
| TICK-04 | Multiple files may be attached to a single ticket. |
| TICK-05 | Upon submission, the ticket status shall be set to **Open** and a creation timestamp recorded. |

### 3.6 Ticket Tracking (Requester)

| ID | Requirement |
|---|---|
| TICK-06 | Requesters shall only be able to view their own tickets. |
| TICK-07 | The Requester dashboard shall list their tickets with: ticket ID, title, category, priority, status, and date submitted. |
| TICK-08 | Requesters shall be able to filter their ticket list by status and date range. |
| TICK-09 | Requesters shall be able to view the full ticket detail including status history, public replies, and attachments. |
| TICK-10 | Requesters shall be able to add a public reply (comment) to their open ticket. |
| TICK-11 | Requesters shall NOT be able to edit or delete a ticket after submission. |

### 3.7 Ticket Management (Agent)

| ID | Requirement |
|---|---|
| TICK-12 | Agents shall be able to view all tickets in the general (unassigned) queue and all tickets assigned to them. |
| TICK-13 | Agents shall be able to filter the ticket list by: status, priority, category, department, and assignee (self or all). |
| TICK-14 | Agents shall be able to assign a ticket to themselves or reassign it to another Agent. |
| TICK-15 | Agents shall be able to update ticket status to: **Open**, **In Progress**, **On Hold**, **Resolved**, **Closed**. |
| TICK-16 | Agents shall be able to add a **public reply** (visible to Requester) or an **internal note** (visible only to Agents/Admins). |
| TICK-17 | All status changes and assignments shall be recorded in a ticket activity log (history). |
| TICK-18 | When an Agent sets status to **Resolved**, the ticket resolution timestamp shall be recorded. |
| TICK-19 | Requesters shall be able to reopen a Resolved ticket by adding a reply, which sets the status back to **Open** automatically. |

### 3.8 Ticket Administration (Admin)

| ID | Requirement |
|---|---|
| TICK-20 | Admin shall have full visibility into all tickets regardless of department or assignee. |
| TICK-21 | Admin shall be able to perform all Agent actions on any ticket. |
| TICK-22 | Admin shall be able to delete a ticket (with confirmation). |
| TICK-23 | Admin shall be able to manage (add/edit/deactivate) departments, categories, and priority SLA settings. |

### 3.9 Notifications

| ID | Requirement |
|---|---|
| NOTIF-01 | An email notification shall be sent to the Requester when their ticket is: created (confirmation), updated (status change), commented on (public reply), or resolved/closed. |
| NOTIF-02 | An email notification shall be sent to the assigned Agent when: a ticket is assigned to them, a Requester adds a public reply to their ticket. |
| NOTIF-03 | When a ticket is created with **Urgent** priority, an email notification shall be sent immediately to all Admin users and all Agents. |
| NOTIF-04 | Email notifications shall be sent using Laravel's Notification system with a Mailable (configurable SMTP). |
| NOTIF-05 | Users shall be able to view a simple in-app notification bell showing unread notifications (new assignment, new reply on own tickets). |

### 3.10 SLA Tracking

| ID | Requirement |
|---|---|
| SLA-01 | The system shall calculate SLA due date/time when a ticket is created: `created_at` + priority SLA target time. |
| SLA-02 | Tickets approaching SLA breach (within 20% of remaining time) shall be visually flagged (yellow warning) on Agent and Admin dashboards. |
| SLA-03 | Tickets that have breached SLA shall be visually flagged (red) on Agent and Admin dashboards. |
| SLA-04 | SLA timer shall pause when a ticket is set to **On Hold** and resume when set back to a non-hold status. |
| SLA-05 | SLA timer shall stop when a ticket reaches **Resolved** or **Closed**. |

### 3.11 Dashboard

| ID | Requirement |
|---|---|
| DASH-01 | **Requester Dashboard:** Summary cards (total tickets, open, resolved), ticket list with status badges and filters (status, date range). |
| DASH-02 | **Agent Dashboard:** Summary cards (assigned to me, open queue, resolved today), ticket queue with filters (status, priority, category, department, assigned to me / unassigned). SLA breach flags visible. |
| DASH-03 | **Admin Dashboard:** Overview stats — total open tickets, avg. resolution time, tickets by department (bar chart), tickets by category (pie/doughnut chart), SLA breach count. |

### 3.12 Reports (Admin)

| ID | Requirement |
|---|---|
| RPT-01 | Admin shall be able to view a Ticket Volume by Department report (filterable by date range). |
| RPT-02 | Admin shall be able to view an Average Resolution Time by Category report. |
| RPT-03 | Admin shall be able to export reports as CSV. |
| RPT-04 | Admin shall be able to export a monthly IT performance summary as PDF. |

---

## 4. Non-Functional Requirements

| ID | Requirement |
|---|---|
| NFR-01 | The system shall be responsive and usable on desktop (1920×1080) and tablet (768px+) screens. |
| NFR-02 | All forms shall use server-side validation via Laravel Form Requests, with user-friendly error messages. |
| NFR-03 | Role-based access shall be enforced via Laravel Policies; unauthorized access attempts shall return HTTP 403. |
| NFR-04 | File uploads shall be stored on the local disk using Laravel Storage facade; file paths shall not be publicly guessable. |
| NFR-05 | Passwords shall be hashed using bcrypt (Laravel default). |
| NFR-06 | The system shall protect against CSRF attacks (Laravel default CSRF tokens). |
| NFR-07 | The application shall be built on the latest Laravel LTS release. |
| NFR-08 | Database schema shall be managed exclusively through Laravel migrations. |
| NFR-09 | Seed data shall include: all 9 departments, 10 ticket categories, 4 priority levels with SLA times, at least one sample user per role per department, and sample tickets in various states. |
| NFR-10 | The UI shall use a blue-toned color palette consistent with TMCWD's water utility branding. |
| NFR-11 | The header and login page shall include a placeholder for the TMCWD logo. |

---

## 5. Data Requirements

### 5.1 Core Entities

| Entity | Key Attributes |
|---|---|
| User | id, name, email, password, department_id, role, active, timestamps |
| Department | id, name, active, timestamps |
| Category | id, name, active, timestamps |
| Priority | id, name, sla_hours, color_code, timestamps |
| Ticket | id, ticket_number, title, description, requester_id, department_id, category_id, priority_id, assigned_to, status, sla_due_at, sla_paused_at, resolved_at, closed_at, timestamps |
| Comment | id, ticket_id, user_id, body, is_internal, timestamps |
| Attachment | id, ticket_id, filename, filepath, mime_type, size, timestamps |
| TicketActivity | id, ticket_id, user_id, action, old_value, new_value, timestamps |
| Notification | id, user_id, type, data (JSON), read_at, timestamps |

### 5.2 Ticket Status Values
`open` → `in_progress` → `on_hold` → `resolved` → `closed`  
A resolved ticket can transition back to `open` via Requester reply.

---

## 6. Out of Scope

- Customer-facing (public) portal
- Live chat or real-time messaging
- Asset/inventory management
- Integration with external ITSM tools (e.g., Jira, ServiceNow)
- Mobile native app
- Two-factor authentication (can be added in a future phase)
