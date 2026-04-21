# NU Clark Campus Event Management System
## Capstone Defense — Complete Technical Reference Notes

---

## 1. System Overview

**NU Clark Campus Event Management System** is a full-stack web application designed to digitize and streamline the entire lifecycle of campus events and venue reservations at National University – Clark Campus. It covers:

- Student **event discovery and registration**
- Organizer **event creation and approval submission**
- Multi-level **approval chain** for both events and venue reservations (digital file-hunting)
- **QR code-based attendance** tracking with selfie photo verification
- **Admin control** for users, announcements, and system configuration
- A companion **REST API** for future mobile app integration

---

## 2. Language & Framework

### Backend: **PHP 8.2 + Laravel 12**
- **Why PHP?** — Widely supported, free, and has the largest ecosystem for web backend development. PHP 8.2 adds union types, enums, and fibers for modern performance.
- **Why Laravel?** — Laravel is the most popular PHP framework; it provides:
  - **MVC Architecture** — clean separation between data (Models), logic (Controllers), and presentation (Views/Blade).
  - **Eloquent ORM** — maps database tables to PHP classes with relationships, scopes, and soft deletes. No raw SQL needed for most operations.
  - **Blade Templating** — server-side HTML rendering for the web portal. Blade compiles to pure PHP for speed.
  - **Artisan CLI** — command-line tools for migrations, seeding, caching, and task automation.
  - **Built-in security** — CSRF protection, password hashing, session management, and input validation are all built-in.

### Frontend: **Vanilla HTML + Blade + Bootstrap 5 + Bootstrap Icons**
- **Bootstrap 5** — loaded via CDN. Provides responsive grid, modals, tabs, badges, cards without writing custom CSS.
- **Bootstrap Icons** — icon set used throughout the UI for visual clarity.
- **FullCalendar.js** — calendar component used by organizers, student department, and approvers to visualize event/venue schedules.
- **Html5Qrcode** — JavaScript library used in the organizer dashboard to scan student QR codes via camera (rear-camera optimized for mobile).
- **Axios** — HTTP client for async API calls (notifications mark-read, calendar events fetch).
- **Vite** — modern asset bundler. Compiles and hot-reloads frontend assets during development.

### Database: **MySQL** (production on Railway)
- Relational database with 12 tables and a well-defined migration history.
- All table relationships are enforced at both the ORM level (Eloquent) and at the DB level via foreign keys.

---

## 3. System Architecture

```
Browser / Mobile App
       │
       ▼
   [ Laravel App (Railway PaaS) ]
       │
       ├── Web Routes (/web.php)       → Blade views  (Server-Side Rendering)
       │       └── Middleware: auth, role, throttle, CSRF
       │
       ├── API Routes (/api.php)       → JSON responses (for Mobile App)
       │       └── Middleware: auth:sanctum, role, throttle
       │
       ├── Controllers
       │       ├── Web/ — 7 controllers for the web portal
       │       └── Api/ — 9 controllers for the REST API
       │
       ├── Models (Eloquent ORM)
       │       └── 11 models → MySQL tables
       │
       └── Storage: AWS S3
               ├── Event posters
               ├── Attendance selfie photos
               └── E-signatures
```

---

## 4. User Roles & Access Control

The system uses a **single `role` column on the `users` table** — a simple, lightweight RBAC (Role-Based Access Control) design.

| Role | Access |
|---|---|
| `student` | View events, register, QR code, attendance selfie, notifications |
| `organizer` | Create/manage events, scan QR codes, view attendees, export reports |
| `student_development` | Dual role: can create events (organizer panel) + submit venue reservations |
| `adviser` | Approve/reject events (Step 1) |
| `department_head` | Approve/reject events (Step 2) |
| `dean` | Approve/reject events + venue reservations |
| `program_chair` | Approve/reject venue reservations |
| `executive_director` | Final approval for events and venues |
| `admin` | Full system access — user management, reports, file-hunting config |

**How enforcement works:**
1. **`RoleMiddleware`** — every protected route group declares allowed roles: `role:student,admin`. If the logged-in user's role is not in the list, they get a `403 Forbidden`.
2. **`is_active` check** — the same middleware logs out and redirects any user whose account is deactivated mid-session (even if they had a valid session cookie).
3. **Per-controller closure middleware** — controllers implementing `HasMiddleware` declare their own inline guards for fine-grained control.

---

## 5. System Flow — Step by Step

### 5A. Student Registration & Event Discovery
1. Student visits `/register` → email must end in `@student.nu-clark.edu.ph` (enforced by custom validation rule).
2. Student ID must match regex `YYYY-NNNNNN` (e.g. `2023-190866`).
3. After login, student lands on `/student/dashboard` — sees upcoming events, their registration count, attendance count.
4. Student browses `/student/events`, clicks **Register** → system checks capacity, creates a `Registration` record with a **SHA-256 QR token** (includes user_id, event_id, timestamp, random 16-char string).
5. Student views their QR code at `/student/qr/{id}` — code embeds the full URL: `/organizer/scan/{token}`.

### 5B. Event Creation & Approval Chain
1. Organizer creates an event at `/organizer/events/create`.
2. Event is saved with `status = 'pending_adviser'` → enters the approval pipeline.
3. Approval chain (web events):
   - **Adviser** approves → `pending_dept_head`
   - **Department Head** approves → `pending_dean`
   - **Dean** approves → `pending_director`
   - **Executive Director** approves → `published`
   - Any rejection at any step → `rejected` (with feedback comments)
4. When published → all students receive an `AppNotification` (bell icon in nav).

### 5C. Venue Reservation (File Hunting / Digital Paper Chase)
1. Student Development staff logs into their dedicated portal at `/student-department/dashboard`.
2. Submits a venue reservation form — selects venue, date, time, event link.
3. System checks for **schedule conflicts** with 1-hour buffer (ingress/egress).
4. Reservation enters a **dynamic signatory chain** configured by the admin in the "File Hunting" panel.
5. Each signatory (e.g. student_development → program_chair → executive_director) must:
   - **Open** the document first (records `opened_at` timestamp).
   - Then can **View the permission form**, **Approve** (with e-signature), or **Reject** (with reason).
6. Once all signatories approve → status = `approved` → permission form is printable as a formal document.
7. The tracker on the Student Dept. dashboard shows a **Shopee-style parcel tracker** with step-by-step icons.

### 5D. Attendance — QR Code Check-in
1. Organizer opens scanner at `/organizer/dashboard` → camera activates using `Html5Qrcode` (rear cam on mobile).
2. Student shows their QR code.
3. Scanner reads the URL, sends a POST to `/organizer/scan/{token}`.
4. System validates token, checks if already checked-in, marks attendance as `pending`.
5. System prompts **student to take a selfie** (additional identity verification via camera API).
6. Selfie uploads to **S3**; organizer manually verifies and marks attendance as `verified`.

---

## 6. Database Schema (Key Tables)

| Table | Purpose |
|---|---|
| `users` | All accounts; role, student_id, e_signature_path |
| `courses` / `sections` | Academic structure for student accounts |
| `events` | Event records with status, organizer, poster |
| `registrations` | Student ↔ Event link, QR token, expiry |
| `attendances` | Check-in record, selfie photo path, status |
| `app_notifications` | In-app notification bell messages |
| `venue_reservations` | Venue booking requests with status |
| `venue_reservation_approvals` | One record per signatory per reservation |
| `event_approvals` | One record per approver per event |
| `file_hunting_signatories` | Admin-configurable ordered chain (step_order) |
| `cache` / `jobs` | Laravel internal tables |

**Key design decisions:**
- `Event` uses **SoftDeletes** — deleted events are soft-deleted (recoverable), never permanently wiped.
- `Registration.qr_token` is a **SHA-256 hash** — cryptographically secure, collision-resistant.
- `VenueReservation.status` is a **string state machine** (e.g. `pending_student_development`, `pending_program_chair`, `approved`, `rejected`, `cancelled`) — readable and flexible.
- `file_hunting_signatories` is **admin-configurable at runtime** — the approval chain can be reordered or changed without touching code.

---

## 7. API Layer (Mobile App Ready)

The system exposes a full **RESTful JSON API** at `/api/*` protected by **Laravel Sanctum**.

### Authentication
- Mobile users POST to `/api/login` → receive a **Bearer token** (Sanctum personal access token).
- All subsequent requests include `Authorization: Bearer {token}` header.
- Tokens are stored in the `personal_access_tokens` table and can be revoked.

### Key API Endpoints
| Method | Endpoint | Access |
|---|---|---|
| POST | `/api/login` | Public |
| POST | `/api/register` | Public |
| GET | `/api/events` | Public |
| GET | `/api/events/upcoming` | Public |
| POST | `/api/events/{id}/register` | Student |
| GET | `/api/my-registrations` | Student |
| GET | `/api/qrcode/{registration_id}` | Student |
| POST | `/api/attendance/checkin` | Student |
| GET | `/api/notifications` | Authenticated |
| POST | `/api/events` | Organizer |
| PUT | `/api/attendance/{id}/verify` | Organizer |
| GET | `/api/users` | Admin only |

### API Security
- **`auth:sanctum`** — validates the Bearer token on every protected request.
- **`role` middleware** — applies the same role check as the web layer to API routes.
- **`throttle:10,1`** on auth routes — max 10 login/register attempts per minute per IP (brute-force protection).
- **`throttle:api`** on general routes — Laravel's default API rate limiter.

---

## 8. Security Implementation

### 8A. Authentication Security
| Mechanism | How |
|---|---|
| Password hashing | Laravel `bcrypt` (automatic via `'password' => 'hashed'` cast) |
| CSRF protection | Every web form includes `@csrf` token; Laravel validates it on every POST/PUT/DELETE |
| Session regeneration | Token regenerated on login, logout, and account deactivation |
| Throttle on login | `throttle:login` — prevents brute-force attacks |
| Email domain restriction | Students must use `@student.nu-clark.edu.ph` only |

### 8B. Authorization Security
| Mechanism | How |
|---|---|
| Role-Based Access Control | `RoleMiddleware` checks `user->role` against allowed roles on every route |
| Deactivated account guard | `is_active` check in middleware — logs out banned users instantly |
| Object-level authorization | Controllers verify ownership (e.g. `VenueReservation::where('reserved_by', Auth::id())`) |
| Approver queue validation | Approvers can only act on reservations that are `pending_<their_role>` |

### 8C. HTTP Security Headers (`SecurityHeaders` Middleware)
| Header | Purpose |
|---|---|
| `Strict-Transport-Security` | Forces HTTPS for 1 year (HSTS) |
| `Content-Security-Policy` | Whitelists JS/CSS sources; blocks inline scripts from untrusted origins |
| `X-Frame-Options: DENY` | Prevents clickjacking (embedding in iframes) |
| `X-Content-Type-Options: nosniff` | Prevents MIME-type sniffing attacks |
| `Referrer-Policy` | Limits referrer info sent to external sites |
| `Permissions-Policy` | Camera allowed only from self; microphone and geolocation blocked |
| Remove `X-Powered-By` / `Server` | Hides PHP/server version from attackers |

### 8D. Input Validation
- **All form inputs** are validated via Laravel's `$request->validate()` before any database write.
- Validation rules include: `required`, `string`, `date`, `after_or_equal:today`, `min`, `max`, `mimes`, `unique`, `in`, `regex`.
- Custom error messages are provided for user-friendly feedback.
- **SQL injection** is impossible — Eloquent uses **PDO prepared statements** exclusively.
- **XSS prevention** — Blade's `{{ }}` syntax auto-escapes HTML entities. Raw output (`{!! !!}`) is used only where explicitly safe.

### 8E. File Upload Security
- Profile photos, event posters, attendance selfies, and e-signatures are uploaded to **AWS S3** (not stored locally).
- File type is validated server-side: `mimes:jpg,jpeg,png,webp`, `max:2048` (KB).
- S3 paths are never exposed in URLs directly — a **proxy route** (`/storage/s3/{path}`) streams files securely.

---

## 9. Third-Party Libraries & Services (Full List)

### PHP Packages (via Composer)

| Package | Version | Purpose |
|---|---|---|
| **laravel/framework** | ^12.0 | Core MVC framework |
| **laravel/sanctum** | ^4.3 | API token authentication for the mobile layer |
| **laravel/tinker** | ^2.10 | REPL for debugging in production |
| **barryvdh/laravel-dompdf** | ^3.1 | Generates PDF reports (attendance sheets, event reports) |
| **maatwebsite/excel** | ^3.1 | Exports attendance data to `.xlsx` Excel files |
| **simplesoftwareio/simple-qrcode** | ^4.2 | Generates QR code images for student registrations |
| **league/flysystem-aws-s3-v3** | * | S3 filesystem adapter (connects Laravel Storage to AWS S3) |
| **spatie/laravel-permission** | ^6.24 | Extended role/permission package (installed, available for future use) |
| **ext-gd** | * | PHP image processing extension (used by QR code generation) |
| **ext-intl** | * | PHP internationalization extension |
| **ext-zip** | * | PHP ZIP extension (required by Excel export) |

### Dev-Only PHP Packages
| Package | Purpose |
|---|---|
| **fakerphp/faker** | Generates fake data for database seeders |
| **phpunit/phpunit** | Unit and feature testing framework |
| **laravel/pail** | Real-time log tail in terminal |
| **laravel/pint** | PHP code style fixer |
| **laravel/sail** | Docker dev environment |

### JavaScript / Frontend (CDN + NPM)

| Library | Source | Purpose |
|---|---|---|
| **Bootstrap 5.3** | CDN (jsdelivr) | Responsive UI components (modals, tabs, badges, cards, grid) |
| **Bootstrap Icons 1.11** | CDN (jsdelivr) | Icon library used throughout the interface |
| **FullCalendar** | CDN | Interactive calendar for event/venue scheduling |
| **Html5Qrcode** | CDN | Camera-based QR code scanning in the organizer dashboard |
| **Axios** | NPM (^1.11) | HTTP client for async fetch calls |
| **Vite** | NPM (^7.0) | Frontend build tool & dev server |
| **laravel-vite-plugin** | NPM | Integrates Vite with Laravel's asset pipeline |
| **Tailwind CSS v4** | NPM (^4.0) | Utility CSS framework (installed, partially used) |
| **concurrently** | NPM (^9.0) | Runs multiple dev processes simultaneously |

### Cloud Services

| Service | Purpose |
|---|---|
| **AWS S3** | Object storage for all uploaded files (posters, selfies, signatures) |
| **Railway.app** | PaaS hosting for the Laravel application and MySQL database |
| **GitHub** | Source control and CI/CD trigger (push to main → Railway auto-deploys) |

---

## 10. Deployment & DevOps

- **Platform:** Railway.app (PaaS)
- **Deployment config:** `nixpacks.toml` — Railway reads this to know how to build and start the app.
- **Build steps:**
  1. `composer install --no-dev --optimize-autoloader` — installs PHP dependencies, optimized for production.
  2. `npm install && npm run build` — compiles frontend assets with Vite.
- **Start command:**
  ```
  php artisan storage:link --force
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan migrate --force
  php artisan db:seed --force
  php artisan serve --host=0.0.0.0 --port=$PORT
  ```
  - `config:cache`, `route:cache`, `view:cache` — pre-compiles everything for faster response times.
  - `migrate --force` — runs any new migrations automatically on each deploy.
  - `$PORT` — Railway injects the dynamic port; the app binds to it.
- **Environment variables** stored in Railway's dashboard (not committed to Git): `APP_KEY`, `DB_*`, `AWS_*` credentials.

---

## 11. Key Design Patterns Used

| Pattern | Where Used |
|---|---|
| **MVC** | All controllers (web + API) follow Model-View-Controller |
| **Repository-light (Eloquent Scopes)** | `scopeUpcoming()`, `scopePublished()`, `scopeSearch()` on the Event model |
| **State Machine** | `Event.status` and `VenueReservation.status` progress through defined states |
| **Strategy (Dynamic Chain)** | `FileHuntingSignatory` table drives the approval order — changeable at runtime |
| **Observer-ready** | `AppNotification` is inserted in bulk after events are published (fan-out notification) |
| **Proxy Route** | `/storage/s3/{path}` — server-side proxy for private S3 file access |
| **Soft Delete** | Events use `SoftDeletes` trait — recoverable without data loss |
| **Token-based QR** | SHA-256 token ensures QR codes are unforgeable and unique per registration |

---

## 12. Quick Answers for Common Defense Questions

**Q: Why Laravel and not Node.js/Django?**
> Laravel is PHP-native and has the most mature ecosystem for rapid web development. It provides ORM, authentication, API scaffolding, testing, PDF/Excel export, and S3 integration all in one framework. The team had prior PHP experience.

**Q: How is the system secure?**
> Multiple layers: password bcrypt hashing, CSRF tokens on all forms, role-based access control enforced at every route, HTTP security headers (HSTS, CSP, X-Frame-Options), rate limiting on auth endpoints, server-side input validation, PDO prepared statements (no raw SQL), and S3 proxying for file privacy.

**Q: How does the QR attendance work?**
> A cryptographically secure SHA-256 token is generated per registration. The QR code encodes the scan URL. When scanned, Laravel validates the token, prevents double check-in, and prompts for a selfie. The organizer then verifies the selfie and marks the attendance `verified`.

**Q: What is "File Hunting"?**
> It's the digitized version of the physical paper-chasing process at NU Clark, where documents physically travel between offices for signatures. In the system, each signatory sees the request in their dashboard, opens the digital permission form, e-signs by approving with their uploaded signature, and passes it to the next step.

**Q: Why AWS S3?**
> Railway's filesystem is ephemeral — files saved locally during one deploy are lost on the next. S3 is persistent, scalable, and accessible across deploys. All user-uploaded files (posters, selfies, signatures) are stored there.

**Q: What happens if a signatory rejects a venue request?**
> The entire chain stops. Status becomes `rejected`. The rejection reason (comments) is stored in `venue_reservation_approvals`. The student department department sees the rejection feedback on their dashboard and can resubmit a new request.

**Q: Is there a mobile app?**
> The REST API layer (`/api/*`) is fully built and secured with Laravel Sanctum tokens, ready for a mobile app. The web system is also mobile-responsive via Bootstrap 5. A `mobile_app_blueprint.md` exists in the project root documenting the planned mobile features.

**Q: How does the system prevent double-booking of venues?**
> `VenueReservation::hasConflict()` checks the DB for any existing reservation at the same venue and date where time ranges overlap (including a 1-hour ingress/egress buffer). If a conflict is found, the form submission is rejected with an error message.

**Q: How is role switching prevented?**
> The `role` field is never exposed to the user for editing. Only admins can change roles via the `/admin/users` panel (PUT `/admin/users/{id}`). The role is read server-side from the authenticated session/token — never from user input.
