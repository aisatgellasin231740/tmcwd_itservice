<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Department;
use App\Models\Priority;
use App\Models\Ticket;
use App\Models\TicketActivity;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $admin   = User::where('email', 'admin@tmcwd.gov.ph')->first();
        $juan    = User::where('email', 'juan@tmcwd.gov.ph')->first();
        $maria   = User::where('email', 'maria@tmcwd.gov.ph')->first();
        $ana     = User::where('email', 'ana@tmcwd.gov.ph')->first();
        $pedro   = User::where('email', 'pedro@tmcwd.gov.ph')->first();
        $rosa    = User::where('email', 'rosa@tmcwd.gov.ph')->first();
        $carlo   = User::where('email', 'carlo@tmcwd.gov.ph')->first();
        $liza    = User::where('email', 'liza@tmcwd.gov.ph')->first();
        $ben     = User::where('email', 'ben@tmcwd.gov.ph')->first();
        $joy     = User::where('email', 'joy@tmcwd.gov.ph')->first();

        $catId  = fn(string $n) => Category::where('name', $n)->value('id');
        $priId  = fn(string $n) => Priority::where('name', $n)->value('id');
        $deptId = fn(string $n) => Department::where('name', $n)->value('id');

        $year = now()->year;
        $seq  = 1;

        $tickets = [
            // 1 — SLA BREACHED (Urgent, created 3 days ago, unresolved)
            [
                'title'       => 'Billing system completely down — cannot process payments',
                'description' => 'The main billing system (BIMS) is inaccessible. Error: "Cannot connect to database server." All billing clerks are affected. Payments cannot be processed.',
                'requester'   => $ana,
                'dept'        => 'Finance/Billing',
                'category'    => 'Billing System Issue',
                'priority'    => 'Urgent',
                'status'      => 'in_progress',
                'assigned'    => $juan,
                'created_at'  => now()->subDays(3),
                'sla_offset'  => 2,   // SLA was 2 hours — now breached
            ],
            // 2 — SLA BREACHED (High, created 2 days ago)
            [
                'title'       => 'Network switch in Engineering building is faulty',
                'description' => 'The network switch in Engineering office Room 201 is intermittently dropping connections, affecting 12 workstations.',
                'requester'   => $pedro,
                'dept'        => 'Engineering',
                'category'    => 'Network/Internet Issue',
                'priority'    => 'High',
                'status'      => 'open',
                'assigned'    => null,
                'created_at'  => now()->subDays(2),
                'sla_offset'  => 8,
            ],
            // 3 — SLA BREACHED (Medium, 5 days old, unresolved)
            [
                'title'       => 'Printer in Customer Service not printing in color',
                'description' => 'The HP LaserJet in the Commercial area only prints in grayscale. Color printing is needed for customer billing statements.',
                'requester'   => $rosa,
                'dept'        => 'Commercial/Customer Service',
                'category'    => 'Printer/Scanner Issue',
                'priority'    => 'Medium',
                'status'      => 'on_hold',
                'assigned'    => $maria,
                'created_at'  => now()->subDays(5),
                'sla_offset'  => 48,
            ],
            // 4 — Resolved (within SLA)
            [
                'title'       => 'Cannot access email — password reset needed',
                'description' => 'My Outlook account is locked out. I have tried the self-service reset but the recovery email is outdated.',
                'requester'   => $carlo,
                'dept'        => 'Administration',
                'category'    => 'Email/Account Access',
                'priority'    => 'High',
                'status'      => 'resolved',
                'assigned'    => $juan,
                'created_at'  => now()->subDays(4),
                'sla_offset'  => 8,
                'resolved_at' => now()->subDays(4)->addHours(6),
            ],
            // 5 — Closed
            [
                'title'       => 'Request new laptop for new Engineering employee',
                'description' => 'New hire Mark Dela Fuente (Engineer II) will start on Sept 15. Please prepare a laptop with standard software.',
                'requester'   => $pedro,
                'dept'        => 'Engineering',
                'category'    => 'New Equipment Request',
                'priority'    => 'Low',
                'status'      => 'closed',
                'assigned'    => $maria,
                'created_at'  => now()->subDays(10),
                'sla_offset'  => 120,
                'resolved_at' => now()->subDays(6),
                'closed_at'   => now()->subDays(5),
            ],
            // 6 — Open, unassigned (Urgent)
            [
                'title'       => 'SCADA monitoring system not responding at Plant 2',
                'description' => 'The SCADA/plant monitoring dashboard at Production Plant 2 is showing no data since 8:00 AM. Operators cannot monitor water pressure levels.',
                'requester'   => $liza,
                'dept'        => 'Production/Plant Operations',
                'category'    => 'Software Issue',
                'priority'    => 'Urgent',
                'status'      => 'open',
                'assigned'    => null,
                'created_at'  => now()->subHours(1),
                'sla_offset'  => 2,
            ],
            // 7 — In Progress
            [
                'title'       => 'GIS mapping software crashes on startup',
                'description' => 'ArcGIS crashes immediately after the splash screen on my workstation. Tried reinstalling but the problem persists.',
                'requester'   => $pedro,
                'dept'        => 'Engineering',
                'category'    => 'GIS/Mapping System Issue',
                'priority'    => 'High',
                'status'      => 'in_progress',
                'assigned'    => $maria,
                'created_at'  => now()->subDays(1),
                'sla_offset'  => 8,
            ],
            // 8 — Open (Low priority)
            [
                'title'       => 'Create user account for new HR staff',
                'description' => 'New HR staff Melissa Cruz will start Monday. She needs a Windows login, email account (mcruz@tmcwd.gov.ph), and access to the HRIS portal.',
                'requester'   => $joy,
                'dept'        => 'Human Resources',
                'category'    => 'Account Creation/Access Request',
                'priority'    => 'Medium',
                'status'      => 'open',
                'assigned'    => $juan,
                'created_at'  => now()->subHours(3),
                'sla_offset'  => 48,
            ],
            // 9 — Resolved
            [
                'title'       => 'Meter reading tablets not syncing with server',
                'description' => 'All 5 Android tablets used for meter reading are failing to sync data to the central server. Last sync was 2 days ago.',
                'requester'   => $ben,
                'dept'        => 'Meter Reading',
                'category'    => 'Network/Internet Issue',
                'priority'    => 'High',
                'status'      => 'resolved',
                'assigned'    => $juan,
                'created_at'  => now()->subDays(3),
                'sla_offset'  => 8,
                'resolved_at' => now()->subDays(3)->addHours(7),
            ],
            // 10 — Open (unassigned)
            [
                'title'       => 'Mouse and keyboard not working on Finance PC #4',
                'description' => 'USB mouse and keyboard are unresponsive on the PC assigned to the Accounts Payable section. Tried different ports and devices.',
                'requester'   => $ana,
                'dept'        => 'Finance/Billing',
                'category'    => 'Hardware Issue',
                'priority'    => 'Medium',
                'status'      => 'open',
                'assigned'    => null,
                'created_at'  => now()->subHours(5),
                'sla_offset'  => 48,
            ],
            // 11 — In Progress
            [
                'title'       => 'Antivirus software expired on all Admin workstations',
                'description' => 'Norton antivirus license expired on 12 Administration workstations. Need renewal or replacement AV deployment.',
                'requester'   => $carlo,
                'dept'        => 'Administration',
                'category'    => 'Software Issue',
                'priority'    => 'High',
                'status'      => 'in_progress',
                'assigned'    => $maria,
                'created_at'  => now()->subDays(1)->subHours(2),
                'sla_offset'  => 8,
            ],
            // 12 — On Hold (waiting for parts)
            [
                'title'       => 'Scanner at Billing not recognized by PC',
                'description' => 'Canon scanner CanoScan is not detected. Device Manager shows "Unknown Device". Possibly a driver or hardware issue.',
                'requester'   => $ana,
                'dept'        => 'Finance/Billing',
                'category'    => 'Printer/Scanner Issue',
                'priority'    => 'Low',
                'status'      => 'on_hold',
                'assigned'    => $juan,
                'created_at'  => now()->subDays(6),
                'sla_offset'  => 120,
            ],
            // 13 — Open
            [
                'title'       => 'Slow internet connection in Customer Service area',
                'description' => 'Internet speed in the Commercial/Customer Service wing has been very slow for the past week. Speed tests show only 2 Mbps download.',
                'requester'   => $rosa,
                'dept'        => 'Commercial/Customer Service',
                'category'    => 'Network/Internet Issue',
                'priority'    => 'Medium',
                'status'      => 'open',
                'assigned'    => null,
                'created_at'  => now()->subDays(2)->subHours(4),
                'sla_offset'  => 48,
            ],
            // 14 — Resolved
            [
                'title'       => 'Request for additional monitor for HR workstation',
                'description' => 'HR staff Joy Flores requests a second monitor for improved productivity when managing HRIS data and spreadsheets simultaneously.',
                'requester'   => $joy,
                'dept'        => 'Human Resources',
                'category'    => 'New Equipment Request',
                'priority'    => 'Low',
                'status'      => 'resolved',
                'assigned'    => $maria,
                'created_at'  => now()->subDays(7),
                'sla_offset'  => 120,
                'resolved_at' => now()->subDays(4),
            ],
            // 15 — Open (just submitted)
            [
                'title'       => 'Forgot Windows login password — locked out',
                'description' => 'I have been locked out of my workstation PC due to too many incorrect password attempts. Need password reset.',
                'requester'   => $ben,
                'dept'        => 'Meter Reading',
                'category'    => 'Email/Account Access',
                'priority'    => 'High',
                'status'      => 'open',
                'assigned'    => null,
                'created_at'  => now()->subMinutes(30),
                'sla_offset'  => 8,
            ],
        ];

        foreach ($tickets as $data) {
            $createdAt  = $data['created_at'];
            $slaHours   = $data['sla_offset'];
            $slaDueAt   = (clone $createdAt)->addHours($slaHours);
            $resolvedAt = $data['resolved_at'] ?? null;
            $closedAt   = $data['closed_at']   ?? null;

            $ticketNumber = 'TMCWD-' . $year . '-' . str_pad($seq++, 5, '0', STR_PAD_LEFT);

            $ticket = Ticket::create([
                'ticket_number'      => $ticketNumber,
                'title'              => $data['title'],
                'description'        => $data['description'],
                'requester_id'       => $data['requester']->id,
                'department_id'      => Department::where('name', $data['dept'])->value('id'),
                'category_id'        => $catId($data['category']),
                'priority_id'        => $priId($data['priority']),
                'assigned_to'        => $data['assigned']?->id,
                'status'             => $data['status'],
                'sla_due_at'         => $slaDueAt,
                'sla_paused_seconds' => 0,
                'resolved_at'        => $resolvedAt,
                'closed_at'          => $closedAt,
                'created_at'         => $createdAt,
                'updated_at'         => $createdAt,
            ]);

            // Activity: ticket created
            TicketActivity::create([
                'ticket_id'   => $ticket->id,
                'user_id'     => $data['requester']->id,
                'action'      => 'created',
                'description' => 'Ticket submitted',
                'created_at'  => $createdAt,
                'updated_at'  => $createdAt,
            ]);

            // Activity: assigned
            if ($data['assigned']) {
                TicketActivity::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $admin->id,
                    'action'      => 'assigned',
                    'new_value'   => $data['assigned']->name,
                    'created_at'  => (clone $createdAt)->addMinutes(10),
                    'updated_at'  => (clone $createdAt)->addMinutes(10),
                ]);
            }

            // Activity: status changes for non-open tickets
            if (!in_array($data['status'], ['open'])) {
                TicketActivity::create([
                    'ticket_id'  => $ticket->id,
                    'user_id'    => $data['assigned']?->id ?? $admin->id,
                    'action'     => 'status_changed',
                    'old_value'  => 'open',
                    'new_value'  => $data['status'],
                    'created_at' => (clone $createdAt)->addHours(1),
                    'updated_at' => (clone $createdAt)->addHours(1),
                ]);
            }

            // Sample comments
            if (in_array($data['status'], ['in_progress', 'on_hold', 'resolved', 'closed'])) {
                Comment::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $data['assigned']?->id ?? $admin->id,
                    'body'        => 'I have picked up this ticket and am currently investigating the issue.',
                    'is_internal' => false,
                    'created_at'  => (clone $createdAt)->addHours(1),
                    'updated_at'  => (clone $createdAt)->addHours(1),
                ]);

                Comment::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $data['assigned']?->id ?? $admin->id,
                    'body'        => 'Internal note: Checked logs. Escalating if not resolved within 1 hour.',
                    'is_internal' => true,
                    'created_at'  => (clone $createdAt)->addHours(2),
                    'updated_at'  => (clone $createdAt)->addHours(2),
                ]);

                Comment::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $data['requester']->id,
                    'body'        => 'Thank you for the update. Please let me know if you need anything else from my end.',
                    'is_internal' => false,
                    'created_at'  => (clone $createdAt)->addHours(3),
                    'updated_at'  => (clone $createdAt)->addHours(3),
                ]);
            }

            if (in_array($data['status'], ['resolved', 'closed'])) {
                Comment::create([
                    'ticket_id'   => $ticket->id,
                    'user_id'     => $data['assigned']?->id ?? $admin->id,
                    'body'        => 'Issue has been resolved. Please let us know if the problem recurs.',
                    'is_internal' => false,
                    'created_at'  => $resolvedAt ?? (clone $createdAt)->addHours(6),
                    'updated_at'  => $resolvedAt ?? (clone $createdAt)->addHours(6),
                ]);

                TicketActivity::create([
                    'ticket_id'  => $ticket->id,
                    'user_id'    => $data['assigned']?->id ?? $admin->id,
                    'action'     => 'status_changed',
                    'old_value'  => 'in_progress',
                    'new_value'  => 'resolved',
                    'created_at' => $resolvedAt ?? (clone $createdAt)->addHours(6),
                    'updated_at' => $resolvedAt ?? (clone $createdAt)->addHours(6),
                ]);
            }
        }
    }
}
