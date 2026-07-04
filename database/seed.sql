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
