<?php

// 1. Secure Session Cookie Setup
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// 2. Register PSR-4 Autoloader mapping App\ namespace to app/ folder
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// 3. Load global helpers
require_once __DIR__ . '/../app/Core/helpers.php';

use App\Core\Router;

try {
    $router = new Router();

    // Home / Public Guest Leads
    $router->add('GET', '/', 'HomeController@index');
    $router->add('POST', '/leads/public-store', 'HomeController@publicStore');

    // Auth Routes
    $router->add('GET', '/login', 'AuthController@login');
    $router->add('POST', '/login', 'AuthController@handleLogin');
    $router->add('POST', '/logout', 'AuthController@logout');

    // Admin Dashboard
    $router->add('GET', '/dashboard', 'DashboardController@index');

    // Course Leads Module
    $router->add('GET', '/leads', 'LeadController@index');
    $router->add('GET', '/leads/create', 'LeadController@create');
    $router->add('POST', '/leads/store', 'LeadController@store');
    $router->add('GET', '/leads/edit', 'LeadController@edit');
    $router->add('POST', '/leads/update', 'LeadController@update');
    $router->add('POST', '/leads/delete', 'LeadController@delete');

    // Enrollments Module
    $router->add('GET', '/enrollments', 'EnrollmentController@index');
    $router->add('GET', '/enrollments/create', 'EnrollmentController@create');
    $router->add('POST', '/enrollments/store', 'EnrollmentController@store');
    $router->add('GET', '/enrollments/edit', 'EnrollmentController@edit');
    $router->add('POST', '/enrollments/update', 'EnrollmentController@update');
    $router->add('POST', '/enrollments/delete', 'EnrollmentController@delete');

    // System Diagnostics
    $router->add('GET', '/health', 'HealthController@index');

    // Dispatch request
    $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

} catch (\Throwable $e) {
    http_response_code(500);
    log_message("Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());
    
    // Check if app is in debug mode
    $appConfig = file_exists(__DIR__ . '/../config/app.php') ? require __DIR__ . '/../config/app.php' : ['debug' => false];
    $errorMessage = ($appConfig['debug'] ?? false) ? $e->getMessage() : 'Đã có lỗi hệ thống xảy ra. Vui lòng liên hệ quản trị viên.';
    
    render('errors/500', [
        'title' => '500 Internal Error',
        'message' => $errorMessage
    ]);
}
