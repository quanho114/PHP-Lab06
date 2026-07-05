<?php

// Register PSR-4 Autoloader
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

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    echo "Seeding database with dummy data...\n";
    
    $courses = ['PHP Web Development', 'Laravel Enterprise', 'ReactJS Frontend', 'Python for AI', 'Node.js Backend'];
    $statuses = ['new', 'contacted', 'enrolled', 'lost'];
    
    $db->beginTransaction();
    
    // course_leads
    $stmtLead = $db->prepare("INSERT INTO course_leads (fullname, email, phone, status, interested_course, created_at) VALUES (:fullname, :email, :phone, :status, :interested_course, :created_at)");
    
    for ($i = 1; $i <= 150; $i++) {
        $email = "lead_dummy_{$i}_" . uniqid() . "@example.com";
        $stmtLead->execute([
            'fullname' => "Học Viên Dummy {$i}",
            'email' => $email,
            'phone' => '098' . sprintf('%07d', rand(0, 9999999)),
            'status' => $statuses[array_rand($statuses)],
            'interested_course' => $courses[array_rand($courses)],
            'created_at' => date('Y-m-d H:i:s', time() - rand(0, 30 * 24 * 3600))
        ]);
    }
    
    // enrollments
    $stmtEnroll = $db->prepare("INSERT INTO enrollments (enrollment_code, student_name, student_email, course_fee, payment_status, created_at) VALUES (:enrollment_code, :student_name, :student_email, :course_fee, :payment_status, :created_at)");
    
    $paymentStatuses = ['paid', 'unpaid', 'refunded', 'cancelled'];
    for ($i = 1; $i <= 150; $i++) {
        $code = sprintf('ENR-2026-%04d', $i + 1000);
        $stmtEnroll->execute([
            'enrollment_code' => $code,
            'student_name' => "Sinh Viên Dummy {$i}",
            'student_email' => "student_dummy_{$i}_" . uniqid() . "@example.com",
            'course_fee' => rand(500000, 5000000),
            'payment_status' => $paymentStatuses[array_rand($paymentStatuses)],
            'created_at' => date('Y-m-d H:i:s', time() - rand(0, 30 * 24 * 3600))
        ]);
    }
    
    $db->commit();
    echo "Successfully seeded 150 course leads and 150 enrollments!\n";
    
} catch (\Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "Seeding failed: " . $e->getMessage() . "\n";
}
