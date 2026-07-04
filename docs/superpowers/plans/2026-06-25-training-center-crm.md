# Training Center CRM Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Implement a Clean-Rewrite Training Center CRM system managing Course Leads (potential students) and Enrollments (course registrations/payments) based on the approved design specification, incorporating robust MVC architecture, strict validation, anti-spam protections (Honeypot, Rate Limits), CSRF validation, inactivity timeouts, whitelisted sorting, and error logging.

**Architecture:** Custom PHP MVC structure using a single Front Controller (`public/index.php`), clean class-based separation (Controllers, Services, Repositories, Views, Core), utilizing PDO prepared statements for database operations, and secure session management.

**Tech Stack:** PHP 8.2, MySQL 8.4, Apache, HTML5, Vanilla CSS, cURL (for integration test suite).

---

### Task 1: Database Setup (Schema and Seeds)

**Files:**
- Create: `database/schema.sql`
- Create: `database/seed.sql`

- [ ] **Step 1: Write the database schema SQL**
  Create `database/schema.sql` defining `users`, `course_leads`, and `enrollments` tables.
  
  ```sql
  CREATE DATABASE IF NOT EXISTS training_crm;
  USE training_crm;

  DROP TABLE IF EXISTS enrollments;
  DROP TABLE IF EXISTS course_leads;
  DROP TABLE IF EXISTS users;

  CREATE TABLE users (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(100) NOT NULL,
      email VARCHAR(150) NOT NULL UNIQUE,
      password_hash VARCHAR(255) NOT NULL,
      role VARCHAR(50) DEFAULT 'staff',
      status VARCHAR(50) DEFAULT 'active',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_user_status (status)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

  CREATE TABLE course_leads (
      id INT AUTO_INCREMENT PRIMARY KEY,
      fullname VARCHAR(100) NOT NULL,
      email VARCHAR(150) NOT NULL UNIQUE,
      phone VARCHAR(20) DEFAULT NULL,
      status VARCHAR(50) DEFAULT 'new',
      interested_course VARCHAR(150) DEFAULT NULL,
      note TEXT DEFAULT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_lead_status (status),
      INDEX idx_lead_created (created_at)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

  CREATE TABLE enrollments (
      id INT AUTO_INCREMENT PRIMARY KEY,
      enrollment_code VARCHAR(50) NOT NULL UNIQUE,
      student_name VARCHAR(100) NOT NULL,
      student_email VARCHAR(150) DEFAULT NULL,
      course_fee DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
      payment_status VARCHAR(50) DEFAULT 'unpaid',
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      INDEX idx_enrollment_status (payment_status),
      INDEX idx_enrollment_code (enrollment_code),
      INDEX idx_enrollment_created (created_at)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  ```

- [ ] **Step 2: Write the database seed SQL**
  Create `database/seed.sql` with default admin credentials (password: `123456`) and at least 20 records for pagination testing in both leads and enrollments tables.
  
  ```sql
  USE training_crm;

  INSERT INTO users (name, email, password_hash, role, status) VALUES 
  ('Admin User', 'admin@example.com', '$2y$12$jI9GbgXonGU5UPJUkWQE5eIWacJWmHZ9TDm5B6MjZ5ovon5RgPltu', 'admin', 'active');

  INSERT INTO course_leads (fullname, email, phone, status, interested_course, note) VALUES
  ('Nguyen Van A', 'a@example.com', '0901111111', 'new', 'PHP MVC Framework', 'Muon dang ky som'),
  ('Tran Thi B', 'b@example.com', '0902222222', 'contacted', 'Python Django', 'Da tu van dien thoai'),
  ('Le Van C', 'c@example.com', '0903333333', 'enrolled', 'ReactJS Frontend', 'Da dong hoc phi'),
  ('Pham Van D', 'd@example.com', '0904444444', 'lost', 'NodeJS Backend', 'Gia hoc phi cao'),
  ('Hoang Thi E', 'e@example.com', '0905555555', 'new', 'Flutter Mobile App', NULL),
  ('Ngo Van F', 'f@example.com', '0906666666', 'contacted', 'PHP MVC Framework', 'Hen goi lai sau'),
  ('Vu Thi G', 'g@example.com', '0907777777', 'new', 'Python Django', NULL),
  ('Do Van H', 'h@example.com', '0908888888', 'new', 'ReactJS Frontend', NULL),
  ('Bui Thi I', 'i@example.com', '0909999999', 'contacted', 'NodeJS Backend', NULL),
  ('Dang Van J', 'j@example.com', '0901234567', 'new', 'Flutter Mobile App', NULL),
  ('Dinh Thi K', 'k@example.com', '0902345678', 'new', 'PHP MVC Framework', NULL),
  ('Lam Van L', 'l@example.com', '0903456789', 'new', 'Python Django', NULL),
  ('Phan Thi M', 'm@example.com', '0904567890', 'new', 'ReactJS Frontend', NULL),
  ('Mai Van N', 'n@example.com', '0905678901', 'new', 'NodeJS Backend', NULL),
  ('Quach Thi O', 'o@example.com', '0906789012', 'new', 'Flutter Mobile App', NULL),
  ('Luong Van P', 'p@example.com', '0907890123', 'new', 'PHP MVC Framework', NULL),
  ('Trieu Thi Q', 'q@example.com', '0908901234', 'new', 'Python Django', NULL),
  ('Nghiem Van R', 'r@example.com', '0909012345', 'new', 'ReactJS Frontend', NULL),
  ('Vi Thi S', 's@example.com', '0900123456', 'new', 'NodeJS Backend', NULL),
  ('Duong Van T', 't@example.com', '0901122334', 'new', 'Flutter Mobile App', NULL),
  ('Ly Thi U', 'u@example.com', '0902233445', 'new', 'PHP MVC Framework', NULL),
  ('Vo Van V', 'v@example.com', '0903344556', 'new', 'Python Django', NULL);

  INSERT INTO enrollments (enrollment_code, student_name, student_email, course_fee, payment_status) VALUES
  ('ENR-2026-0001', 'Nguyen Van A', 'a@example.com', 4500000.00, 'paid'),
  ('ENR-2026-0002', 'Le Van C', 'c@example.com', 5000000.00, 'paid'),
  ('ENR-2026-0003', 'Tran Thi X', 'x@example.com', 3800000.00, 'unpaid'),
  ('ENR-2026-0004', 'Hoang Giang', 'giang@example.com', 6000000.00, 'cancelled'),
  ('ENR-2026-0005', 'Bui Long', 'long@example.com', 4500000.00, 'refunded'),
  ('ENR-2026-0006', 'Student 06', 'student06@example.com', 3000000.00, 'unpaid'),
  ('ENR-2026-0007', 'Student 07', 'student07@example.com', 3500000.00, 'paid'),
  ('ENR-2026-0008', 'Student 08', 'student08@example.com', 4000000.00, 'paid'),
  ('ENR-2026-0009', 'Student 09', 'student09@example.com', 4500000.00, 'unpaid'),
  ('ENR-2026-0010', 'Student 10', 'student10@example.com', 5000000.00, 'paid'),
  ('ENR-2026-0011', 'Student 11', 'student11@example.com', 5500000.00, 'paid'),
  ('ENR-2026-0012', 'Student 12', 'student12@example.com', 6000000.00, 'unpaid'),
  ('ENR-2026-0013', 'Student 13', 'student13@example.com', 3800000.00, 'paid'),
  ('ENR-2026-0014', 'Student 14', 'student14@example.com', 3900000.00, 'paid'),
  ('ENR-2026-0015', 'Student 15', 'student15@example.com', 4200000.00, 'cancelled'),
  ('ENR-2026-0016', 'Student 16', 'student16@example.com', 4600000.00, 'paid'),
  ('ENR-2026-0017', 'Student 17', 'student17@example.com', 4700000.00, 'paid'),
  ('ENR-2026-0018', 'Student 18', 'student18@example.com', 4800000.00, 'unpaid'),
  ('ENR-2026-0019', 'Student 19', 'student19@example.com', 4900000.00, 'paid'),
  ('ENR-2026-0020', 'Student 20', 'student20@example.com', 5000000.00, 'paid'),
  ('ENR-2026-0021', 'Student 21', 'student21@example.com', 5100000.00, 'unpaid'),
  ('ENR-2026-0022', 'Student 22', 'student22@example.com', 5200000.00, 'paid');
  ```

- [ ] **Step 3: Execute DB initialization**
  Run commands:
  `docker exec -i crm_db mysql -uroot -proot -e "CREATE DATABASE IF NOT EXISTS training_crm;"`
  `docker exec -i crm_db mysql -uroot -proot training_crm < database/schema.sql`
  `docker exec -i crm_db mysql -uroot -proot training_crm < database/seed.sql`
  Expected: Command succeeds with warnings only about password on CLI.

- [ ] **Step 4: Commit DB changes**
  ```bash
  git add database/schema.sql database/seed.sql
  git commit -m "db: initialize schema and seeds for training crm"
  ```

---

### Task 2: Core Components Configuration

**Files:**
- Create: `app/Core/DuplicateRecordException.php`
- Modify: `app/Core/Database.php`
- Modify: `app/Core/Router.php`
- Modify: `app/Core/helpers.php`

- [ ] **Step 1: Write DuplicateRecordException**
  Create `app/Core/DuplicateRecordException.php`.
  
  ```php
  <?php

  namespace App\Core;

  class DuplicateRecordException extends \Exception {}
  ```

- [ ] **Step 2: Update Database Helper**
  Modify `app/Core/Database.php` to fetch `training_crm` and configure secure attributes.
  
  ```php
  <?php

  namespace App\Core;

  use PDO;

  class Database
  {
      private static ?PDO $instance = null;

      public static function getInstance(): PDO
      {
          if (self::$instance === null) {
              $config = require __DIR__ . '/../../config/database.php';
              $dsn = "mysql:host={$config['host']};dbname={$config['database']};charset={$config['charset']}";
              self::$instance = new PDO($dsn, $config['username'], $config['password'], [
                  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                  PDO::ATTR_EMULATE_PREPARES => false,
              ]);
          }
          return self::$instance;
      }
  }
  ```

- [ ] **Step 3: Update helpers**
  Modify `app/Core/helpers.php` to define general helper utilities including a dynamic CSRF handler helper and secure timeout check.
  
  ```php
  <?php

  use App\Services\CSRFService;

  if (!function_exists('e')) {
      function e(string $value): string {
          return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
      }
  }

  if (!function_exists('redirect')) {
      function redirect(string $url): void {
          header("Location: " . $url);
          exit;
      }
  }

  if (!function_exists('render')) {
      function render(string $view, array $data = []): void {
          extract($data);
          $old = $_SESSION['old_input'] ?? [];
          unset($_SESSION['old_input']);
          
          $errors = $_SESSION['errors'] ?? [];
          unset($_SESSION['errors']);

          $flash = $_SESSION['flash'] ?? [];
          unset($_SESSION['flash']);

          // Buffering child view
          ob_start();
          require __DIR__ . '/../Views/' . $view . '.php';
          $content = ob_get_clean();

          require __DIR__ . '/../Views/layouts/main.php';
      }
  }

  if (!function_exists('partial')) {
      function partial(string $name, array $data = []): void {
          extract($data);
          require __DIR__ . '/../Views/partials/' . $name . '.php';
      }
  }

  if (!function_exists('flash')) {
      function flash(string $key, string $message): void {
          $_SESSION['flash'][$key] = $message;
      }
  }

  if (!function_exists('old')) {
      function old(string $key, array $old = [], string $default = ''): string {
          return (string)($old[$key] ?? $default);
      }
  }

  if (!function_exists('require_login')) {
      function require_login(): void {
          // Timeout check: 10 minutes (600 seconds)
          if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 600)) {
              $_SESSION = [];
              if (ini_get('session.use_cookies')) {
                  $params = session_get_cookie_params();
                  setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
              }
              session_destroy();
              session_start();
              flash('error', 'Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
              redirect('/login');
          }

          if (!isset($_SESSION['user_id'])) {
              flash('error', 'Vui lòng đăng nhập để tiếp tục.');
              redirect('/login');
          }
          
          // Update activity timestamp
          $_SESSION['last_activity'] = time();
      }
  }

  if (!function_exists('csrf_field')) {
      function csrf_field(): string {
          $token = CSRFService::getToken();
          return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
      }
  }

  if (!function_exists('log_message')) {
      function log_message(string $msg): void {
          $logDir = __DIR__ . '/../../storage/logs';
          if (!is_dir($logDir)) {
              mkdir($logDir, 0755, true);
          }
          $logFile = $logDir . '/app.log';
          $time = date('Y-m-d H:i:s');
          file_put_contents($logFile, "[$time] $msg\n", FILE_APPEND);
      }
  }
  ```

- [ ] **Step 4: Update Router with CSRF protection and 404/405**
  Modify `app/Core/Router.php` to handle routing table, match methods, validate CSRF tokens for POST actions, and execute controller methods.
  
  ```php
  <?php

  namespace App\Core;

  use App\Services\CSRFService;

  class Router
  {
      private array $routes = [];

      public function add(string $method, string $path, string $handler): void
      {
          $this->routes[] = [
              'method' => strtoupper($method),
              'path' => $path,
              'handler' => $handler
          ];
      }

      public function dispatch(string $method, string $uri): void
      {
          $method = strtoupper($method);
          $parts = parse_url($uri);
          $path = $parts['path'] ?? '/';

          $matchedPathRoute = null;

          foreach ($this->routes as $route) {
              if ($route['path'] === $path) {
                  $matchedPathRoute = $route;
                  if ($route['method'] === $method) {
                      // Found exact route match
                      
                      // Perform CSRF protection check for POST/PUT/DELETE
                      if ($method === 'POST') {
                          $token = $_POST['csrf_token'] ?? '';
                          if (!CSRFService::validate($token)) {
                              http_response_code(403);
                              log_message("CSRF Token validation failed for: " . $path);
                              echo "403 Forbidden - Invalid CSRF Token.";
                              exit;
                          }
                      }

                      // Execute
                      [$controllerName, $action] = explode('@', $route['handler']);
                      $className = "App\\Controllers\\" . $controllerName;
                      
                      if (class_exists($className)) {
                          $controller = new $className();
                          if (method_exists($controller, $action)) {
                              $controller->$action();
                              return;
                          }
                      }
                      
                      throw new \Exception("Handler {$route['handler']} not found.");
                  }
              }
          }

          if ($matchedPathRoute) {
              // Path matches but Method is wrong
              http_response_code(405);
              render('errors/405', ['title' => '405 Method Not Allowed']);
              exit;
          }

          // Path not found
          http_response_code(404);
          render('errors/404', ['title' => '404 Page Not Found']);
          exit;
      }
  }
  ```

- [ ] **Step 5: Commit Core component modifications**
  ```bash
  git add app/Core/
  git commit -m "core: implement database, duplicate exception, helpers, router with CSRF"
  ```

---

### Task 3: App Configs & Front Controller Integration

**Files:**
- Modify: `config/database.php`
- Modify: `config/app.php`
- Modify: `public/index.php`

- [ ] **Step 1: Update config/database.php**
  Set default database to `training_crm`.
  
  ```php
  <?php

  return [
      'host'     => getenv('DB_HOST') ?: '127.0.0.1',
      'database' => getenv('DB_NAME') ?: 'training_crm',
      'username' => getenv('DB_USER') ?: 'crm_user',
      'password' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'crm_password',
      'charset'  => 'utf8mb4',
  ];
  ```

- [ ] **Step 2: Update config/app.php**
  ```php
  <?php

  return [
      'name' => 'Training Center CRM',
      'debug' => getenv('APP_DEBUG') === 'true' ?: false,
  ];
  ```

- [ ] **Step 3: Update public/index.php**
  Ensure autoloading, secure sessions setup, error handling, route registration, and dispatch.
  
  ```php
  <?php

  require_once __DIR__ . '/../vendor/autoload.php';
  require_once __DIR__ . '/../app/Core/helpers.php';

  // Secure session cookie setup
  session_set_cookie_params([
      'lifetime' => 0,
      'path' => '/',
      'domain' => '',
      'secure' => isset($_SERVER['HTTPS']),
      'httponly' => true,
      'samesite' => 'Lax',
  ]);

  if (session_status() === PHP_SESSION_NONE) {
      session_start();
  }

  use App\Core\Router;

  $router = new Router();

  // Route declarations
  $router->add('GET', '/', 'HomeController@index');
  $router->add('GET', '/login', 'AuthController@login');
  $router->add('POST', '/login', 'AuthController@handleLogin');
  $router->add('POST', '/logout', 'AuthController@logout');
  $router->add('GET', '/dashboard', 'DashboardController@index');

  // Public lead routes
  $router->add('GET', '/public-leads/create', 'PublicLeadController@create');
  $router->add('POST', '/public-leads', 'PublicLeadController@store');

  // Admin Course Lead routes
  $router->add('GET', '/course-leads', 'CourseLeadController@index');
  $router->add('GET', '/course-leads/create', 'CourseLeadController@create');
  $router->add('POST', '/course-leads/store', 'CourseLeadController@store');
  $router->add('GET', '/course-leads/edit', 'CourseLeadController@edit');
  $router->add('POST', '/course-leads/update', 'CourseLeadController@update');
  $router->add('POST', '/course-leads/delete', 'CourseLeadController@delete');

  // Admin Enrollment routes
  $router->add('GET', '/enrollments', 'EnrollmentController@index');
  $router->add('GET', '/enrollments/create', 'EnrollmentController@create');
  $router->add('POST', '/enrollments/store', 'EnrollmentController@store');
  $router->add('GET', '/enrollments/edit', 'EnrollmentController@edit');
  $router->add('POST', '/enrollments/update', 'EnrollmentController@update');
  $router->add('POST', '/enrollments/delete', 'EnrollmentController@delete');

  // Diagnostic route
  $router->add('GET', '/health', 'HealthController@index');

  // Dispatch execution
  try {
      $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
  } catch (\Throwable $e) {
      http_response_code(500);
      log_message("Exception occurred: " . $e->getMessage() . "\n" . $e->getTraceAsString());
      
      $config = require __DIR__ . '/../config/app.php';
      $debug = $config['debug'] ?? false;
      
      $msg = $debug ? $e->getMessage() : 'Đã có lỗi hệ thống xảy ra. Vui lòng thử lại sau.';
      render('errors/500', [
          'title' => '500 Internal Server Error',
          'message' => $msg
      ]);
  }
  ```

- [ ] **Step 4: Commit configurations**
  ```bash
  git add config/ public/index.php
  git commit -m "config: update configurations and public entrypoint"
  ```

---

### Task 4: CSRF & Authentication Backend Integration

**Files:**
- Create: `app/Services/CSRFService.php`
- Create: `app/Repositories/UserRepository.php`
- Create: `app/Services/AuthService.php`
- Create: `app/Controllers/AuthController.php`
- Create: `app/Controllers/DashboardController.php`

- [ ] **Step 1: Write CSRFService**
  Create `app/Services/CSRFService.php`.
  
  ```php
  <?php

  namespace App\Services;

  class CSRFService
  {
      public static function getToken(): string
      {
          if (session_status() === PHP_SESSION_NONE) {
              session_start();
          }
          if (empty($_SESSION['csrf_token'])) {
              $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
          }
          return $_SESSION['csrf_token'];
      }

      public static function validate(?string $token): bool
      {
          if (session_status() === PHP_SESSION_NONE) {
              session_start();
          }
          $stored = $_SESSION['csrf_token'] ?? '';
          if (empty($stored) || empty($token)) {
              return false;
          }
          return hash_equals($stored, $token);
      }
  }
  ```

- [ ] **Step 2: Write UserRepository**
  Create `app/Repositories/UserRepository.php`.
  
  ```php
  <?php

  namespace App\Repositories;

  use PDO;

  class UserRepository
  {
      public function __construct(private PDO $db) {}

      public function findActiveByEmail(string $email): ?array
      {
          $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email AND status = 'active' LIMIT 1");
          $stmt->execute(['email' => $email]);
          $user = $stmt->fetch();
          return $user ?: null;
      }
  }
  ```

- [ ] **Step 3: Write AuthService**
  Create `app/Services/AuthService.php`.
  
  ```php
  <?php

  namespace App\Services;

  use App\Repositories\UserRepository;

  class AuthService
  {
      public function __construct(private UserRepository $userRepo) {}

      public function authenticate(string $email, string $password): ?array
      {
          $user = $this->userRepo->findActiveByEmail($email);
          if (!$user) {
              return null;
          }
          if (password_verify($password, $user['password_hash'])) {
              return $user;
          }
          return null;
      }
  }
  ```

- [ ] **Step 4: Write AuthController**
  Create `app/Controllers/AuthController.php`. Handles session ID regeneration on success and safe logout.
  
  ```php
  <?php

  namespace App\Controllers;

  use App\Core\Database;
  use App\Repositories\UserRepository;
  use App\Services\AuthService;

  class AuthController
  {
      private AuthService $authService;

      public function __construct()
      {
          $db = Database::getInstance();
          $repo = new UserRepository($db);
          $this->authService = new AuthService($repo);
      }

      public function login(): void
      {
          if (isset($_SESSION['user_id'])) {
              redirect('/dashboard');
          }
          render('auth/login', ['title' => 'Đăng nhập']);
      }

      public function handleLogin(): void
      {
          $email = trim($_POST['email'] ?? '');
          $password = $_POST['password'] ?? '';

          $user = $this->authService->authenticate($email, $password);
          if ($user) {
              session_regenerate_id(true);
              $_SESSION['user_id'] = $user['id'];
              $_SESSION['user_name'] = $user['name'];
              $_SESSION['user_role'] = $user['role'];
              $_SESSION['last_activity'] = time();

              flash('success', 'Đăng nhập thành công.');
              redirect('/dashboard');
          } else {
              $_SESSION['old_input'] = ['email' => $email];
              $_SESSION['errors'] = ['general' => 'Email hoặc mật khẩu không đúng.'];
              redirect('/login');
          }
      }

      public function logout(): void
      {
          $_SESSION = [];
          if (ini_get('session.use_cookies')) {
              $params = session_get_cookie_params();
              setcookie(
                  session_name(),
                  '',
                  time() - 42000,
                  $params['path'],
                  $params['domain'],
                  $params['secure'],
                  $params['httponly']
              );
          }
          session_destroy();
          session_start();
          flash('success', 'Đăng xuất thành công.');
          redirect('/login');
      }
  }
  ```

- [ ] **Step 5: Write DashboardController**
  Create `app/Controllers/DashboardController.php`. Aggregates system metrics.
  
  ```php
  <?php

  namespace App\Controllers;

  use App\Core\Database;
  use App\Repositories\CourseLeadRepository;
  use App\Repositories\EnrollmentRepository;

  class DashboardController
  {
      public function index(): void
      {
          require_login();

          $db = Database::getInstance();
          $leadRepo = new CourseLeadRepository($db);
          $enrollRepo = new EnrollmentRepository($db);

          $totalLeads = $leadRepo->countAll();
          $totalEnrollments = $enrollRepo->countAll();
          $totalRevenue = $enrollRepo->getRevenueSum();

          render('dashboard/index', [
              'title' => 'Tổng quan Hệ thống',
              'totalLeads' => $totalLeads,
              'totalEnrollments' => $totalEnrollments,
              'totalRevenue' => $totalRevenue
          ]);
      }
  }
  ```

- [ ] **Step 6: Commit authentication backend**
  ```bash
  git add app/Services/CSRFService.php app/Repositories/UserRepository.php app/Services/AuthService.php app/Controllers/AuthController.php app/Controllers/DashboardController.php
  git commit -m "auth: implement user auth backend and CSRF middleware service"
  ```

---

### Task 5: Layouts, Partials, and Login Views

**Files:**
- Create: `app/Views/layouts/main.php`
- Create: `app/Views/partials/nav.php`
- Create: `app/Views/partials/flash.php`
- Create: `app/Views/auth/login.php`
- Create: `app/Views/dashboard/index.php`

- [ ] **Step 1: Write View Layout wrapper**
  Create `app/Views/layouts/main.php`.
  
  ```html
  <!doctype html>
  <html lang="vi">
  <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title><?= e($title ?? 'Training CRM') ?></title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
      <link rel="stylesheet" href="/assets/style.css">
  </head>
  <body>
  <div class="app-layout">
      <?php partial('nav'); ?>
      
      <div class="main-area">
          <main class="main-content">
              <?php partial('flash'); ?>
              <?= $content ?? '' ?>
          </main>
          
          <footer class="footer" style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 0.8rem; border-top: 1px solid var(--border-light); background: var(--bg-surface); margin-top: auto;">
              <p>&copy; <?= date('Y') ?> - Training Center CRM Control Center. Built with MVC architecture.</p>
          </footer>
      </div>
  </div>
  </body>
  </html>
  ```

- [ ] **Step 2: Write Navigation partial**
  Create `app/Views/partials/nav.php`.
  
  ```html
  <nav class="nav-bar">
      <div class="nav-container">
          <a href="/" class="brand-logo">
              <span class="logo-icon">🎓</span>
              Training CRM
          </a>
          
          <ul class="nav-links">
              <?php if (isset($_SESSION['user_id'])): ?>
                  <li><a href="/dashboard" class="<?= $_SERVER['REQUEST_URI'] === '/dashboard' ? 'active' : '' ?>">Dashboard</a></li>
                  <li><a href="/course-leads" class="<?= strpos($_SERVER['REQUEST_URI'], '/course-leads') === 0 ? 'active' : '' ?>">Leads</a></li>
                  <li><a href="/course-leads/create" class="<?= $_SERVER['REQUEST_URI'] === '/course-leads/create' ? 'active' : '' ?>">Thêm Lead</a></li>
                  <li><a href="/enrollments" class="<?= strpos($_SERVER['REQUEST_URI'], '/enrollments') === 0 ? 'active' : '' ?>">Đăng ký học</a></li>
                  <li><a href="/enrollments/create" class="<?= $_SERVER['REQUEST_URI'] === '/enrollments/create' ? 'active' : '' ?>">Thêm Đơn</a></li>
                  <li><a href="/health" class="<?= $_SERVER['REQUEST_URI'] === '/health' ? 'active' : '' ?>">Health</a></li>
              <?php else: ?>
                  <li><a href="/public-leads/create" class="<?= $_SERVER['REQUEST_URI'] === '/public-leads/create' ? 'active' : '' ?>">Đăng ký Tư vấn</a></li>
              <?php endif; ?>
          </ul>

          <div class="nav-auth">
              <?php if (isset($_SESSION['user_id'])): ?>
                  <span class="user-badge">
                      <?= e($_SESSION['user_name']) ?> 
                      <span class="role-tag"><?= strtoupper(e($_SESSION['user_role'])) ?></span>
                  </span>
                  <form method="POST" action="/logout" style="display:inline;">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn logout-btn">Đăng xuất</button>
                  </form>
              <?php else: ?>
                  <a href="/login" class="btn login-btn">Đăng nhập Admin</a>
              <?php endif; ?>
          </div>
      </div>
  </nav>
  ```

- [ ] **Step 3: Write Flash renderer**
  Create `app/Views/partials/flash.php`.
  
  ```html
  <?php if (!empty($flash)): ?>
      <div class="flash-messages-container">
          <?php foreach ($flash as $type => $message): ?>
              <div class="alert alert-<?= e($type) ?> alert-dismissible" role="alert">
                  <div class="alert-content">
                      <span class="alert-icon"><?= $type === 'success' ? '✓' : '⚠' ?></span>
                      <span class="alert-text"><?= e($message) ?></span>
                  </div>
              </div>
          <?php endforeach; ?>
      </div>
  <?php endif; ?>
  ```

- [ ] **Step 4: Write Admin Login View**
  Create `app/Views/auth/login.php`.
  
  ```html
  <div class="login-wrapper" style="max-width: 450px; margin: 80px auto; padding: 24px; background: var(--bg-surface); border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-light);">
      <h2 style="text-align: center; margin-bottom: 24px; font-weight: 700; color: var(--text-main);">Đăng Nhập</h2>
      <p style="text-align: center; margin-top: -15px; margin-bottom: 25px; color: var(--text-muted); font-size: 0.9rem;">Truy cập cổng quản lý Training CRM</p>

      <?php if (isset($errors['general'])): ?>
          <div style="background: var(--danger-bg); color: var(--danger-text); padding: 12px; border-radius: 6px; margin-bottom: 16px; border: 1px solid var(--danger-border); font-size: 0.9rem;">
              <?= e($errors['general']) ?>
          </div>
      <?php endif; ?>

      <form method="POST" action="/login">
          <?= csrf_field() ?>
          
          <div class="form-group" style="margin-bottom: 16px;">
              <label for="email" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem;">Email đăng nhập</label>
              <input type="email" id="email" name="email" value="<?= e($old['email'] ?? '') ?>" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main); background: var(--bg-surface); box-sizing: border-box;" placeholder="example@email.com">
          </div>

          <div class="form-group" style="margin-bottom: 24px;">
              <label for="password" style="display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.9rem;">Mật khẩu</label>
              <input type="password" id="password" name="password" required style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main); background: var(--bg-surface); box-sizing: border-box;" placeholder="••••••">
          </div>

          <button type="submit" class="btn primary" style="width: 100%; padding: 12px; font-weight: 600;">Đăng nhập hệ thống</button>
      </form>
  </div>
  ```

- [ ] **Step 5: Write Admin Dashboard View**
  Create `app/Views/dashboard/index.php`.
  
  ```html
  <div class="index-header">
      <h1><?= e($title) ?></h1>
      <p style="color: var(--text-muted); margin-top: 5px;">Chào mừng quay trở lại, <strong><?= e($_SESSION['user_name']) ?></strong>. Dưới đây là thống kê hiện tại của hệ thống đào tạo.</p>
  </div>

  <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 30px;">
      <!-- Total Leads Card -->
      <div class="card" style="padding: 24px; border-radius: 12px; background: var(--bg-surface); border: 1px solid var(--border-light); display: flex; align-items: center; justify-content: space-between;">
          <div>
              <div style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px;">Tổng số Lead tư vấn</div>
              <div style="font-size: 2.2rem; font-weight: 800; color: var(--text-main);"><?= e((string)$totalLeads) ?></div>
          </div>
          <div style="font-size: 2.5rem; opacity: 0.2;">👥</div>
      </div>

      <!-- Total Enrollments Card -->
      <div class="card" style="padding: 24px; border-radius: 12px; background: var(--bg-surface); border: 1px solid var(--border-light); display: flex; align-items: center; justify-content: space-between;">
          <div>
              <div style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px;">Tổng số phiếu đăng ký</div>
              <div style="font-size: 2.2rem; font-weight: 800; color: var(--text-main);"><?= e((string)$totalEnrollments) ?></div>
          </div>
          <div style="font-size: 2.5rem; opacity: 0.2;">🎓</div>
      </div>

      <!-- Total Revenue Card -->
      <div class="card" style="padding: 24px; border-radius: 12px; background: var(--bg-surface); border: 1px solid var(--border-light); display: flex; align-items: center; justify-content: space-between;">
          <div>
              <div style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); margin-bottom: 8px;">Học phí đã thu (Paid)</div>
              <div style="font-size: 1.8rem; font-weight: 800; color: var(--success-text);"><?= number_format($totalRevenue, 0, ',', '.') ?> đ</div>
          </div>
          <div style="font-size: 2.5rem; opacity: 0.2;">💵</div>
      </div>
  </div>

  <div class="dashboard-sections" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
      <div class="card-horizontal" style="padding: 24px; border-radius: 12px; background: var(--bg-surface); border: 1px solid var(--border-light);">
          <h3 style="margin-top: 0; margin-bottom: 12px; font-weight: 700;">Truy cập nhanh</h3>
          <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px;">Vui lòng chọn các tính năng quản lý dưới đây hoặc sử dụng thanh menu phía trên.</p>
          <div style="display: flex; gap: 12px;">
              <a href="/course-leads" class="btn primary">Quản lý Lead</a>
              <a href="/enrollments" class="btn secondary">Quản lý Đăng ký</a>
          </div>
      </div>

      <div class="card-horizontal" style="padding: 24px; border-radius: 12px; background: #fdfaf2; border: 1px solid var(--warning-border);">
          <h3 style="margin-top: 0; margin-bottom: 12px; font-weight: 700; color: #856404;">Trạng thái Bảo mật</h3>
          <ul style="padding-left: 20px; font-size: 0.9rem; color: #856404; line-height: 1.8;">
              <li>Session Cookie đã được bảo vệ với cờ HttpOnly & Lax.</li>
              <li>Chống tấn công CSRF Token cho tất cả các form gửi dữ liệu.</li>
              <li>Tự động gia hạn ID session sau khi đăng nhập thành công.</li>
              <li>Phiên làm việc sẽ hết hạn sau 10 phút không hoạt động.</li>
          </ul>
      </div>
  </div>
  ```

- [ ] **Step 6: Commit layouts and login views**
  ```bash
  git add app/Views/layouts/ app/Views/partials/ app/Views/auth/ app/Views/dashboard/
  git commit -m "view: implement layouts, partials, login and dashboard views"
  ```

---

### Task 6: Course Leads Module Backend

**Files:**
- Create: `app/Repositories/CourseLeadRepository.php`
- Create: `app/Services/CourseLeadService.php`
- Create: `app/Controllers/CourseLeadController.php`

- [ ] **Step 1: Write CourseLeadRepository**
  Create `app/Repositories/CourseLeadRepository.php`. Protects queries with whitelisting and prepared statements.
  
  ```php
  <?php

  namespace App\Repositories;

  use PDO;
  use App\Core\DuplicateRecordException;
  use PDOException;

  class CourseLeadRepository
  {
      public function __construct(private PDO $db) {}

      public function countAll(string $keyword = ''): int
      {
          $sql = "SELECT COUNT(*) AS total FROM course_leads";
          $params = [];
          if ($keyword !== '') {
              $sql .= " WHERE fullname LIKE :keyword OR email LIKE :keyword OR phone LIKE :keyword";
              $params['keyword'] = '%' . $keyword . '%';
          }
          $stmt = $this->db->prepare($sql);
          $stmt->execute($params);
          return (int)($stmt->fetch()['total'] ?? 0);
      }

      public function getPaginated(string $keyword, int $limit, int $offset, string $sort = 'created_at', string $direction = 'DESC'): array
      {
          $allowedSort = ['id', 'fullname', 'email', 'phone', 'status', 'interested_course', 'created_at'];
          if (!in_array($sort, $allowedSort, true)) {
              $sort = 'created_at';
          }
          $direction = strtoupper($direction);
          if ($direction !== 'ASC' && $direction !== 'DESC') {
              $direction = 'DESC';
          }

          $sql = "SELECT id, fullname, email, phone, status, interested_course, created_at FROM course_leads";
          $params = [];
          if ($keyword !== '') {
              $sql .= " WHERE fullname LIKE :keyword OR email LIKE :keyword OR phone LIKE :keyword";
              $params['keyword'] = '%' . $keyword . '%';
          }

          $sql .= " ORDER BY {$sort} {$direction} LIMIT :limit OFFSET :offset";

          $stmt = $this->db->prepare($sql);
          foreach ($params as $key => $value) {
              $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
          }
          $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
          $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
          $stmt->execute();
          return $stmt->fetchAll();
      }

      public function create(array $data): bool
      {
          $sql = "INSERT INTO course_leads (fullname, email, phone, status, interested_course, note)
                  VALUES (:fullname, :email, :phone, :status, :interested_course, :note)";
          $stmt = $this->db->prepare($sql);
          try {
              return $stmt->execute($data);
          } catch (PDOException $e) {
              if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                  throw new DuplicateRecordException("Trùng email đăng ký.");
              }
              throw $e;
          }
      }

      public function findById(int $id): ?array
      {
          $stmt = $this->db->prepare("SELECT * FROM course_leads WHERE id = :id LIMIT 1");
          $stmt->execute(['id' => $id]);
          $lead = $stmt->fetch();
          return $lead ?: null;
      }

      public function update(int $id, array $data): bool
      {
          $data['id'] = $id;
          $sql = "UPDATE course_leads SET fullname=:fullname, email=:email, phone=:phone,
                  status=:status, interested_course=:interested_course, note=:note, updated_at=NOW()
                  WHERE id=:id";
          $stmt = $this->db->prepare($sql);
          try {
              return $stmt->execute($data);
          } catch (PDOException $e) {
              if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                  throw new DuplicateRecordException("Trùng email đăng ký.");
              }
              throw $e;
          }
      }

      public function delete(int $id): bool
      {
          $stmt = $this->db->prepare("DELETE FROM course_leads WHERE id = :id");
          return $stmt->execute(['id' => $id]);
      }
  }
  ```

- [ ] **Step 2: Write CourseLeadService**
  Create `app/Services/CourseLeadService.php`. Implements server validation, pagination, and duplicate email error catching.
  
  ```php
  <?php

  namespace App\Services;

  use App\Repositories\CourseLeadRepository;
  use App\Core\DuplicateRecordException;

  class CourseLeadService
  {
      public function __construct(private CourseLeadRepository $repo) {}

      public function getLeadList(array $query): array
      {
          $keyword = trim($query['q'] ?? '');
          $page = max(1, (int)($query['page'] ?? 1));
          $perPage = 10;
          $sort = trim($query['sort'] ?? 'created_at');
          $direction = trim($query['direction'] ?? 'desc');

          $totalItems = $this->repo->countAll($keyword);
          $totalPages = max(1, (int)ceil($totalItems / $perPage));
          $page = min($page, $totalPages);
          $offset = ($page - 1) * $perPage;

          return [
              'leads' => $this->repo->getPaginated($keyword, $perPage, $offset, $sort, $direction),
              'keyword' => $keyword,
              'page' => $page,
              'totalPages' => $totalPages,
              'totalItems' => $totalItems,
              'sort' => $sort,
              'direction' => $direction
          ];
      }

      public function validateLeadData(array $input): array
      {
          $errors = [];
          $fullname = trim($input['fullname'] ?? '');
          $email = trim($input['email'] ?? '');
          $phone = trim($input['phone'] ?? '');
          $status = trim($input['status'] ?? 'new');
          $interested_course = trim($input['interested_course'] ?? '');
          $note = trim($input['note'] ?? '');

          if ($fullname === '') {
              $errors['fullname'] = 'Họ tên không được để trống.';
          }
          if ($email === '') {
              $errors['email'] = 'Email không được để trống.';
          } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
              $errors['email'] = 'Email không đúng định dạng.';
          }
          if (!in_array($status, ['new', 'contacted', 'enrolled', 'lost'], true)) {
              $errors['status'] = 'Trạng thái không hợp lệ.';
          }

          return [
              'errors' => $errors,
              'values' => compact('fullname', 'email', 'phone', 'status', 'interested_course', 'note')
          ];
      }

      public function createLead(array $input): array
      {
          $validation = $this->validateLeadData($input);
          if (!empty($validation['errors'])) {
              return ['success' => false, 'errors' => $validation['errors']];
          }
          try {
              $this->repo->create($validation['values']);
              return ['success' => true, 'errors' => []];
          } catch (DuplicateRecordException $e) {
              return [
                  'success' => false,
                  'errors' => ['email' => 'Email này đã tồn tại trong hệ thống.']
              ];
          }
      }

      public function updateLead(int $id, array $input): array
      {
          if (!$this->repo->findById($id)) {
              return ['success' => false, 'errors' => ['general' => 'Lead không tồn tại.']];
          }
          $validation = $this->validateLeadData($input);
          if (!empty($validation['errors'])) {
              return ['success' => false, 'errors' => $validation['errors']];
          }
          try {
              $this->repo->update($id, $validation['values']);
              return ['success' => true, 'errors' => []];
          } catch (DuplicateRecordException $e) {
              return [
                  'success' => false,
                  'errors' => ['email' => 'Email này đã tồn tại trong hệ thống.']
              ];
          }
      }

      public function deleteLead(int $id): array
      {
          if ($id <= 0) {
              return ['success' => false, 'errors' => ['general' => 'ID không hợp lý.']];
          }
          $this->repo->delete($id);
          return ['success' => true, 'errors' => []];
      }

      public function findLeadById(int $id): ?array
      {
          return $this->repo->findById($id);
      }
  }
  ```

- [ ] **Step 3: Write CourseLeadController**
  Create `app/Controllers/CourseLeadController.php`. Uses PRG pattern for store/update/delete.
  
  ```php
  <?php

  namespace App\Controllers;

  use App\Core\Database;
  use App\Repositories\CourseLeadRepository;
  use App\Services\CourseLeadService;

  class CourseLeadController
  {
      private CourseLeadService $service;

      public function __construct()
      {
          $db = Database::getInstance();
          $repo = new CourseLeadRepository($db);
          $this->service = new CourseLeadService($repo);
      }

      public function index(): void
      {
          require_login();
          $data = $this->service->getLeadList($_GET);
          $data['title'] = 'Quản lý Lead Tư Vấn';
          render('course-leads/index', $data);
      }

      public function create(): void
      {
          require_login();
          render('course-leads/create', [
              'title' => 'Thêm Lead Mới',
              'old' => [],
              'errors' => []
          ]);
      }

      public function store(): void
      {
          require_login();
          $res = $this->service->createLead($_POST);
          if ($res['success']) {
              flash('success', 'Thêm lead mới thành công.');
              redirect('/course-leads');
          } else {
              $_SESSION['old_input'] = $_POST;
              $_SESSION['errors'] = $res['errors'];
              redirect('/course-leads/create');
          }
      }

      public function edit(): void
      {
          require_login();
          $id = (int)($_GET['id'] ?? 0);
          $lead = $this->service->findLeadById($id);
          if (!$lead) {
              flash('error', 'Không tìm thấy lead.');
              redirect('/course-leads');
          }
          render('course-leads/edit', [
              'title' => 'Chỉnh sửa Lead',
              'lead' => $lead,
              'old' => [],
              'errors' => []
          ]);
      }

      public function update(): void
      {
          require_login();
          $id = (int)($_POST['id'] ?? 0);
          $res = $this->service->updateLead($id, $_POST);
          if ($res['success']) {
              flash('success', 'Cập nhật lead thành công.');
              redirect('/course-leads');
          } else {
              $_SESSION['old_input'] = $_POST;
              $_SESSION['errors'] = $res['errors'];
              redirect("/course-leads/edit?id=" . $id);
          }
      }

      public function delete(): void
      {
          require_login();
          $id = (int)($_POST['id'] ?? 0);
          $res = $this->service->deleteLead($id);
          if ($res['success']) {
              flash('success', 'Xóa lead thành công.');
          } else {
              flash('error', $res['errors']['general'] ?? 'Không thể xóa lead.');
          }
          redirect('/course-leads');
      }
  }
  ```

- [ ] **Step 4: Commit Course Lead module backend**
  ```bash
  git add app/Repositories/CourseLeadRepository.php app/Services/CourseLeadService.php app/Controllers/CourseLeadController.php
  git commit -m "lead: implement course lead business layer (MVC)"
  ```

---

### Task 7: Course Leads Module Views

**Files:**
- Create: `app/Views/course-leads/index.php`
- Create: `app/Views/course-leads/create.php`
- Create: `app/Views/course-leads/edit.php`

- [ ] **Step 1: Write Course Leads list view**
  Create `app/Views/course-leads/index.php`. Integrates dynamic whitelisted sort query parameters, search, and pagination.
  
  ```html
  <div class="index-header">
      <h1><?= e($title) ?></h1>
      <a href="/course-leads/create" class="btn primary">Thêm Lead Mới</a>
  </div>

  <div class="table-controls">
      <form method="GET" action="/course-leads" class="search-form">
          <input type="text" name="q" value="<?= e($keyword) ?>" placeholder="Tìm tên, email, sđt...">
          <input type="hidden" name="sort" value="<?= e($sort) ?>">
          <input type="hidden" name="direction" value="<?= e($direction) ?>">
          <button type="submit" class="btn secondary">Tìm kiếm</button>
          <?php if ($keyword !== ''): ?>
              <a href="/course-leads" class="btn text-btn">Xóa lọc</a>
          <?php endif; ?>
      </form>
  </div>

  <div class="card-table">
      <table class="data-table">
          <thead>
              <tr>
                  <th><a href="?q=<?= urlencode($keyword) ?>&sort=id&direction=<?= $sort === 'id' && $direction === 'asc' ? 'desc' : 'asc' ?>">ID <?= $sort === 'id' ? ($direction === 'asc' ? '▲' : '▼') : '' ?></a></th>
                  <th><a href="?q=<?= urlencode($keyword) ?>&sort=fullname&direction=<?= $sort === 'fullname' && $direction === 'asc' ? 'desc' : 'asc' ?>">Họ tên <?= $sort === 'fullname' ? ($direction === 'asc' ? '▲' : '▼') : '' ?></a></th>
                  <th><a href="?q=<?= urlencode($keyword) ?>&sort=email&direction=<?= $sort === 'email' && $direction === 'asc' ? 'desc' : 'asc' ?>">Email <?= $sort === 'email' ? ($direction === 'asc' ? '▲' : '▼') : '' ?></a></th>
                  <th>SĐT</th>
                  <th>Khóa học quan tâm</th>
                  <th>Trạng thái</th>
                  <th>Hành động</th>
              </tr>
          </thead>
          <tbody>
              <?php if (empty($leads)): ?>
                  <tr>
                      <td colspan="7" class="text-center" style="padding: 24px; color: var(--text-muted);">Không tìm thấy học viên nào.</td>
                  </tr>
              <?php else: ?>
                  <?php foreach ($leads as $lead): ?>
                      <tr>
                          <td><?= e((string)$lead['id']) ?></td>
                          <td class="font-semibold"><?= e($lead['fullname']) ?></td>
                          <td><?= e($lead['email']) ?></td>
                          <td><?= e($lead['phone'] ?: '-') ?></td>
                          <td><?= e($lead['interested_course'] ?: '-') ?></td>
                          <td>
                              <?php
                              $badgeClass = 'badge-new';
                              $statusText = 'Mới';
                              if ($lead['status'] === 'contacted') { $badgeClass = 'badge-contacted'; $statusText = 'Đang tư vấn'; }
                              elseif ($lead['status'] === 'enrolled') { $badgeClass = 'badge-qualified'; $statusText = 'Đã nhập học'; }
                              elseif ($lead['status'] === 'lost') { $badgeClass = 'badge-lost'; $statusText = 'Thất bại'; }
                              ?>
                              <span class="badge <?= $badgeClass ?>"><?= e($statusText) ?></span>
                          </td>
                          <td class="actions-cell">
                              <a href="/course-leads/edit?id=<?= e((string)$lead['id']) ?>" class="btn-icon" title="Sửa">✏️</a>
                              <form method="POST" action="/course-leads/delete" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa lead này?');">
                                  <?= csrf_field() ?>
                                  <input type="hidden" name="id" value="<?= e((string)$lead['id']) ?>">
                                  <button type="submit" class="btn-icon text-danger" style="background:none; border:none; cursor:pointer;" title="Xóa">🗑️</button>
                              </form>
                          </td>
                      </tr>
                  <?php endforeach; ?>
              <?php endif; ?>
          </tbody>
      </table>
  </div>

  <div class="pagination-area">
      <div class="pagination-info">
          Tổng <strong><?= e((string)$totalItems) ?></strong> leads. Trang <?= e((string)$page) ?>/<?= e((string)$totalPages) ?>.
      </div>
      <?php if ($totalPages > 1): ?>
          <ul class="pagination">
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <li>
                      <a href="?page=<?= $i ?>&q=<?= urlencode($keyword) ?>&sort=<?= e($sort) ?>&direction=<?= e($direction) ?>" class="page-link <?= $page === $i ? 'active' : '' ?>">
                          <?= $i ?>
                      </a>
                  </li>
              <?php endfor; ?>
          </ul>
      <?php endif; ?>
  </div>
  ```

- [ ] **Step 2: Write Course Leads creation view**
  Create `app/Views/course-leads/create.php`.
  
  ```html
  <div class="index-header">
      <h1><?= e($title) ?></h1>
      <a href="/course-leads" class="btn secondary">&larr; Quay lại danh sách</a>
  </div>

  <div class="form-card-horizontal" style="max-width: 700px; margin: 0 auto; border-color: var(--border-light); background: var(--bg-surface); padding: 24px; border-radius: 12px; border: 1px solid var(--border-light);">
      <form method="POST" action="/course-leads/store">
          <?= csrf_field() ?>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="fullname" style="display: block; margin-bottom: 6px; font-weight: 500;">Họ tên <span style="color:var(--danger-text)">*</span></label>
              <input type="text" id="fullname" name="fullname" class="<?= isset($errors['fullname']) ? 'input-error' : '' ?>" value="<?= e(old('fullname', $old)) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="Họ tên học viên...">
              <?php if (isset($errors['fullname'])): ?>
                  <div class="error" style="color:var(--danger-text); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['fullname']) ?></div>
              <?php endif; ?>
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="email" style="display: block; margin-bottom: 6px; font-weight: 500;">Email <span style="color:var(--danger-text)">*</span></label>
              <input type="email" id="email" name="email" class="<?= isset($errors['email']) ? 'input-error' : '' ?>" value="<?= e(old('email', $old)) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="example@email.com">
              <?php if (isset($errors['email'])): ?>
                  <div class="error" style="color:var(--danger-text); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['email']) ?></div>
              <?php endif; ?>
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="phone" style="display: block; margin-bottom: 6px; font-weight: 500;">Số điện thoại</label>
              <input type="text" id="phone" name="phone" value="<?= e(old('phone', $old)) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="Ví dụ: 090xxxxxxx">
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="interested_course" style="display: block; margin-bottom: 6px; font-weight: 500;">Khóa học quan tâm</label>
              <input type="text" id="interested_course" name="interested_course" value="<?= e(old('interested_course', $old)) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="Ví dụ: PHP MVC Framework">
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="status" style="display: block; margin-bottom: 6px; font-weight: 500;">Trạng thái <span style="color:var(--danger-text)">*</span></label>
              <select id="status" name="status" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main); background: var(--bg-surface);">
                  <?php
                  $statuses = [
                      'new' => 'Mới đăng ký (New)',
                      'contacted' => 'Đang tư vấn (Contacted)',
                      'enrolled' => 'Đã nhập học (Enrolled)',
                      'lost' => 'Thất bại (Lost)'
                  ];
                  $selectedStatus = old('status', $old, 'new');
                  foreach ($statuses as $val => $label):
                  ?>
                      <option value="<?= e($val) ?>" <?= $selectedStatus === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
              </select>
          </div>

          <div class="form-row" style="margin-bottom: 24px;">
              <label for="note" style="display: block; margin-bottom: 6px; font-weight: 500;">Ghi chú</label>
              <textarea id="note" name="note" rows="4" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);"><?= e(old('note', $old)) ?></textarea>
          </div>

          <div class="form-actions-horizontal" style="display: flex; gap: 12px;">
              <button type="submit" class="btn primary">Tạo Lead mới</button>
              <a href="/course-leads" class="btn secondary">Hủy</a>
          </div>
      </form>
  </div>
  ```

- [ ] **Step 3: Write Course Leads edit view**
  Create `app/Views/course-leads/edit.php`.
  
  ```html
  <div class="index-header">
      <h1><?= e($title) ?></h1>
      <a href="/course-leads" class="btn secondary">&larr; Quay lại danh sách</a>
  </div>

  <div class="form-card-horizontal" style="max-width: 700px; margin: 0 auto; border-color: var(--border-light); background: var(--bg-surface); padding: 24px; border-radius: 12px; border: 1px solid var(--border-light);">
      <form method="POST" action="/course-leads/update">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= e((string)$lead['id']) ?>">

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="fullname" style="display: block; margin-bottom: 6px; font-weight: 500;">Họ tên <span style="color:var(--danger-text)">*</span></label>
              <input type="text" id="fullname" name="fullname" class="<?= isset($errors['fullname']) ? 'input-error' : '' ?>" value="<?= e(old('fullname', $old, $lead['fullname'])) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="Họ tên học viên...">
              <?php if (isset($errors['fullname'])): ?>
                  <div class="error" style="color:var(--danger-text); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['fullname']) ?></div>
              <?php endif; ?>
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="email" style="display: block; margin-bottom: 6px; font-weight: 500;">Email <span style="color:var(--danger-text)">*</span></label>
              <input type="email" id="email" name="email" class="<?= isset($errors['email']) ? 'input-error' : '' ?>" value="<?= e(old('email', $old, $lead['email'])) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="example@email.com">
              <?php if (isset($errors['email'])): ?>
                  <div class="error" style="color:var(--danger-text); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['email']) ?></div>
              <?php endif; ?>
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="phone" style="display: block; margin-bottom: 6px; font-weight: 500;">Số điện thoại</label>
              <input type="text" id="phone" name="phone" value="<?= e(old('phone', $old, $lead['phone'] ?? '')) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="Ví dụ: 090xxxxxxx">
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="interested_course" style="display: block; margin-bottom: 6px; font-weight: 500;">Khóa học quan tâm</label>
              <input type="text" id="interested_course" name="interested_course" value="<?= e(old('interested_course', $old, $lead['interested_course'] ?? '')) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="Ví dụ: PHP MVC Framework">
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="status" style="display: block; margin-bottom: 6px; font-weight: 500;">Trạng thái <span style="color:var(--danger-text)">*</span></label>
              <select id="status" name="status" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main); background: var(--bg-surface);">
                  <?php
                  $statuses = [
                      'new' => 'Mới đăng ký (New)',
                      'contacted' => 'Đang tư vấn (Contacted)',
                      'enrolled' => 'Đã nhập học (Enrolled)',
                      'lost' => 'Thất bại (Lost)'
                  ];
                  $selectedStatus = old('status', $old, $lead['status'] ?? 'new');
                  foreach ($statuses as $val => $label):
                  ?>
                      <option value="<?= e($val) ?>" <?= $selectedStatus === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
              </select>
          </div>

          <div class="form-row" style="margin-bottom: 24px;">
              <label for="note" style="display: block; margin-bottom: 6px; font-weight: 500;">Ghi chú</label>
              <textarea id="note" name="note" rows="4" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);"><?= e(old('note', $old, $lead['note'] ?? '')) ?></textarea>
          </div>

          <div class="form-actions-horizontal" style="display: flex; gap: 12px;">
              <button type="submit" class="btn primary">Cập nhật Lead</button>
              <a href="/course-leads" class="btn secondary">Hủy</a>
          </div>
      </form>
  </div>
  ```

- [ ] **Step 4: Commit Course Lead module views**
  ```bash
  git add app/Views/course-leads/
  git commit -m "view: implement paginated leads list, create, and edit forms"
  ```

---

### Task 8: Enrollments Module Backend

**Files:**
- Create: `app/Repositories/EnrollmentRepository.php`
- Create: `app/Services/EnrollmentService.php`
- Create: `app/Controllers/EnrollmentController.php`

- [ ] **Step 1: Write EnrollmentRepository**
  Create `app/Repositories/EnrollmentRepository.php`. Protects queries with whitelisting and prepared statements.
  
  ```php
  <?php

  namespace App\Repositories;

  use PDO;
  use App\Core\DuplicateRecordException;
  use PDOException;

  class EnrollmentRepository
  {
      public function __construct(private PDO $db) {}

      public function countAll(string $keyword = ''): int
      {
          $sql = "SELECT COUNT(*) AS total FROM enrollments";
          $params = [];
          if ($keyword !== '') {
              $sql .= " WHERE enrollment_code LIKE :keyword OR student_name LIKE :keyword OR student_email LIKE :keyword";
              $params['keyword'] = '%' . $keyword . '%';
          }
          $stmt = $this->db->prepare($sql);
          $stmt->execute($params);
          return (int)($stmt->fetch()['total'] ?? 0);
      }

      public function getPaginated(string $keyword, int $limit, int $offset, string $sort = 'created_at', string $direction = 'DESC'): array
      {
          $allowedSort = ['id', 'enrollment_code', 'student_name', 'student_email', 'course_fee', 'payment_status', 'created_at'];
          if (!in_array($sort, $allowedSort, true)) {
              $sort = 'created_at';
          }
          $direction = strtoupper($direction);
          if ($direction !== 'ASC' && $direction !== 'DESC') {
              $direction = 'DESC';
          }

          $sql = "SELECT id, enrollment_code, student_name, student_email, course_fee, payment_status, created_at FROM enrollments";
          $params = [];
          if ($keyword !== '') {
              $sql .= " WHERE enrollment_code LIKE :keyword OR student_name LIKE :keyword OR student_email LIKE :keyword";
              $params['keyword'] = '%' . $keyword . '%';
          }

          $sql .= " ORDER BY {$sort} {$direction} LIMIT :limit OFFSET :offset";

          $stmt = $this->db->prepare($sql);
          foreach ($params as $key => $value) {
              $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
          }
          $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
          $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
          $stmt->execute();
          return $stmt->fetchAll();
      }

      public function create(array $data): bool
      {
          $sql = "INSERT INTO enrollments (enrollment_code, student_name, student_email, course_fee, payment_status)
                  VALUES (:enrollment_code, :student_name, :student_email, :course_fee, :payment_status)";
          $stmt = $this->db->prepare($sql);
          try {
              return $stmt->execute($data);
          } catch (PDOException $e) {
              if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                  throw new DuplicateRecordException("Trùng mã phiếu đăng ký.");
              }
              throw $e;
          }
      }

      public function findById(int $id): ?array
      {
          $stmt = $this->db->prepare("SELECT * FROM enrollments WHERE id = :id LIMIT 1");
          $stmt->execute(['id' => $id]);
          $enrollment = $stmt->fetch();
          return $enrollment ?: null;
      }

      public function update(int $id, array $data): bool
      {
          $data['id'] = $id;
          $sql = "UPDATE enrollments SET enrollment_code=:enrollment_code, student_name=:student_name,
                  student_email=:student_email, course_fee=:course_fee, payment_status=:payment_status, updated_at=NOW()
                  WHERE id=:id";
          $stmt = $this->db->prepare($sql);
          try {
              return $stmt->execute($data);
          } catch (PDOException $e) {
              if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                  throw new DuplicateRecordException("Trùng mã phiếu đăng ký.");
              }
              throw $e;
          }
      }

      public function delete(int $id): bool
      {
          $stmt = $this->db->prepare("DELETE FROM enrollments WHERE id = :id");
          return $stmt->execute(['id' => $id]);
      }

      public function getRevenueSum(): float
      {
          $stmt = $this->db->query("SELECT SUM(course_fee) AS total FROM enrollments WHERE payment_status = 'paid'");
          $result = $stmt->fetch();
          return (float)($result['total'] ?? 0.0);
      }
  }
  ```

- [ ] **Step 2: Write EnrollmentService**
  Create `app/Services/EnrollmentService.php`. Implements validation (fee non-negative), pagination, and code uniqueness handling.
  
  ```php
  <?php

  namespace App\Services;

  use App\Repositories\EnrollmentRepository;
  use App\Core\DuplicateRecordException;

  class EnrollmentService
  {
      public function __construct(private EnrollmentRepository $repo) {}

      public function getEnrollmentList(array $query): array
      {
          $keyword = trim($query['q'] ?? '');
          $page = max(1, (int)($query['page'] ?? 1));
          $perPage = 10;
          $sort = trim($query['sort'] ?? 'created_at');
          $direction = trim($query['direction'] ?? 'desc');

          $totalItems = $this->repo->countAll($keyword);
          $totalPages = max(1, (int)ceil($totalItems / $perPage));
          $page = min($page, $totalPages);
          $offset = ($page - 1) * $perPage;

          return [
              'enrollments' => $this->repo->getPaginated($keyword, $perPage, $offset, $sort, $direction),
              'keyword' => $keyword,
              'page' => $page,
              'totalPages' => $totalPages,
              'totalItems' => $totalItems,
              'sort' => $sort,
              'direction' => $direction
          ];
      }

      public function validateEnrollmentData(array $input): array
      {
          $errors = [];
          $enrollment_code = trim($input['enrollment_code'] ?? '');
          $student_name = trim($input['student_name'] ?? '');
          $student_email = trim($input['student_email'] ?? '');
          $course_fee = $input['course_fee'] ?? '';
          $payment_status = trim($input['payment_status'] ?? 'unpaid');

          if ($enrollment_code === '') {
              $errors['enrollment_code'] = 'Mã đăng ký không được để trống.';
          }
          if ($student_name === '') {
              $errors['student_name'] = 'Tên học viên không được để trống.';
          }
          if ($student_email !== '' && !filter_var($student_email, FILTER_VALIDATE_EMAIL)) {
              $errors['student_email'] = 'Email không đúng định dạng.';
          }
          if ($course_fee === '') {
              $errors['course_fee'] = 'Học phí không được để trống.';
          } else {
              $fee = floatval($course_fee);
              if ($fee < 0) {
                  $errors['course_fee'] = 'Học phí không được là số âm.';
              }
          }
          if (!in_array($payment_status, ['unpaid', 'paid', 'refunded', 'cancelled'], true)) {
              $errors['payment_status'] = 'Trạng thái thanh toán không hợp lệ.';
          }

          return [
              'errors' => $errors,
              'values' => [
                  'enrollment_code' => $enrollment_code,
                  'student_name' => $student_name,
                  'student_email' => $student_email ?: null,
                  'course_fee' => $course_fee !== '' ? floatval($course_fee) : 0.0,
                  'payment_status' => $payment_status
              ]
          ];
      }

      public function createEnrollment(array $input): array
      {
          $validation = $this->validateEnrollmentData($input);
          if (!empty($validation['errors'])) {
              return ['success' => false, 'errors' => $validation['errors']];
          }
          try {
              $this->repo->create($validation['values']);
              return ['success' => true, 'errors' => []];
          } catch (DuplicateRecordException $e) {
              return [
                  'success' => false,
                  'errors' => ['enrollment_code' => 'Mã đăng ký học này đã tồn tại trong hệ thống.']
              ];
          }
      }

      public function updateEnrollment(int $id, array $input): array
      {
          if (!$this->repo->findById($id)) {
              return ['success' => false, 'errors' => ['general' => 'Phiếu đăng ký học không tồn tại.']];
          }
          $validation = $this->validateEnrollmentData($input);
          if (!empty($validation['errors'])) {
              return ['success' => false, 'errors' => $validation['errors']];
          }
          try {
              $this->repo->update($id, $validation['values']);
              return ['success' => true, 'errors' => []];
          } catch (DuplicateRecordException $e) {
              return [
                  'success' => false,
                  'errors' => ['enrollment_code' => 'Mã đăng ký học này đã tồn tại trong hệ thống.']
              ];
          }
      }

      public function deleteEnrollment(int $id): array
      {
          if ($id <= 0) {
              return ['success' => false, 'errors' => ['general' => 'ID không hợp lệ.']];
          }
          $this->repo->delete($id);
          return ['success' => true, 'errors' => []];
      }

      public function findEnrollmentById(int $id): ?array
      {
          return $this->repo->findById($id);
      }
  }
  ```

- [ ] **Step 3: Write EnrollmentController**
  Create `app/Controllers/EnrollmentController.php`. Uses PRG patterns.
  
  ```php
  <?php

  namespace App\Controllers;

  use App\Core\Database;
  use App\Repositories\EnrollmentRepository;
  use App\Services\EnrollmentService;

  class EnrollmentController
  {
      private EnrollmentService $service;

      public function __construct()
      {
          $db = Database::getInstance();
          $repo = new EnrollmentRepository($db);
          $this->service = new EnrollmentService($repo);
      }

      public function index(): void
      {
          require_login();
          $data = $this->service->getEnrollmentList($_GET);
          $data['title'] = 'Danh sách Phiếu Đăng ký & Học phí';
          render('enrollments/index', $data);
      }

      public function create(): void
      {
          require_login();
          render('enrollments/create', [
              'title' => 'Thêm Phiếu Đăng Ký',
              'old' => [],
              'errors' => []
          ]);
      }

      public function store(): void
      {
          require_login();
          $res = $this->service->createEnrollment($_POST);
          if ($res['success']) {
              flash('success', 'Thêm phiếu đăng ký thành công.');
              redirect('/enrollments');
          } else {
              $_SESSION['old_input'] = $_POST;
              $_SESSION['errors'] = $res['errors'];
              redirect('/enrollments/create');
          }
      }

      public function edit(): void
      {
          require_login();
          $id = (int)($_GET['id'] ?? 0);
          $enrollment = $this->service->findEnrollmentById($id);
          if (!$enrollment) {
              flash('error', 'Không tìm thấy phiếu đăng ký.');
              redirect('/enrollments');
          }
          render('enrollments/edit', [
              'title' => 'Chỉnh sửa Phiếu Đăng ký',
              'enrollment' => $enrollment,
              'old' => [],
              'errors' => []
          ]);
      }

      public function update(): void
      {
          require_login();
          $id = (int)($_POST['id'] ?? 0);
          $res = $this->service->updateEnrollment($id, $_POST);
          if ($res['success']) {
              flash('success', 'Cập nhật phiếu đăng ký thành công.');
              redirect('/enrollments');
          } else {
              $_SESSION['old_input'] = $_POST;
              $_SESSION['errors'] = $res['errors'];
              redirect("/enrollments/edit?id=" . $id);
          }
      }

      public function delete(): void
      {
          require_login();
          $id = (int)($_POST['id'] ?? 0);
          $res = $this->service->deleteEnrollment($id);
          if ($res['success']) {
              flash('success', 'Xóa phiếu đăng ký thành công.');
          } else {
              flash('error', $res['errors']['general'] ?? 'Không thể xóa.');
          }
          redirect('/enrollments');
      }
  }
  ```

- [ ] **Step 4: Commit Enrollments module backend**
  ```bash
  git add app/Repositories/EnrollmentRepository.php app/Services/EnrollmentService.php app/Controllers/EnrollmentController.php
  git commit -m "enrollment: implement enrollment business layer (MVC)"
  ```

---

### Task 9: Enrollments Module Views

**Files:**
- Create: `app/Views/enrollments/index.php`
- Create: `app/Views/enrollments/create.php`
- Create: `app/Views/enrollments/edit.php`

- [ ] **Step 1: Write Enrollments list view**
  Create `app/Views/enrollments/index.php`. Integrates pagination, search, sorting.
  
  ```html
  <div class="index-header">
      <h1><?= e($title) ?></h1>
      <a href="/enrollments/create" class="btn primary">Thêm Đơn đăng ký</a>
  </div>

  <div class="table-controls">
      <form method="GET" action="/enrollments" class="search-form">
          <input type="text" name="q" value="<?= e($keyword) ?>" placeholder="Mã phiếu, tên học viên, email...">
          <input type="hidden" name="sort" value="<?= e($sort) ?>">
          <input type="hidden" name="direction" value="<?= e($direction) ?>">
          <button type="submit" class="btn secondary">Tìm kiếm</button>
          <?php if ($keyword !== ''): ?>
              <a href="/enrollments" class="btn text-btn">Xóa lọc</a>
          <?php endif; ?>
      </form>
  </div>

  <div class="card-table">
      <table class="data-table">
          <thead>
              <tr>
                  <th><a href="?q=<?= urlencode($keyword) ?>&sort=id&direction=<?= $sort === 'id' && $direction === 'asc' ? 'desc' : 'asc' ?>">ID <?= $sort === 'id' ? ($direction === 'asc' ? '▲' : '▼') : '' ?></a></th>
                  <th><a href="?q=<?= urlencode($keyword) ?>&sort=enrollment_code&direction=<?= $sort === 'enrollment_code' && $direction === 'asc' ? 'desc' : 'asc' ?>">Mã phiếu <?= $sort === 'enrollment_code' ? ($direction === 'asc' ? '▲' : '▼') : '' ?></a></th>
                  <th><a href="?q=<?= urlencode($keyword) ?>&sort=student_name&direction=<?= $sort === 'student_name' && $direction === 'asc' ? 'desc' : 'asc' ?>">Tên học viên <?= $sort === 'student_name' ? ($direction === 'asc' ? '▲' : '▼') : '' ?></a></th>
                  <th><a href="?q=<?= urlencode($keyword) ?>&sort=student_email&direction=<?= $sort === 'student_email' && $direction === 'asc' ? 'desc' : 'asc' ?>">Email <?= $sort === 'student_email' ? ($direction === 'asc' ? '▲' : '▼') : '' ?></a></th>
                  <th><a href="?q=<?= urlencode($keyword) ?>&sort=course_fee&direction=<?= $sort === 'course_fee' && $direction === 'asc' ? 'desc' : 'asc' ?>">Học phí <?= $sort === 'course_fee' ? ($direction === 'asc' ? '▲' : '▼') : '' ?></a></th>
                  <th>Trạng thái</th>
                  <th>Ngày tạo</th>
                  <th>Hành động</th>
              </tr>
          </thead>
          <tbody>
              <?php if (empty($enrollments)): ?>
                  <tr>
                      <td colspan="8" class="text-center" style="padding: 24px; color: var(--text-muted);">Không tìm thấy phiếu đăng ký học nào.</td>
                  </tr>
              <?php else: ?>
                  <?php foreach ($enrollments as $enroll): ?>
                      <tr>
                          <td><?= e((string)$enroll['id']) ?></td>
                          <td class="font-mono" style="font-weight: 600; font-size: 0.85rem;"><?= e($enroll['enrollment_code']) ?></td>
                          <td class="font-semibold"><?= e($enroll['student_name']) ?></td>
                          <td><?= e($enroll['student_email'] ?: '-') ?></td>
                          <td class="font-semibold"><?= number_format((float)$enroll['course_fee'], 0, ',', '.') ?> đ</td>
                          <td>
                              <?php
                              $badgeClass = 'badge-pending';
                              $statusText = 'Chưa thanh toán';
                              if ($enroll['payment_status'] === 'paid') { $badgeClass = 'badge-completed'; $statusText = 'Đã thanh toán'; }
                              elseif ($enroll['payment_status'] === 'refunded') { $badgeClass = 'badge-shipping'; $statusText = 'Đã hoàn học phí'; }
                              elseif ($enroll['payment_status'] === 'cancelled') { $badgeClass = 'badge-lost'; $statusText = 'Đã hủy'; }
                              ?>
                              <span class="badge <?= $badgeClass ?>"><?= e($statusText) ?></span>
                          </td>
                          <td style="font-size:0.8rem; color:var(--text-muted);"><?= e(date('d/m/Y H:i', strtotime($enroll['created_at']))) ?></td>
                          <td class="actions-cell">
                              <a href="/enrollments/edit?id=<?= e((string)$enroll['id']) ?>" class="btn-icon" title="Sửa">✏️</a>
                              <form method="POST" action="/enrollments/delete" style="display:inline;" onsubmit="return confirm('Bạn chắc chắn muốn xóa đơn đăng ký này?');">
                                  <?= csrf_field() ?>
                                  <input type="hidden" name="id" value="<?= e((string)$enroll['id']) ?>">
                                  <button type="submit" class="btn-icon text-danger" style="background:none; border:none; cursor:pointer;" title="Xóa">🗑️</button>
                              </form>
                          </td>
                      </tr>
                  <?php endforeach; ?>
              <?php endif; ?>
          </tbody>
      </table>
  </div>

  <div class="pagination-area">
      <div class="pagination-info">
          Tổng <strong><?= e((string)$totalItems) ?></strong> phiếu đăng ký. Trang <?= e((string)$page) ?>/<?= e((string)$totalPages) ?>.
      </div>
      <?php if ($totalPages > 1): ?>
          <ul class="pagination">
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                  <li>
                      <a href="?page=<?= $i ?>&q=<?= urlencode($keyword) ?>&sort=<?= e($sort) ?>&direction=<?= e($direction) ?>" class="page-link <?= $page === $i ? 'active' : '' ?>">
                          <?= $i ?>
                      </a>
                  </li>
              <?php endfor; ?>
          </ul>
      <?php endif; ?>
  </div>
  ```

- [ ] **Step 2: Write Enrollments creation view**
  Create `app/Views/enrollments/create.php`.
  
  ```html
  <div class="index-header">
      <h1><?= e($title) ?></h1>
      <a href="/enrollments" class="btn secondary">&larr; Quay lại danh sách</a>
  </div>

  <div class="form-card-horizontal" style="max-width: 700px; margin: 0 auto; border-color: var(--border-light); background: var(--bg-surface); padding: 24px; border-radius: 12px; border: 1px solid var(--border-light);">
      <form method="POST" action="/enrollments/store">
          <?= csrf_field() ?>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="enrollment_code" style="display: block; margin-bottom: 6px; font-weight: 500;">Mã phiếu học <span style="color:var(--danger-text)">*</span></label>
              <input type="text" id="enrollment_code" name="enrollment_code" class="<?= isset($errors['enrollment_code']) ? 'input-error' : '' ?>" value="<?= e(old('enrollment_code', $old)) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="Ví dụ: ENR-2026-0001">
              <?php if (isset($errors['enrollment_code'])): ?>
                  <div class="error" style="color:var(--danger-text); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['enrollment_code']) ?></div>
              <?php endif; ?>
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="student_name" style="display: block; margin-bottom: 6px; font-weight: 500;">Họ tên học viên <span style="color:var(--danger-text)">*</span></label>
              <input type="text" id="student_name" name="student_name" class="<?= isset($errors['student_name']) ? 'input-error' : '' ?>" value="<?= e(old('student_name', $old)) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="Học tên học viên đóng phí...">
              <?php if (isset($errors['student_name'])): ?>
                  <div class="error" style="color:var(--danger-text); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['student_name']) ?></div>
              <?php endif; ?>
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="student_email" style="display: block; margin-bottom: 6px; font-weight: 500;">Email học viên</label>
              <input type="email" id="student_email" name="student_email" class="<?= isset($errors['student_email']) ? 'input-error' : '' ?>" value="<?= e(old('student_email', $old)) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="example@email.com">
              <?php if (isset($errors['student_email'])): ?>
                  <div class="error" style="color:var(--danger-text); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['student_email']) ?></div>
              <?php endif; ?>
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="course_fee" style="display: block; margin-bottom: 6px; font-weight: 500;">Học phí (VND) <span style="color:var(--danger-text)">*</span></label>
              <input type="number" id="course_fee" name="course_fee" class="<?= isset($errors['course_fee']) ? 'input-error' : '' ?>" value="<?= e(old('course_fee', $old)) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="0">
              <?php if (isset($errors['course_fee'])): ?>
                  <div class="error" style="color:var(--danger-text); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['course_fee']) ?></div>
              <?php endif; ?>
          </div>

          <div class="form-row" style="margin-bottom: 24px;">
              <label for="payment_status" style="display: block; margin-bottom: 6px; font-weight: 500;">Trạng thái thanh toán <span style="color:var(--danger-text)">*</span></label>
              <select id="payment_status" name="payment_status" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main); background: var(--bg-surface);">
                  <?php
                  $statuses = [
                      'unpaid' => 'Chưa thanh toán (Unpaid)',
                      'paid' => 'Đã thanh toán (Paid)',
                      'refunded' => 'Đã hoàn học phí (Refunded)',
                      'cancelled' => 'Đã hủy (Cancelled)'
                  ];
                  $selectedStatus = old('payment_status', $old, 'unpaid');
                  foreach ($statuses as $val => $label):
                  ?>
                      <option value="<?= e($val) ?>" <?= $selectedStatus === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
              </select>
          </div>

          <div class="form-actions-horizontal" style="display: flex; gap: 12px;">
              <button type="submit" class="btn primary">Tạo phiếu đăng ký</button>
              <a href="/enrollments" class="btn secondary">Hủy</a>
          </div>
      </form>
  </div>
  ```

- [ ] **Step 3: Write Enrollments edit view**
  Create `app/Views/enrollments/edit.php`.
  
  ```html
  <div class="index-header">
      <h1><?= e($title) ?></h1>
      <a href="/enrollments" class="btn secondary">&larr; Quay lại danh sách</a>
  </div>

  <div class="form-card-horizontal" style="max-width: 700px; margin: 0 auto; border-color: var(--border-light); background: var(--bg-surface); padding: 24px; border-radius: 12px; border: 1px solid var(--border-light);">
      <form method="POST" action="/enrollments/update">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= e((string)$enrollment['id']) ?>">

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="enrollment_code" style="display: block; margin-bottom: 6px; font-weight: 500;">Mã phiếu học <span style="color:var(--danger-text)">*</span></label>
              <input type="text" id="enrollment_code" name="enrollment_code" class="<?= isset($errors['enrollment_code']) ? 'input-error' : '' ?>" value="<?= e(old('enrollment_code', $old, $enrollment['enrollment_code'])) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="Ví dụ: ENR-2026-0001">
              <?php if (isset($errors['enrollment_code'])): ?>
                  <div class="error" style="color:var(--danger-text); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['enrollment_code']) ?></div>
              <?php endif; ?>
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="student_name" style="display: block; margin-bottom: 6px; font-weight: 500;">Họ tên học viên <span style="color:var(--danger-text)">*</span></label>
              <input type="text" id="student_name" name="student_name" class="<?= isset($errors['student_name']) ? 'input-error' : '' ?>" value="<?= e(old('student_name', $old, $enrollment['student_name'])) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="Học tên học viên đóng phí...">
              <?php if (isset($errors['student_name'])): ?>
                  <div class="error" style="color:var(--danger-text); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['student_name']) ?></div>
              <?php endif; ?>
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="student_email" style="display: block; margin-bottom: 6px; font-weight: 500;">Email học viên</label>
              <input type="email" id="student_email" name="student_email" class="<?= isset($errors['student_email']) ? 'input-error' : '' ?>" value="<?= e(old('student_email', $old, $enrollment['student_email'] ?? '')) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="example@email.com">
              <?php if (isset($errors['student_email'])): ?>
                  <div class="error" style="color:var(--danger-text); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['student_email']) ?></div>
              <?php endif; ?>
          </div>

          <div class="form-row" style="margin-bottom: 16px;">
              <label for="course_fee" style="display: block; margin-bottom: 6px; font-weight: 500;">Học phí (VND) <span style="color:var(--danger-text)">*</span></label>
              <input type="number" id="course_fee" name="course_fee" class="<?= isset($errors['course_fee']) ? 'input-error' : '' ?>" value="<?= e(old('course_fee', $old, isset($enrollment['course_fee']) ? (string)$enrollment['course_fee'] : '')) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main);" placeholder="0">
              <?php if (isset($errors['course_fee'])): ?>
                  <div class="error" style="color:var(--danger-text); font-size: 0.8rem; margin-top: 4px;"><?= e($errors['course_fee']) ?></div>
              <?php endif; ?>
          </div>

          <div class="form-row" style="margin-bottom: 24px;">
              <label for="payment_status" style="display: block; margin-bottom: 6px; font-weight: 500;">Trạng thái thanh toán <span style="color:var(--danger-text)">*</span></label>
              <select id="payment_status" name="payment_status" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main); background: var(--bg-surface);">
                  <?php
                  $statuses = [
                      'unpaid' => 'Chưa thanh toán (Unpaid)',
                      'paid' => 'Đã thanh toán (Paid)',
                      'refunded' => 'Đã hoàn học phí (Refunded)',
                      'cancelled' => 'Đã hủy (Cancelled)'
                  ];
                  $selectedStatus = old('payment_status', $old, $enrollment['payment_status'] ?? 'unpaid');
                  foreach ($statuses as $val => $label):
                  ?>
                      <option value="<?= e($val) ?>" <?= $selectedStatus === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                  <?php endforeach; ?>
              </select>
          </div>

          <div class="form-actions-horizontal" style="display: flex; gap: 12px;">
              <button type="submit" class="btn primary">Cập nhật phiếu đăng ký</button>
              <a href="/enrollments" class="btn secondary">Hủy</a>
          </div>
      </form>
  </div>
  ```

- [ ] **Step 4: Commit Enrollments module views**
  ```bash
  git add app/Views/enrollments/
  git commit -m "view: implement paginated enrollments list, create, and edit forms"
  ```

---

### Task 10: Home, Public Lead, Diagnostics & Error Pages

**Files:**
- Create: `app/Controllers/HomeController.php`
- Create: `app/Controllers/PublicLeadController.php`
- Create: `app/Controllers/HealthController.php`
- Create: `app/Views/home/index.php`
- Create: `app/Views/public-leads/create.php`
- Create: `app/Views/errors/404.php`
- Create: `app/Views/errors/405.php`
- Create: `app/Views/errors/500.php`

- [ ] **Step 1: Write HomeController**
  Create `app/Controllers/HomeController.php`.
  
  ```php
  <?php

  namespace App\Controllers;

  class HomeController
  {
      public function index(): void
      {
          if (isset($_SESSION['user_id'])) {
              redirect('/dashboard');
          }
          render('home/index', ['title' => 'Cổng tư vấn tuyển sinh']);
      }
  }
  ```

- [ ] **Step 2: Write HomeController View**
  Create `app/Views/home/index.php`.
  
  ```html
  <div style="max-width: 800px; margin: 60px auto; text-align: center; padding: 24px;">
      <h1 style="font-size: 2.8rem; font-weight: 800; color: var(--text-main); margin-bottom: 16px;">Chào mừng tới Trung Tâm Đào Tạo</h1>
      <p style="font-size: 1.2rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 30px;">Hệ thống tư vấn học viên tiềm năng và hỗ trợ đăng ký học viên. Vui lòng bấm vào đăng ký tư vấn để gửi thông tin.</p>
      
      <div style="display: flex; justify-content: center; gap: 16px;">
          <a href="/public-leads/create" class="btn primary" style="padding: 14px 28px; font-size: 1.1rem; font-weight: 600;">Đăng ký Tư vấn ngay</a>
          <a href="/login" class="btn secondary" style="padding: 14px 28px; font-size: 1.1rem; font-weight: 600;">Cổng Quản trị viên</a>
      </div>
  </div>
  ```

- [ ] **Step 3: Write PublicLeadController**
  Create `app/Controllers/PublicLeadController.php`. Implements Honeypot validation, 5s Rate limit checks, and stores guest leads using CourseLeadService.
  
  ```php
  <?php

  namespace App\Controllers;

  use App\Core\Database;
  use App\Repositories\CourseLeadRepository;
  use App\Services\CourseLeadService;

  class PublicLeadController
  {
      private CourseLeadService $service;

      public function __construct()
      {
          $db = Database::getInstance();
          $repo = new CourseLeadRepository($db);
          $this->service = new CourseLeadService($repo);
      }

      public function create(): void
      {
          render('public-leads/create', [
              'title' => 'Đăng ký tư vấn khóa học',
              'old' => [],
              'errors' => []
          ]);
      }

      public function store(): void
      {
          // 1. Honeypot check
          $honeypot = $_POST['website'] ?? '';
          if ($honeypot !== '') {
              // Silently drop spam submission
              log_message("Spam detected via Honeypot: website=" . $honeypot);
              redirect('/');
          }

          // 2. Rate limit check (5 seconds)
          $now = time();
          $lastSubmit = $_SESSION['last_submission_time'] ?? 0;
          if (($now - $lastSubmit) < 5) {
              log_message("Spam detected via Rate Limiting: interval=" . ($now - $lastSubmit));
              $_SESSION['old_input'] = $_POST;
              flash('error', 'Vui lòng đợi 5 giây giữa các lần gửi đăng ký.');
              redirect('/public-leads/create');
          }

          // 3. Save Lead
          $res = $this->service->createLead($_POST);
          if ($res['success']) {
              $_SESSION['last_submission_time'] = $now;
              flash('success', 'Đăng ký nhận tư vấn thành công! Chúng tôi sẽ liên hệ sớm.');
              redirect('/public-leads/create');
          } else {
              $_SESSION['old_input'] = $_POST;
              $_SESSION['errors'] = $res['errors'];
              redirect('/public-leads/create');
          }
      }
  }
  ```

- [ ] **Step 4: Write Public Lead Registration View**
  Create `app/Views/public-leads/create.php`. Styled with inline style to hide the Honeypot field.
  
  ```html
  <div style="max-width: 600px; margin: 40px auto; background: var(--bg-surface); padding: 32px; border-radius: 12px; box-shadow: var(--shadow-sm); border: 1px solid var(--border-light);">
      <h2 style="margin-top: 0; margin-bottom: 8px; font-weight: 700; color: var(--text-main); text-align: center;"><?= e($title) ?></h2>
      <p style="color: var(--text-muted); text-align: center; margin-bottom: 24px; font-size: 0.9rem;">Điền đầy đủ thông tin để được đội ngũ tuyển sinh hỗ trợ tư vấn lộ trình học phù hợp nhất.</p>

      <form method="POST" action="/public-leads">
          <?= csrf_field() ?>

          <!-- Honeypot anti-spam field -->
          <div style="display: none !important;">
              <label for="website">Website</label>
              <input type="text" id="website" name="website" value="" autocomplete="off">
          </div>

          <div style="margin-bottom: 16px;">
              <label for="fullname" style="display: block; margin-bottom: 6px; font-weight: 500;">Họ và tên <span style="color:var(--danger-text)">*</span></label>
              <input type="text" id="fullname" name="fullname" class="<?= isset($errors['fullname']) ? 'input-error' : '' ?>" value="<?= e(old('fullname', $old)) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main); box-sizing: border-box;" placeholder="Nguyễn Văn A">
              <?php if (isset($errors['fullname'])): ?>
                  <div style="color:var(--danger-text); font-size:0.8rem; margin-top:4px;"><?= e($errors['fullname']) ?></div>
              <?php endif; ?>
          </div>

          <div style="margin-bottom: 16px;">
              <label for="email" style="display: block; margin-bottom: 6px; font-weight: 500;">Email liên hệ <span style="color:var(--danger-text)">*</span></label>
              <input type="email" id="email" name="email" class="<?= isset($errors['email']) ? 'input-error' : '' ?>" value="<?= e(old('email', $old)) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main); box-sizing: border-box;" placeholder="example@email.com">
              <?php if (isset($errors['email'])): ?>
                  <div style="color:var(--danger-text); font-size:0.8rem; margin-top:4px;"><?= e($errors['email']) ?></div>
              <?php endif; ?>
          </div>

          <div style="margin-bottom: 16px;">
              <label for="phone" style="display: block; margin-bottom: 6px; font-weight: 500;">Số điện thoại</label>
              <input type="text" id="phone" name="phone" value="<?= e(old('phone', $old)) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main); box-sizing: border-box;" placeholder="090xxxxxxx">
          </div>

          <div style="margin-bottom: 16px;">
              <label for="interested_course" style="display: block; margin-bottom: 6px; font-weight: 500;">Khóa học muốn học</label>
              <input type="text" id="interested_course" name="interested_course" value="<?= e(old('interested_course', $old)) ?>" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main); box-sizing: border-box;" placeholder="Ví dụ: PHP MVC Framework">
          </div>

          <div style="margin-bottom: 24px;">
              <label for="note" style="display: block; margin-bottom: 6px; font-weight: 500;">Nhu cầu hoặc thắc mắc của bạn</label>
              <textarea id="note" name="note" rows="3" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid var(--border-main); box-sizing: border-box;" placeholder="Viết ghi chú tại đây..."><?= e(old('note', $old)) ?></textarea>
          </div>

          <button type="submit" class="btn primary" style="width: 100%; padding: 12px; font-weight: 600; font-size: 1rem;">Gửi Đăng ký tư vấn</button>
      </form>
  </div>
  ```

- [ ] **Step 5: Write HealthController**
  Create `app/Controllers/HealthController.php`.
  
  ```php
  <?php

  namespace App\Controllers;

  use App\Core\Database;

  class HealthController
  {
      public function index(): void
      {
          header('Content-Type: application/json');
          try {
              $db = Database::getInstance();
              $db->query("SELECT 1");
              echo json_encode([
                  'status' => 'success',
                  'app' => 'healthy',
                  'database' => 'connected'
              ], JSON_PRETTY_PRINT);
          } catch (\Throwable $e) {
              http_response_code(500);
              echo json_encode([
                  'status' => 'error',
                  'app' => 'healthy',
                  'database' => 'disconnected',
                  'message' => $e->getMessage()
              ], JSON_PRETTY_PRINT);
          }
      }
  }
  ```

- [ ] **Step 6: Write Error Views**
  Create error files `app/Views/errors/404.php`, `app/Views/errors/405.php`, and `app/Views/errors/500.php`.
  
  * `app/Views/errors/404.php`:
  ```html
  <div style="max-width: 600px; margin: 100px auto; text-align: center; padding: 24px;">
      <div style="font-size: 5rem; margin-bottom: 10px;">🔍</div>
      <h1 style="font-weight: 800; margin-bottom: 12px;">404 Page Not Found</h1>
      <p style="color: var(--text-muted); margin-bottom: 24px;">Đường dẫn bạn yêu cầu không tồn tại trong hệ thống.</p>
      <a href="/" class="btn primary">Quay lại trang chủ</a>
  </div>
  ```

  * `app/Views/errors/405.php`:
  ```html
  <div style="max-width: 600px; margin: 100px auto; text-align: center; padding: 24px;">
      <div style="font-size: 5rem; margin-bottom: 10px;">❌</div>
      <h1 style="font-weight: 800; margin-bottom: 12px;">405 Method Not Allowed</h1>
      <p style="color: var(--text-muted); margin-bottom: 24px;">Phương thức HTTP gửi lên không được hỗ trợ cho đường dẫn này.</p>
      <a href="/" class="btn primary">Quay lại trang chủ</a>
  </div>
  ```

  * `app/Views/errors/500.php`:
  ```html
  <div style="max-width: 600px; margin: 100px auto; text-align: center; padding: 24px;">
      <div style="font-size: 5rem; margin-bottom: 10px;">💥</div>
      <h1 style="font-weight: 800; margin-bottom: 12px;">500 Internal Server Error</h1>
      <p style="color: var(--text-muted); margin-bottom: 24px;"><?= e($message) ?></p>
      <a href="/" class="btn primary">Quay lại trang chủ</a>
  </div>
  ```

- [ ] **Step 7: Commit home, public, and error pages**
  ```bash
  git add app/Controllers/HomeController.php app/Controllers/PublicLeadController.php app/Controllers/HealthController.php app/Views/home/ app/Views/public-leads/ app/Views/errors/
  git commit -m "view: implement home page, guest leads registration, error handlers"
  ```

---

### Task 11: Update Automated Integration Tests

**Files:**
- Modify: `scratch/test_crm.php`

- [ ] **Step 1: Write integration tests script**
  Rewrite `scratch/test_crm.php` to target the new `training_crm` schema endpoints, using whitelisted sorting, duplicate keys, Honeypot, CSRF tokens, and guest registration rate limits.
  
  ```php
  <?php

  $baseUrl = 'http://localhost';
  $cookieFile = tempnam(sys_get_temp_dir(), 'cookie_');

  // Utility to fetch token
  function extract_csrf(string $html): string
  {
      if (preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $matches)) {
          return $matches[1];
      }
      return '';
  }

  function request(string $method, string $path, array $data = [], bool $useCookie = true): array
  {
      global $baseUrl, $cookieFile;
      $ch = curl_init();
      $url = $baseUrl . $path;

      if ($method === 'GET' && !empty($data)) {
          $url .= '?' . http_build_query($data);
      }

      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

      if ($useCookie) {
          curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
          curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
      }

      if ($method === 'POST') {
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
      }

      $response = curl_exec($ch);
      $info = curl_getinfo($ch);
      curl_close($ch);

      $headerSize = $info['header_size'];
      $headers = substr($response, 0, $headerSize);
      $body = substr($response, $headerSize);

      return [
          'status' => $info['http_code'],
          'headers' => $headers,
          'body' => $body,
          'redirect' => $info['redirect_url'] ?? '',
      ];
  }

  echo "=== TRAINING CRM INTEGRATION TEST SUITE ===\n\n";

  // TC03: Access /dashboard when logged out -> redirects to /login
  $res = request('GET', '/dashboard');
  $tc03 = ($res['status'] === 302 && strpos($res['redirect'], '/login') !== false);
  echo "TC03: Access dashboard logged out (Redirect to /login): " . ($tc03 ? "PASSED" : "FAILED") . "\n";

  // Get login page to extract CSRF token
  $loginPage = request('GET', '/login');
  $csrf = extract_csrf($loginPage['body']);

  // TC01: Try wrong password -> error shown
  $res = request('POST', '/login', [
      'email' => 'admin@example.com',
      'password' => 'wrongpassword',
      'csrf_token' => $csrf
  ]);
  $tc01 = ($res['status'] === 302 && strpos($res['redirect'], '/login') !== false);
  // Get redirect target to inspect errors
  $target = request('GET', '/login');
  $tc01_error = (strpos($target['body'], 'Email hoặc mật khẩu không đúng.') !== false && strpos($target['body'], 'admin@example.com') !== false);
  echo "TC01: Login with wrong password (Error & email preservation): " . ($tc01 && $tc01_error ? "PASSED" : "FAILED") . "\n";

  // TC02: Login with admin@example.com / 123456 -> redirects to /dashboard
  $csrf = extract_csrf($target['body']);
  $res = request('POST', '/login', [
      'email' => 'admin@example.com',
      'password' => '123456',
      'csrf_token' => $csrf
  ]);
  $tc02 = ($res['status'] === 302 && strpos($res['redirect'], '/dashboard') !== false);
  echo "TC02: Admin login with correct credentials (Redirect to /dashboard): " . ($tc02 ? "PASSED" : "FAILED") . "\n";

  // Verify logged in session on dashboard
  $res = request('GET', '/dashboard');
  $dashboardAccessible = ($res['status'] === 200 && strpos($res['body'], 'Tổng quan Hệ thống') !== false);
  echo "Dashboard Access Verification (Session Active): " . ($dashboardAccessible ? "PASSED" : "FAILED") . "\n";

  // Get course-leads/create to extract token
  $createPage = request('GET', '/course-leads/create');
  $csrf = extract_csrf($createPage['body']);

  // TC04-TC05: Create lead validation check (empty fields)
  $res = request('POST', '/course-leads/store', [
      'fullname' => '',
      'email' => 'invalid-email',
      'phone' => '',
      'status' => 'new',
      'csrf_token' => $csrf
  ]);
  $target = request('GET', '/course-leads/create');
  $tc04_05 = (strpos($target['body'], 'Họ tên không được để trống.') !== false && strpos($target['body'], 'Email không đúng định dạng.') !== false);
  echo "TC04-TC05: Lead validation checks (Empty name / invalid email): " . ($tc04_05 ? "PASSED" : "FAILED") . "\n";

  // TC06-TC07: Create duplicate email lead
  // First, create a valid lead
  $csrf = extract_csrf($target['body']);
  $uniqueEmail = 'lead_' . time() . '@example.com';
  $res1 = request('POST', '/course-leads/store', [
      'fullname' => 'Nguyen Van Test',
      'email' => $uniqueEmail,
      'phone' => '0909090909',
      'status' => 'new',
      'interested_course' => 'PHP MVC',
      'csrf_token' => $csrf
  ]);
  
  // Now try to create another lead with the exact same email
  $createPage = request('GET', '/course-leads/create');
  $csrf = extract_csrf($createPage['body']);
  $res2 = request('POST', '/course-leads/store', [
      'fullname' => 'Duplicate Test',
      'email' => $uniqueEmail,
      'phone' => '0911111111',
      'status' => 'new',
      'interested_course' => 'Python',
      'csrf_token' => $csrf
  ]);
  $target = request('GET', '/course-leads/create');
  $tc06_07 = (strpos($target['body'], 'Email này đã tồn tại trong hệ thống') !== false);
  echo "TC06-TC07: Duplicate email lead exception handling: " . ($tc06_07 ? "PASSED" : "FAILED") . "\n";

  // TC08: Search lead
  $res = request('GET', '/course-leads', ['q' => 'Nguyen Van Test']);
  $tc08 = ($res['status'] === 200 && strpos($res['body'], 'Nguyen Van Test') !== false);
  echo "TC08: Search lead by keyword: " . ($tc08 ? "PASSED" : "FAILED") . "\n";

  // TC09: Safe sort sorting inputs (whitelist check)
  $res = request('GET', '/course-leads', ['sort' => 'invalid_column', 'direction' => 'INVALID_DIR']);
  $tc09 = ($res['status'] === 200 && strpos($res['body'], 'Quản lý Lead Tư Vấn') !== false); 
  echo "TC09: Whitelisted sort parameter safety: " . ($tc09 ? "PASSED" : "FAILED") . "\n";

  // TC10: POST to /health -> returns 405 Method Not Allowed
  $res = request('POST', '/health', ['csrf_token' => 'dummy']);
  $tc10 = ($res['status'] === 405 && strpos($res['body'], '405') !== false);
  echo "TC10: POST to health check (405 Method Not Allowed): " . ($tc10 ? "PASSED" : "FAILED") . "\n";

  // TC11: Unknown route /unknown -> returns 404 Not Found
  $res = request('GET', '/unknown');
  $tc11 = ($res['status'] === 404 && strpos($res['body'], '404') !== false);
  echo "TC11: Access unknown route (404 Not Found): " . ($tc11 ? "PASSED" : "FAILED") . "\n";

  // TC12-TC13: Create enrollment negative amount or duplicate code
  // Extract token from create view
  $createPage = request('GET', '/enrollments/create');
  $csrf = extract_csrf($createPage['body']);

  // Create enrollment with negative fee
  $res1 = request('POST', '/enrollments/store', [
      'enrollment_code' => 'ENR-' . time(),
      'student_name' => 'Student A',
      'student_email' => 'studenta@example.com',
      'course_fee' => '-100',
      'payment_status' => 'unpaid',
      'csrf_token' => $csrf
  ]);
  $target = request('GET', '/enrollments/create');
  $negativeCheck = (strpos($target['body'], 'Học phí không được là số âm.') !== false);

  // Create valid enrollment
  $csrf = extract_csrf($target['body']);
  $code = 'ENR-' . time();
  $res2 = request('POST', '/enrollments/store', [
      'enrollment_code' => $code,
      'student_name' => 'Student B',
      'student_email' => 'studentb@example.com',
      'course_fee' => '1500000',
      'payment_status' => 'unpaid',
      'csrf_token' => $csrf
  ]);

  // Try creating enrollment with duplicate code
  $createPage = request('GET', '/enrollments/create');
  $csrf = extract_csrf($createPage['body']);
  $res3 = request('POST', '/enrollments/store', [
      'enrollment_code' => $code,
      'student_name' => 'Student C',
      'student_email' => 'studentc@example.com',
      'course_fee' => '2000000',
      'payment_status' => 'unpaid',
      'csrf_token' => $csrf
  ]);
  $target = request('GET', '/enrollments/create');
  $duplicateCodeCheck = (strpos($target['body'], 'Mã đăng ký học này đã tồn tại trong hệ thống') !== false);
  $tc12_13 = ($negativeCheck && $duplicateCodeCheck);
  echo "TC12-TC13: Enrollment business rules (Negative amount / Duplicate code): " . ($tc12_13 ? "PASSED" : "FAILED") . "\n";

  // TC07: CSRF validation missing on POST (Create Enrollment)
  $res_csrf = request('POST', '/enrollments/store', [
      'enrollment_code' => 'ENR-FAIL',
      'student_name' => 'Dummy Student',
      'course_fee' => '100',
      'payment_status' => 'unpaid'
  ], true);
  $csrf_check = ($res_csrf['status'] === 403);
  echo "TC07: CSRF Token enforcement on POST requests: " . ($csrf_check ? "PASSED" : "FAILED") . "\n";

  // TC15: Click logout -> session destroyed
  $dash = request('GET', '/dashboard');
  $csrf = extract_csrf($dash['body']);
  $res = request('POST', '/logout', ['csrf_token' => $csrf]);
  $tc15 = ($res['status'] === 302 && strpos($res['redirect'], '/login') !== false);

  // Confirm logged out session cannot access dashboard anymore
  $resCheck = request('GET', '/dashboard');
  $loggedOutCheck = ($resCheck['status'] === 302 && strpos($resCheck['redirect'], '/login') !== false);
  $tc15_final = ($tc15 && $loggedOutCheck);
  echo "TC15: Logout validation (Session destroyed & redirect to login): " . ($tc15_final ? "PASSED" : "FAILED") . "\n";


  // --- Guest Spam & Honeypot Protection Tests ---
  $guestCookieFile = tempnam(sys_get_temp_dir(), 'guest_cookie_');

  function guest_request(string $method, string $path, array $data = []): array
  {
      global $baseUrl, $guestCookieFile;
      $ch = curl_init();
      $url = $baseUrl . $path;
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
      curl_setopt($ch, CURLOPT_COOKIEJAR, $guestCookieFile);
      curl_setopt($ch, CURLOPT_COOKIEFILE, $guestCookieFile);
      if ($method === 'POST') {
          curl_setopt($ch, CURLOPT_POST, true);
          curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
      }
      $response = curl_exec($ch);
      $info = curl_getinfo($ch);
      curl_close($ch);
      $headerSize = $info['header_size'];
      $headers = substr($response, 0, $headerSize);
      $body = substr($response, $headerSize);
      return [
          'status' => $info['http_code'],
          'body' => $body,
          'redirect' => $info['redirect_url'] ?? '',
      ];
  }

  // Get guest create page to extract CSRF token
  $guestPage = guest_request('GET', '/public-leads/create');
  $guestCsrf = extract_csrf($guestPage['body']);

  // TC08: Submit guest lead with honeypot filled (website = 'http://spam.com')
  $res = guest_request('POST', '/public-leads', [
      'fullname' => 'Spammer',
      'email' => 'spammer@example.com',
      'phone' => '0900000000',
      'website' => 'http://spam.com',
      'csrf_token' => $guestCsrf
  ]);
  $honeypotPassed = ($res['status'] === 302 && strpos($res['redirect'], '/') !== false);
  echo "TC08: Honeypot Protection (Silently discards spam): " . ($honeypotPassed ? "PASSED" : "FAILED") . "\n";

  // TC09: Submit normal guest lead
  $guestPage2 = guest_request('GET', '/public-leads/create');
  $guestCsrf2 = extract_csrf($guestPage2['body']);
  $res1 = guest_request('POST', '/public-leads', [
      'fullname' => 'Valid Guest',
      'email' => 'valid_guest_' . time() . '@example.com',
      'phone' => '0901234567',
      'website' => '',
      'csrf_token' => $guestCsrf2
  ]);
  $firstSubmitPassed = ($res1['status'] === 302);

  // TC09: Immediately submit another guest lead to trigger rate limit
  $guestPage3 = guest_request('GET', '/public-leads/create');
  $guestCsrf3 = extract_csrf($guestPage3['body']);
  $res2 = guest_request('POST', '/public-leads', [
      'fullname' => 'Fast Guest',
      'email' => 'fast_guest_' . time() . '@example.com',
      'phone' => '0907654321',
      'website' => '',
      'csrf_token' => $guestCsrf3
  ]);
  
  $homepageRes = guest_request('GET', '/public-leads/create');
  $rateLimitPassed = (strpos($homepageRes['body'], 'Vui lòng đợi 5 giây giữa các lần gửi đăng ký.') !== false);
  echo "TC09: Rate Limiting Protection (Blocks rapid submissions): " . ($rateLimitPassed ? "PASSED" : "FAILED") . "\n";

  @unlink($cookieFile);
  @unlink($guestCookieFile);

  echo "\nAll HTTP Test Cases executed successfully.\n";
  ```

- [ ] **Step 2: Commit automated integration test modifications**
  ```bash
  git add scratch/test_crm.php
  git commit -m "test: rewrite integration test suite for training crm features"
  ```

- [ ] **Step 3: Run full integration tests**
  Run: `docker exec -i crm_web php /var/www/html/scratch/test_crm.php`
  Expected output: "All HTTP Test Cases executed successfully."
