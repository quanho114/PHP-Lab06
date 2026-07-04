<?php

$baseUrl = 'http://localhost';
$cookieFile = tempnam(sys_get_temp_dir(), 'cookie_');

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
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Do not auto-follow so we can test redirects

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

function getCSRFToken(string $body): string
{
    if (preg_match('/name="csrf_token" value="([^"]+)"/', $body, $matches)) {
        return $matches[1];
    }
    return '';
}

echo "=== CRM INTEGRATION TEST SUITE ===\n\n";

// TC03: Access /dashboard when logged out -> redirects to /login
$res = request('GET', '/dashboard');
$tc03 = ($res['status'] === 302 && strpos($res['redirect'], '/login') !== false);
echo "TC03: Access dashboard logged out (Redirect to /login): " . ($tc03 ? "PASSED" : "FAILED") . " (Status: {$res['status']})\n";

// TC01: Try wrong password -> error shown, form remembers old email
$loginPage = request('GET', '/login');
$csrf = getCSRFToken($loginPage['body']);

$res = request('POST', '/login', [
    'email' => 'admin@example.com',
    'password' => 'wrongpassword',
    'csrf_token' => $csrf
]);
$tc01 = ($res['status'] === 200 && strpos($res['body'], 'Email hoặc mật khẩu không đúng.') !== false && strpos($res['body'], 'admin@example.com') !== false);
echo "TC01: Login with wrong password (Error & email preservation): " . ($tc01 ? "PASSED" : "FAILED") . "\n";

// TC02: Login with admin@example.com / 123456 -> redirects to /dashboard
$loginPage = request('GET', '/login');
$csrf = getCSRFToken($loginPage['body']);

$res = request('POST', '/login', [
    'email' => 'admin@example.com',
    'password' => '123456',
    'csrf_token' => $csrf
]);
$tc02 = ($res['status'] === 302 && strpos($res['redirect'], '/dashboard') !== false);
echo "TC02: Admin login with correct credentials (Redirect to /dashboard): " . ($tc02 ? "PASSED" : "FAILED") . "\n";

// Verify logged in session on dashboard
$res = request('GET', '/dashboard');
$dashboardAccessible = ($res['status'] === 200 && strpos($res['body'], 'Dashboard Tổng Quan') !== false);
echo "Dashboard Access Verification (Session Active): " . ($dashboardAccessible ? "PASSED" : "FAILED") . "\n";

// TC04-TC05: Create lead validation check (empty fields)
$createLeadPage = request('GET', '/leads/create');
$csrf = getCSRFToken($createLeadPage['body']);

$res = request('POST', '/leads/store', [
    'fullname' => '',
    'email' => 'invalid-email',
    'phone' => '',
    'status' => 'new',
    'csrf_token' => $csrf
]);
$tc04_05 = ($res['status'] === 200 && strpos($res['body'], 'Tên lead không được để trống.') !== false && strpos($res['body'], 'Email không đúng định dạng.') !== false);
echo "TC04-TC05: Lead validation checks (Empty name / invalid email): " . ($tc04_05 ? "PASSED" : "FAILED") . "\n";

// TC06-TC07: Create duplicate email lead
// First, create a valid lead
$uniqueEmail = 'lead_' . time() . '@example.com';
$createLeadPage = request('GET', '/leads/create');
$csrf = getCSRFToken($createLeadPage['body']);

$res1 = request('POST', '/leads/store', [
    'fullname' => 'John Doe',
    'email' => $uniqueEmail,
    'phone' => '0909090909',
    'status' => 'new',
    'interested_course' => 'PHP MVC',
    'note' => 'Test lead',
    'csrf_token' => $csrf
]);

// Now try to create another lead with the exact same email
$createLeadPage = request('GET', '/leads/create');
$csrf = getCSRFToken($createLeadPage['body']);

$res2 = request('POST', '/leads/store', [
    'fullname' => 'Duplicate John',
    'email' => $uniqueEmail,
    'phone' => '0911111111',
    'status' => 'new',
    'interested_course' => 'ReactJS',
    'note' => 'Duplicate check',
    'csrf_token' => $csrf
]);
$tc06_07 = ($res2['status'] === 200 && strpos($res2['body'], 'Email này đã tồn tại trong hệ thống') !== false);
echo "TC06-TC07: Duplicate email lead exception handling: " . ($tc06_07 ? "PASSED" : "FAILED") . "\n";

// TC08: Search lead
$res = request('GET', '/leads', ['keyword' => 'John Doe']);
$tc08 = ($res['status'] === 200 && strpos($res['body'], 'John Doe') !== false);
echo "TC08: Search lead by keyword: " . ($tc08 ? "PASSED" : "FAILED") . "\n";

// TC09: Safe sort sorting inputs (whitelist check)
$res = request('GET', '/leads', ['sort' => 'invalid_column', 'direction' => 'INVALID_DIR']);
$tc09 = ($res['status'] === 200 && strpos($res['body'], 'Quản Lý Course Leads') !== false); // Should fallback safely
echo "TC09: Whitelisted sort parameter safety: " . ($tc09 ? "PASSED" : "FAILED") . "\n";

// TC10: POST to /health -> returns 405 Method Not Allowed
$res = request('POST', '/health');
$tc10 = ($res['status'] === 405 && strpos($res['body'], '405') !== false);
echo "TC10: POST to health check (405 Method Not Allowed): " . ($tc10 ? "PASSED" : "FAILED") . "\n";

// TC11: Unknown route /unknown -> returns 404 Not Found
$res = request('GET', '/unknown');
$tc11 = ($res['status'] === 404 && strpos($res['body'], '404') !== false);
echo "TC11: Access unknown route (404 Not Found): " . ($tc11 ? "PASSED" : "FAILED") . "\n";

// TC12-TC13: Enrollment business rules (Negative course fee / duplicate code)
// Create enrollment with negative course fee
$createEnrPage = request('GET', '/enrollments/create');
$csrf = getCSRFToken($createEnrPage['body']);

$res1 = request('POST', '/enrollments/store', [
    'enrollment_code' => 'ENR-' . date('Y') . '-' . sprintf('%04d', rand(1000, 9999)),
    'student_name' => 'Client A',
    'student_email' => 'clienta@example.com',
    'course_fee' => '-100',
    'payment_status' => 'unpaid',
    'csrf_token' => $csrf
]);
$negativeCheck = (strpos($res1['body'], 'Học phí phải là số lớn hơn hoặc bằng 0.') !== false);

// Create valid enrollment
$enrCode = 'ENR-' . date('Y') . '-' . sprintf('%04d', rand(1000, 9999));
$createEnrPage = request('GET', '/enrollments/create');
$csrf = getCSRFToken($createEnrPage['body']);

$res2 = request('POST', '/enrollments/store', [
    'enrollment_code' => $enrCode,
    'student_name' => 'Client B',
    'student_email' => 'clientb@example.com',
    'course_fee' => '150000',
    'payment_status' => 'unpaid',
    'csrf_token' => $csrf
]);

// Try creating enrollment with duplicate code
$createEnrPage = request('GET', '/enrollments/create');
$csrf = getCSRFToken($createEnrPage['body']);

$res3 = request('POST', '/enrollments/store', [
    'enrollment_code' => $enrCode,
    'student_name' => 'Client C',
    'student_email' => 'clientc@example.com',
    'course_fee' => '200000',
    'payment_status' => 'unpaid',
    'csrf_token' => $csrf
]);
$duplicateCodeCheck = (strpos($res3['body'], 'Mã phiếu đăng ký này đã tồn tại trong hệ thống') !== false);
$tc12_13 = ($negativeCheck && $duplicateCodeCheck);
echo "TC12-TC13: Enrollment business rules (Negative course fee / Duplicate code): " . ($tc12_13 ? "PASSED" : "FAILED") . "\n";

// TC14: Click logout -> session destroyed
$dashboardPage = request('GET', '/dashboard');
$csrf = getCSRFToken($dashboardPage['body']);

$res = request('POST', '/logout', [
    'csrf_token' => $csrf
]);
$tc14 = ($res['status'] === 302 && strpos($res['redirect'], '/login') !== false);

// Confirm logged out session cannot access dashboard anymore
$resCheck = request('GET', '/dashboard');
$loggedOutCheck = ($resCheck['status'] === 302 && strpos($resCheck['redirect'], '/login') !== false);
$tc14_final = ($tc14 && $loggedOutCheck);
echo "TC14: Logout validation (Session destroyed & redirect to login): " . ($tc14_final ? "PASSED" : "FAILED") . "\n";

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

// 1. Submit guest lead with honeypot filled (website = 'http://spam.com')
$homeRes = guest_request('GET', '/');
$csrf = getCSRFToken($homeRes['body']);

$res = guest_request('POST', '/leads/public-store', [
    'fullname' => 'Spammer',
    'email' => 'spammer@example.com',
    'phone' => '0900000000',
    'website' => 'http://spam.com', // Honeypot field
    'interested_course' => 'PHP MVC',
    'note' => 'Spam content',
    'csrf_token' => $csrf
]);
// It should silently redirect to / (homepage)
$honeypotPassed = ($res['status'] === 302 && strpos($res['redirect'], '/') !== false);
echo "Honeypot Protection (Silently discards spam): " . ($honeypotPassed ? "PASSED" : "FAILED") . "\n";

// 2. Submit normal guest lead
$homeRes = guest_request('GET', '/');
$csrf = getCSRFToken($homeRes['body']);

$res1 = guest_request('POST', '/leads/public-store', [
    'fullname' => 'Valid Guest',
    'email' => 'valid_guest_' . time() . '@example.com',
    'phone' => '0901234567',
    'website' => '', // Empty honeypot
    'interested_course' => 'PHP MVC',
    'note' => 'I want to study',
    'csrf_token' => $csrf
]);
$firstSubmitPassed = ($res1['status'] === 302);

// 3. Immediately submit another guest lead to trigger rate limit
$homeRes = guest_request('GET', '/');
$csrf = getCSRFToken($homeRes['body']);

$res2 = guest_request('POST', '/leads/public-store', [
    'fullname' => 'Fast Guest',
    'email' => 'fast_guest_' . time() . '@example.com',
    'phone' => '0907654321',
    'website' => '',
    'interested_course' => 'ReactJS',
    'note' => 'Fast submit',
    'csrf_token' => $csrf
]);
// Since it was submitted immediately, it should redirect to / and have the rate limit warning
$homepageRes = guest_request('GET', '/');
$rateLimitPassed = (strpos($homepageRes['body'], 'Hành vi gửi biểu mẫu quá nhanh. Vui lòng đợi 5 giây giữa các lần đăng ký.') !== false);
echo "Rate Limiting Protection (Blocks rapid submissions): " . ($rateLimitPassed ? "PASSED" : "FAILED") . "\n";

// Clean up cookie files
@unlink($cookieFile);
@unlink($guestCookieFile);

echo "\nAll HTTP Test Cases executed successfully.\n";
