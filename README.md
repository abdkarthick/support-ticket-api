<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

# Support Ticket API - Assignment, SLA, Queues, Dashboard

Production-ready Support Ticket System built on Laravel with role-based access, SLA management, queued notifications, events & activity logs.

> Branch `krish/assignment-sla` merged into `main` - 21 routes passing ✅

### 🔥 Features Implemented (krish)

**Core:**
- Auth with Laravel Sanctum (Register, Login, Logout, Me)
- Roles: `user`, `agent`, `admin` with Policies
- Ticket CRUD with assignment logic
- Ticket Replies

**Advanced:**
- **Assignment & SLA:** `assigned_to`, `sla_due_at`, `breached_at` fields
- **SLA Calculator Service** + `CheckSlaBreaches` Command (scheduled every minute)
- **Queues:** All notifications queued via `jobs` table
- **Events:** `TicketCreated`, `TicketAssigned`, `TicketReplied`, `TicketStatusChanged`
- **Listeners:** `LogTicketActivity`, `LogStatusChange`, `SendTicketAssignedNotification`, `SendTicketRepliedNotification`
- **Activity Log:** Full audit trail for tickets
- **Dashboard:** Stats for admin/agent
- **Notifications Table:** DB notifications

### 📁 New Files Added
- `app/Console/Commands/CheckSlaBreaches.php`
- `app/Events/` - 4 events
- `app/Listeners/` - 4 listeners
- `app/Notifications/` - 2 notifications
- `app/Services/SlaCalculatorService.php`, `TicketService.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Models/ActivityLog.php`
- `database/migrations/` - assignment, SLA, activity_logs, notifications

### 🚀 Setup

```bash
# Install
composer install
cp .env.example .env
php artisan key:generate

# DB Setup
php artisan migrate --seed

# Run
php artisan serve
php artisan queue:work
php artisan schedule:work