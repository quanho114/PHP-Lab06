# Design Specification: Training Center CRM (Potential Students & Course Enrollments)

This document details the architecture, security mechanism, database design, and implementation plan for the **Training Center CRM** system, satisfying all rules of PHP MVC Final Lab06.

---

## 1. System Overview & Business Domain
Instead of a generic CRM, this project implements a specialized **Training Center CRM** managing:
- **Course Leads (`course_leads`)**: Potential students registering for consulting services or courses via public or admin forms.
- **Enrollments (`enrollments`)**: Actual course registrations and payment receipts, featuring a unique, non-repeating enrollment code.

---

## 2. File & Directory Structure
The application strictly follows the mandated structure:

```
/var/www/html/
├── public/
│   ├── index.php                 # Front Controller / Entry Point
│   └── assets/
│       └── style.css             # Main styling stylesheet
├── config/
│   ├── app.php                   # General application settings (debug mode, logs)
│   └── database.php              # Database connection settings
├── app/
│   ├── Core/
│   │   ├── Database.php          # Database helper returning configured PDO instance
│   │   ├── Router.php            # HTTP method and path dispatcher with 404/405 error handlers
│   │   ├── helpers.php           # Core global functions (e, redirect, render, partial, flash, old)
│   │   └── DuplicateRecordException.php  # Dedicated exception for unique constraint failures
│   ├── Controllers/
│   │   ├── HomeController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── PublicLeadController.php
│   │   ├── CourseLeadController.php
│   │   ├── EnrollmentController.php
│   │   └── HealthController.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── CourseLeadService.php
│   │   ├── EnrollmentService.php
│   │   └── CSRFService.php
│   ├── Repositories/
│   │   ├── UserRepository.php
│   │   ├── CourseLeadRepository.php
│   │   └── EnrollmentRepository.php
│   └── Views/
│       ├── layouts/
│       │   └── main.php          # Layout wrapper containing HTML head, body, header, footer
│       ├── partials/
│       │   ├── nav.php           # Nav bar with dynamic options based on session role/auth
│       │   └── flash.php         # Central flash message renderer
│       ├── auth/
│       │   └── login.php         # Admin authentication form
│       ├── dashboard/
│       │   └── index.php         # Summary of stats, database metrics, and system options
│       ├── course-leads/
│       │   ├── index.php         # Paginated leads listing with search and sort
│       │   ├── create.php        # Lead creation form
│       │   └── edit.php          # Lead edit form
│       ├── enrollments/
│       │   ├── index.php         # Paginated enrollments listing with search and sort
│       │   ├── create.php        # Enrollment creation form
│       │   └── edit.php          # Enrollment edit form
│       └── errors/
│           ├── 404.php           # Page not found error
│           ├── 405.php           # Method not allowed error
│           └── 500.php           # Internal server error
├── database/
│   ├── schema.sql                # SQL Database layout
│   └── seed.sql                  # Database seed records
├── storage/
│   └── logs/
│       └── app.log               # Internal application logging target
├── scratch/
│   └── test_crm.php              # Automated integration tests suite
├── docker-compose.yml            # Local container configuration
├── Dockerfile                    # Container PHP/Apache setup
└── README.md                     # Full deployment documentation
```

---

## 3. Database Schema Design (`database/schema.sql`)

The database uses InnoDB tables with configured constraints, indexes, and default parameters:

```sql
CREATE DATABASE IF NOT EXISTS training_crm;
USE training_crm;

-- 1. Users table (Admin accounts)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) DEFAULT 'staff', -- admin, staff
    status VARCHAR(50) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Course Leads (Potential students)
CREATE TABLE IF NOT EXISTS course_leads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE, -- Prevents duplicate potential student entries
    phone VARCHAR(20) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'new', -- new, contacted, enrolled, lost
    interested_course VARCHAR(150) DEFAULT NULL,
    note TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_lead_status (status),
    INDEX idx_lead_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Enrollments (Course registrations & payments)
CREATE TABLE IF NOT EXISTS enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_code VARCHAR(50) NOT NULL UNIQUE, -- Unique registration identifier (e.g. ENR-2026-0001)
    student_name VARCHAR(100) NOT NULL,
    student_email VARCHAR(150) DEFAULT NULL,
    course_fee DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    payment_status VARCHAR(50) DEFAULT 'unpaid', -- unpaid, paid, refunded, cancelled
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_enrollment_status (payment_status),
    INDEX idx_enrollment_code (enrollment_code),
    INDEX idx_enrollment_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 4. Route Mapping Table

The Front Controller routing schema maps HTTP requests:

| Method | PATH | Action | Description / Security |
| :--- | :--- | :--- | :--- |
| **GET** | `/` | `HomeController@index` | Main overview page and register portal selection |
| **GET** | `/public-leads/create` | `PublicLeadController@create` | Public lead generation form |
| **POST** | `/public-leads` | `PublicLeadController@store` | Stores guest lead. Validates **Honeypot**, **CSRF**, and **Rate Limit** |
| **GET** | `/login` | `AuthController@login` | Renders Admin login view |
| **POST** | `/login` | `AuthController@handleLogin` | Performs authentication, regenerates session ID, redirects |
| **POST** | `/logout` | `AuthController@logout` | Cleans up session and redirect to `/login` |
| **GET** | `/dashboard` | `DashboardController@index` | Requires login. Renders general system stats and health summary |
| **GET** | `/course-leads` | `CourseLeadController@index` | Requires login. List potential students with search, sort, pagination |
| **GET** | `/course-leads/create` | `CourseLeadController@create` | Form to create a course lead manually |
| **POST** | `/course-leads/store` | `CourseLeadController@store` | Processes lead creation. Validates CSRF. Redirects |
| **GET** | `/course-leads/edit` | `CourseLeadController@edit` | Form to edit a course lead |
| **POST** | `/course-leads/update` | `CourseLeadController@update` | Processes lead updates. Validates CSRF. Redirects |
| **POST** | `/course-leads/delete` | `CourseLeadController@delete` | Deletes a lead. POST-only mapping |
| **GET** | `/enrollments` | `EnrollmentController@index` | Requires login. List enrollments with search, sort, pagination |
| **GET** | `/enrollments/create` | `EnrollmentController@create` | Form to register a new course enrollment |
| **POST** | `/enrollments/store` | `EnrollmentController@store` | Processes enrollment. Validates CSRF. Redirects |
| **GET** | `/enrollments/edit` | `EnrollmentController@edit` | Form to edit enrollment details |
| **POST** | `/enrollments/update` | `EnrollmentController@update` | Processes updates. Validates CSRF. Redirects |
| **POST** | `/enrollments/delete` | `EnrollmentController@delete` | Deletes an enrollment. POST-only mapping |
| **GET** | `/health` | `HealthController@index` | App and DB diagnostic health check (JSON response) |

---

## 5. Security Architecture

1. **Anti-CSRF Protection:**
   - Every POST request must contain a `csrf_token` POST field.
   - The token is generated via secure pseudo-random bytes and compared against session values. If validation fails, Router instantly throws a 403 Forbidden page.
2. **Session Cookie Security:**
   - Session setup uses `session_set_cookie_params()` before `session_start()`.
   - `HttpOnly => true` (prevents JavaScript access), `SameSite => Lax` (mitigates CSRF), and `Secure => true` (if active under HTTPS).
3. **Session Inactivity Timeout:**
   - Clears session data if user is inactive for more than 10 minutes (600 seconds).
4. **Session Fixation Prevention:**
   - `session_regenerate_id(true)` is executed immediately upon successful user login.
5. **Anti-Spam (Honeypot & Rate Limit):**
   - Public forms contain a hidden input field named `website`. If it contains any value, the request is silently dropped (redirected to `/` with no DB insert).
   - Session variable `last_submission_time` is used to enforce a 5-second minimum interval between guest registrations.
6. **SQL Injection Protection:**
   - All dynamic SQL statements are executed via PDO prepared statements with bounded parameters.
   - Data inputs (`limit` and `offset`) are cast to integers and bound via `PDO::PARAM_INT`.
7. **Whitelisted Sorting:**
   - Sort columns and directions are filtered against hardcoded lists in Repositories. Unsupported inputs fallback safely to default parameters.
8. **Production Exception Safe Rendering:**
   - Detailed database error parameters (e.g., SQLSTATE, table structure) are masked when `debug` configuration is set to `false`. Instead, errors are written into `storage/logs/app.log`, and the user is shown a general error screen.

---

## 6. Test Plan & Automated Suite (`scratch/test_crm.php`)

An automated integration script simulates browser requests using cURL. The suite executes:

- **TC01**: Admin login with wrong password -> verification message appears.
- **TC02**: Admin login with correct password -> redirects to `/dashboard` with session.
- **TC03**: Access `/dashboard` when logged out -> redirects to `/login`.
- **TC04**: Public lead validation check (empty fields) -> error messages.
- **TC05**: Public lead honeypot filled -> silently drops entry without DB insertion.
- **TC06**: Public lead rate limit -> blocks rapid submission and displays warning.
- **TC07**: CSRF validation missing on POST -> returns HTTP 403 Forbidden.
- **TC08**: Course lead duplicate email -> handled gracefully via friendly validation error.
- **TC09**: Course lead sorting whitelist validation -> invalid parameters fall back safely.
- **TC10**: Access unknown path -> returns HTTP 404.
- **TC11**: Wrong method on route (e.g., POST `/health`) -> returns HTTP 405.
- **TC12**: Enrollment validation check (negative fee) -> validation warning.
- **TC13**: Enrollment duplicate code validation -> unique constraint prevents database error leaks.
- **TC14**: Delete request via GET method -> blocks execution, returns 404 or 405.
- **TC15**: Admin logout -> destroys session and redirects to `/login`.
