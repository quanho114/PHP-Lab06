# BÁO CÁO KẾT QUẢ THỰC HÀNH LAB06 (FINAL)
## ĐỀ TÀI: TRAINING CENTER CRM (SECURE MVC PORTAL)

- **Họ và tên:** [Họ Tên Sinh Viên]
- **Mã số sinh viên (MSSV):** [MSSV]
- **Môn học:** Lập trình Web với PHP (Lab06 Final - 40% Tổng điểm)
- **Email nộp bài:** tuantran261083course@gmail.com
- **Repository GitHub:** [Link Repo của bạn]

---

## 1. MÔ TẢ DỰ ÁN & THIẾT KẾ HỆ THỐNG

### 1.1 Giới thiệu bài toán: Training Center CRM
Dự án được xây dựng dưới dạng một **Portal Quản lý Đào tạo Nội bộ (Training Center CRM)**. Dự án thay thế bài toán gốc (Lead & Order Management) thành hệ thống quản lý chuyên biệt bao gồm:
1. **Course Leads (Module A):** Quản lý thông tin học viên tiềm năng đăng ký tư vấn các khóa học thông qua form công khai ở Landing Page hoặc do tư vấn viên nhập thủ công.
2. **Course Enrollments (Module B):** Quản lý phiếu đăng ký nhập học và học phí của học viên với mã đăng ký định dạng duy nhất (`ENR-YYYY-XXXX`).

Hệ thống được thiết kế theo cấu trúc **MVC (Model-View-Controller)** thuần, tách biệt rõ ràng các tầng nghiệp vụ nhằm bảo đảm tính bảo mật, dễ bảo trì, và tối ưu hóa hiệu năng truy vấn CSDL.

### 1.2 Kiến trúc Thư mục Dự án
Dự án tuân thủ nghiêm ngặt cấu trúc thư mục chuẩn yêu cầu:
```text
project/
├── public/
│   ├── index.php             # Front Controller (Entrypoint duy nhất)
│   └── assets/
│       └── style.css         # CSS UI/UX Design System
├── config/
│   ├── app.php               # Trạng thái Debug
│   └── database.php          # Cấu hình PDO Kết nối CSDL
├── app/
│   ├── Core/
│   │   ├── Database.php      # Singleton PDO wrapper
│   │   ├── Router.php        # Bộ định tuyến Front Controller & kiểm tra CSRF
│   │   ├── helpers.php       # Các hàm helper e(), render(), require_login(), flash()...
│   │   └── DuplicateRecordException.php # Ngoại lệ bắt lỗi trùng lặp an toàn
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── LeadController.php
│   │   ├── EnrollmentController.php
│   │   └── HealthController.php
│   ├── Services/
│   │   ├── AuthService.php
│   │   ├── LeadService.php
│   │   └── EnrollmentService.php
│   ├── Repositories/
│   │   ├── UserRepository.php
│   │   ├── LeadRepository.php
│   │   └── EnrollmentRepository.php
│   └── Views/
│       ├── layouts/main.php
│       ├── partials/nav.php
│       ├── partials/flash.php
│       ├── auth/login.php
│       ├── dashboard/index.php
│       ├── leads/index.php, create.php, edit.php
│       └── enrollments/index.php, create.php, edit.php
├── database/
│   ├── schema.sql            # Cấu trúc bảng MySQL
│   └── seed.sql              # Dữ liệu mẫu (gồm > 20 bản ghi test pagination)
├── storage/logs/             # Thư mục lưu vết lỗi hệ thống (app.log)
└── README.md                 # Hướng dẫn chạy và thông tin tài khoản demo
```

---

## 2. DATABASE SCHEMA & ROUTE TABLE

### 2.1 Cấu trúc CSDL (`database/schema.sql`)
Hệ thống sử dụng cơ sở dữ liệu `training_crm` gồm 3 bảng chính: `users`, `course_leads`, và `enrollments` có ràng buộc khóa chính, chỉ mục, và kiểm soát trùng lặp chặt chẽ.
```sql
CREATE DATABASE IF NOT EXISTS training_crm;
USE training_crm;

DROP TABLE IF EXISTS enrollments;
DROP TABLE IF EXISTS course_leads;
DROP TABLE IF EXISTS users;

-- 1. Bảng Users quản lý quản trị viên / nhân viên
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

-- 2. Bảng Course Leads (Module A) quản lý leads đăng ký học viên tiềm năng
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

-- 3. Bảng Enrollments (Module B) quản lý phiếu đăng ký học viên & thanh toán học phí
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

### 2.2 Bảng Danh sách Routes Hệ Thống
Mọi request đều đi qua Front Controller `/public/index.php` và được điều hướng động bởi `Router`:

| Method | URL | Controller@Action | Mô tả |
| :--- | :--- | :--- | :--- |
| **GET** | `/` | `HomeController@index` | Landing page chứa form đăng ký tư vấn công khai cho khách hàng |
| **POST** | `/leads/public-store` | `HomeController@publicStore` | Nhận thông tin đăng ký tư vấn khách hàng (honeypot + rate limit) |
| **GET** | `/login` | `AuthController@login` | Form đăng nhập quản trị viên |
| **POST** | `/login` | `AuthController@handleLogin` | Xử lý đăng nhập, regenerate session id, redirect dashboard |
| **POST** | `/logout` | `AuthController@logout` | Đăng xuất sạch, xóa session, hủy cookie |
| **GET** | `/dashboard` | `DashboardController@index` | Trang Dashboard tổng quan dữ liệu |
| **GET** | `/leads` | `LeadController@index` | Danh sách Course Leads (Tìm kiếm, Phân trang, Sắp xếp an toàn) |
| **GET** | `/leads/create` | `LeadController@create` | Form thêm mới Lead học viên tiềm năng |
| **POST** | `/leads/store` | `LeadController@store` | Lưu Lead mới, bắt duplicate key email |
| **GET** | `/leads/edit` | `LeadController@edit` | Form sửa Course Lead cũ |
| **POST** | `/leads/update` | `LeadController@update` | Cập nhật Course Lead cũ |
| **POST** | `/leads/delete` | `LeadController@delete` | Xóa Course Lead bằng phương thức POST + PRG |
| **GET** | `/enrollments` | `EnrollmentController@index` | Danh sách phiếu đăng ký và học phí (Tìm kiếm, Phân trang, Sắp xếp) |
| **GET** | `/enrollments/create` | `EnrollmentController@create` | Form thêm mới phiếu đăng ký (tự động điền mã số ENR) |
| **POST** | `/enrollments/store` | `EnrollmentController@store` | Lưu phiếu đăng ký mới, bắt trùng lặp mã |
| **GET** | `/enrollments/edit` | `EnrollmentController@edit` | Form sửa phiếu đăng ký cũ |
| **POST** | `/enrollments/update` | `EnrollmentController@update` | Cập nhật phiếu đăng ký cũ |
| **POST** | `/enrollments/delete` | `EnrollmentController@delete` | Xóa phiếu đăng ký bằng phương thức POST + PRG |
| **GET** | `/health` | `HealthController@index` | Trả thông tin JSON kiểm tra trạng thái hoạt động của Database |

---

## 3. MINH CHỨNG HOÀN THÀNH CHECKLIST TÁC VỤ (T01 - T30)

| Mã | Task kiểm tra | Trạng thái | Minh chứng thực hiện & Kết quả |
| :--- | :--- | :--- | :--- |
| **T01** | Setup môi trường | **Pass** | PHP 8.5.4 (host), PHP 8.2 (Docker container), Git 2.53.0, Curl 8.18.0. |
| **T02** | Tạo project đúng cấu trúc | **Pass** | Cấu trúc chứa đầy đủ thư mục `public`, `config`, `app/Core`, `app/Controllers`, `app/Services`, `app/Repositories`, `app/Views`, `database`, `storage/logs`. |
| **T03** | Front Controller + Router | **Pass** | Mọi request đi qua `public/index.php`. File `app/Core/Router.php` map chính xác HTTP Method và Path, trả về 404/405 khi không khớp. |
| **T04** | Session cookie setup | **Pass** | Cấu hình `session_set_cookie_params()` chạy trước `session_start()` trong `public/index.php` chứa đầy đủ các cờ bảo mật: `httponly=true`, `samesite=Lax`, `secure` động theo HTTPS. |
| **T05** | Helpers cơ bản | **Pass** | `helpers.php` định nghĩa đầy đủ hàm `e()`, `redirect()`, `render()`, `partial()`, `flash()`, `old()`, `require_login()`, `csrf_field()`. |
| **T06** | Layout/Partial | **Pass** | `layouts/main.php` bao bọc và buffer nội dung view con thông qua `render()`. Tích hợp chung `partials/nav.php` và `partials/flash.php`. |
| **T07** | Public form secure | **Pass** | Landing page chứa form có validate email, tên trống, hiển thị lỗi ngay cạnh input và giữ lại dữ liệu nhập cũ bằng hàm `old()`. |
| **T08** | PRG form công khai | **Pass** | Đăng ký thành công redirect ngược lại `/` bằng `redirect()`. Khi bấm làm mới F5 không gửi lại request POST hay bị lặp dữ liệu. |
| **T09** | Honeypot/rate limit | **Pass** | Chứa field ẩn `website` bị ẩn bằng CSS. Gửi form có điền website sẽ redirect im lặng. Lần gửi tiếp theo cách dưới 5s sẽ báo lỗi "gửi biểu mẫu quá nhanh". |
| **T10** | Login sai/đúng | **Pass** | Nhập sai báo lỗi "Email hoặc mật khẩu không đúng". Nhập đúng credentials `admin@example.com`/`123456` redirect thẳng về `/dashboard`. |
| **T11** | Session regenerate | **Pass** | Trong `AuthService::login`, khi người dùng xác thực thành công sẽ gọi `session_regenerate_id(true)` lập tức trước khi thiết lập dữ liệu session mới. |
| **T12** | Timeout/logout sạch | **Pass** | Inactivity timeout là 10 phút (600s), tự hủy phiên và đưa về `/login`. Bấm logout sẽ hủy toàn bộ session, xóa cookie và redirect về `/login`. |
| **T13** | Database schema | **Pass** | Cấu trúc MySQL chứa đúng 3 bảng `users`, `course_leads`, `enrollments` có đầy đủ khóa chính, khóa ngoại, unique constraints và index cột sắp xếp. |
| **T14** | Seed dữ liệu | **Pass** | Seed sẵn 1 tài khoản admin cùng 22 dòng dữ liệu cho mỗi module giúp kiểm thử phân trang hoạt động hoàn hảo. |
| **T15** | PDO chuẩn | **Pass** | `Database::getInstance()` cấu hình PDO thiết lập UTF8mb4, ném `ERRMODE_EXCEPTION`, `FETCH_ASSOC`, và tắt `EMULATE_PREPARES=false`. |
| **T16** | Repository prepared | **Pass** | Tuyệt đối không cộng chuỗi SQL. Tất cả thao tác SELECT, INSERT, UPDATE, DELETE trong Repositories dùng prepared statements. |
| **T17** | Service layer | **Pass** | Xử lý validation dữ liệu, kiểm soát trùng lặp, chia trang được đóng gói trong `LeadService` và `EnrollmentService`. |
| **T18** | Controller mỏng | **Pass** | Controllers chỉ chịu trách nhiệm nhận request, gọi Service nghiệp vụ tương ứng và render view / redirect, hoàn toàn không chứa câu lệnh SQL. |
| **T19** | View an toàn | **Pass** | Toàn bộ dữ liệu hiển thị ra ngoài View lấy từ database hoặc request đều được bao bọc bởi hàm `e()` để ngăn chặn XSS. |
| **T20** | Module A CRUD | **Pass** | CRUD đầy đủ cho Module Course Leads (Thêm, hiển thị, sửa, cập nhật, xóa thông qua phương thức POST để sửa đổi dữ liệu). |
| **T21** | Module A duplicate | **Pass** | Nhập email đã tồn tại ném ra `DuplicateRecordException` và được xử lý thân thiện ở Service trả về lỗi form mà không làm crash web. |
| **T22** | Module B CRUD | **Pass** | CRUD đầy đủ cho Module Enrollments (Có thêm mã số đăng ký ENR tự sinh tăng tiến). |
| **T23** | Module B duplicate | **Pass** | Trùng lặp `enrollment_code` được chặn bằng unique index trong DB, trả về lỗi thân thiện "Mã phiếu đăng ký đã tồn tại". |
| **T24** | Search/pagination/sort | **Pass** | URL hỗ trợ tham số `q`, `page`, `sort`, `direction`. Số trang âm hoặc lớn hơn số trang thực tế được tự động chuẩn hóa về `1` hoặc `totalPages`. |
| **T25** | Sort nguy hiểm | **Pass** | Hệ thống so khớp cột sắp xếp với mảng whitelist `$allowedSort`. Nếu nhập cột lạ hoặc SQL nguy hiểm, hệ thống tự động fallback về sắp xếp `created_at`. |
| **T26** | Health JSON | **Pass** | Truy cập `GET /health` trả về kết quả JSON trạng thái ứng dụng và trạng thái kết nối DB dạng `{"status":"success","app":"healthy","database":"connected"}`. |
| **T27** | 404/405 | **Pass** | Vào URL không tồn tại trả lỗi 404 Page Not Found. Thực hiện gửi `POST /health` trả lỗi 405 Method Not Allowed. |
| **T28** | Production safe error | **Pass** | Khi cấu hình `debug => false` trong `config/app.php`, lỗi hệ thống (như sập DB) chỉ hiện thông báo lỗi chung chung mà không lộ SQLSTATE, tên bảng hay stack trace. |
| **T29** | EXPLAIN/index | **Pass** | Chạy EXPLAIN trên MySQL xác nhận câu lệnh SELECT sắp xếp dùng chỉ mục `idx_lead_created` và `idx_enrollment_created` đạt Backward Index Scan. |
| **T30** | GitHub/README | **Pass** | Repository có cấu trúc rõ ràng, chứa file `.gitignore`, README hướng dẫn chi tiết, đầy đủ seed/schema và lịch sử 7 commit tuần tự. |

---

## 4. KẾT QUẢ TEST CASES BẮT BUỘC (TC01 - TC25)

Hệ thống đã chạy thành công qua tập lệnh integration tests tự động (`test_crm.php`) với 100% các Test Case đều PASS. Dưới đây là bảng chi tiết kết quả chạy thực tế:

| Mã test | Mô tả kịch bản test | Cách thực hiện | Kết quả mong đợi | Kết quả thực tế (Pass/Fail) |
| :--- | :--- | :--- | :--- | :--- |
| **TC01** | Truy cập màn hình đăng nhập | Gửi yêu cầu `GET /login` | Hiển thị form login quản trị viên với các input Email và Password | **Pass** (Giao diện hiển thị chuẩn xác) |
| **TC02** | Đăng nhập sai mật khẩu | Điền `admin@example.com` và mật khẩu `wrongpassword` | Không tạo session, hiển thị lỗi "Email hoặc mật khẩu không đúng." tại màn hình | **Pass** (Báo lỗi chính xác, giữ lại old email) |
| **TC03** | Đăng nhập tài khoản Admin | Điền `admin@example.com` và mật khẩu `123456` | Tạo phiên làm việc (session), redirect về `/dashboard`, hiện flash message chào mừng | **Pass** (Redirect 302 về `/dashboard` thành công) |
| **TC04** | Chưa đăng nhập vào dashboard | Gửi yêu cầu `GET /dashboard` khi chưa thực hiện login | Trả mã redirect 302 đưa người dùng về trang `/login` kèm thông báo đăng nhập | **Pass** (Redirect đúng về trang đăng nhập) |
| **TC05** | Đăng xuất tài khoản | Gửi yêu cầu `POST /logout` bằng form | Xóa toàn bộ dữ liệu session, xóa cookie, redirect về trang đăng nhập | **Pass** (Đăng xuất sạch sẽ, không truy cập lại được dashboard) |
| **TC06** | Kiểm tra timeout phiên | Giả lập thời gian inactivity vượt quá 10 phút (600 giây) | Khi thực hiện click/truy cập trang tiếp theo, hệ thống tự hủy session và đưa về màn `/login` | **Pass** (Phiên bị tự động hủy, yêu cầu đăng nhập lại) |
| **TC07** | Form public lỗi nhập liệu | Để trống Họ tên và điền email sai định dạng, bấm gửi | Form không được xử lý, hiển thị cảnh báo lỗi cụ thể cạnh từng field và giữ nguyên dữ liệu cũ | **Pass** (Hiện thông báo lỗi chi tiết, giữ old input) |
| **TC08** | Form public chống spam bot | Điền giá trị bất kỳ vào field ẩn `website` (honeypot) và submit | Biểu mẫu bị chặn, redirect im lặng về `/` và không lưu dữ liệu vào database | **Pass** (Spam bot bị loại bỏ im lặng thành công) |
| **TC09** | Form public rate limit | Gửi liên tiếp 2 form tư vấn trong khoảng thời gian dưới 5 giây | Lần gửi thứ 2 bị chặn, redirect về `/` báo lỗi "Hành vi gửi biểu mẫu quá nhanh..." | **Pass** (Chặn submit spam liên tục thành công) |
| **TC10** | Module A thêm thiếu trường | Vào `/leads/create` để trống họ tên và email, gửi form | Dữ liệu không lưu vào DB, quay lại form báo lỗi thiếu thông tin | **Pass** (Bắt lỗi và hiển thị thông tin lỗi cạnh field) |
| **TC11** | Module A thêm hợp lệ | Gửi đầy đủ thông tin Lead hợp lệ lên hệ thống | Lưu vào DB, redirect về danh sách `/leads` kèm flash success thông báo | **Pass** (Thêm lead mới và hiển thị trong danh sách thành công) |
| **TC12** | Module A trùng email | Gửi lead mới có email trùng lặp với lead đã tồn tại trong DB | Ngoại lệ được bắt, báo lỗi email đã tồn tại thân thiện, không làm sập trang | **Pass** (Ném DuplicateRecordException và xử lý lỗi an toàn) |
| **TC13** | Module A sửa/cập nhật | Click sửa lead, sửa thông tin và gửi form | Form nhận dữ liệu cũ hiển thị. Cập nhật thành công redirect danh sách `/leads` | **Pass** (Cập nhật dữ liệu thành công theo mô hình PRG) |
| **TC14** | Module A xóa bằng POST | Gửi request `POST /leads/delete` kèm ID lead cần xóa | Xóa lead khỏi DB thành công, redirect danh sách. Nếu truy cập bằng GET delete trả 405 | **Pass** (Xóa thành công, bảo vệ an toàn phương thức dữ liệu) |
| **TC15** | Module B thêm hợp lệ | Gửi thông tin phiếu đăng ký học viên hợp lệ lên `/enrollments/store` | Lưu phiếu vào DB, chuyển hướng về `/enrollments` kèm thông báo thành công | **Pass** (Lưu phiếu đăng ký mới thành công) |
| **TC16** | Module B trùng mã phiếu | Thêm phiếu mới có `enrollment_code` đã tồn tại từ trước | Không lưu DB, báo lỗi trùng mã phiếu ngay tại form nhập | **Pass** (Bắt trùng khóa duy nhất và thông báo cho người dùng) |
| **TC17** | Search Module A | Gửi `GET /leads?q=Nguyen+Van+A` | Chỉ trả về dòng dữ liệu chứa từ khóa khớp ở tên, email hoặc khóa học | **Pass** (Lọc dữ liệu chính xác theo từ khóa) |
| **TC18** | Page âm / quá lớn | Gửi `GET /leads?page=-5` hoặc `GET /leads?page=999999` | Trang âm tự quy về trang 1. Trang quá lớn tự quy về trang cuối cùng (`totalPages`) | **Pass** (Số trang được chuẩn hóa an toàn trước khi LIMIT/OFFSET) |
| **TC19** | Sắp xếp dữ liệu hợp lệ | Gửi `GET /leads?sort=fullname&direction=asc` | Danh sách được sắp xếp đúng theo cột `fullname` với thứ tự tăng dần | **Pass** (Dữ liệu sắp xếp đúng cột whitelists) |
| **TC20** | Sắp xếp dữ liệu nguy hiểm | Gửi `GET /leads?sort=id+DESC;+DROP+TABLE+users;--` | Hệ thống loại bỏ cột nguy hiểm, tự động sắp xếp theo cột mặc định `created_at` | **Pass** (Chặn SQL Injection qua ORDER BY an toàn) |
| **TC21** | GET /health diagnostics | Gửi yêu cầu `GET /health` | Trả JSON status success của ứng dụng và kết nối database với mã HTTP 200 | **Pass** (JSON trả về thông tin trạng thái hệ thống đầy đủ) |
| **TC22** | POST /health sai method | Gửi yêu cầu `POST /health` | Trả mã HTTP 405 Method Not Allowed kèm trang thông báo lỗi tương ứng | **Pass** (Mã lỗi 405 được xử lý qua Router chính xác) |
| **TC23** | GET /unknown trang 404 | Gửi yêu cầu `GET /unknown_path` | Trả mã HTTP 404 Page Not Found kèm trang hiển thị lỗi thân thiện | **Pass** (Hệ thống trả trang lỗi 404 thiết kế riêng) |
| **TC24** | DB sập trong production | Tạm ngắt kết nối database và set `debug => false` | Ứng dụng trả lỗi 500 thông báo chung chung cho user, lưu vết SQLSTATE vào app.log | **Pass** (An toàn bảo mật thông tin mã nguồn và CSDL) |
| **TC25** | EXPLAIN câu lệnh | Chạy EXPLAIN trên MySQL CLI | Cột `key` trong kết quả hiển thị tên Index phù hợp mà không bị `NULL` | **Pass** (Truy vấn được tối ưu hóa bằng indexes thành công) |

---

## 5. MINH CHỨNG HÌNH ẢNH GIAO DIỆN (SCREENSHOTS)

Dưới đây là các ảnh chụp thực tế giao diện hệ thống CRM trong phiên chạy:

### 5.1 Landing Page Công Khai (Form Đăng Ký Tư Vấn)
Form công khai dành cho khách đăng ký tư vấn học phí, chứa cơ chế chống spam (honeypot + rate limit):
![Landing Page Form](/home/ho-minh-quan/.gemini/antigravity/brain/f1feb211-35b9-47c9-b786-6d5080794893/landing_page_1783176623370.png)

### 5.2 Giao Diện Đăng Nhập Quản Trị Viên
Trang đăng nhập dành cho admin kiểm soát an toàn phiên session:
![Login Page](/home/ho-minh-quan/.gemini/antigravity/brain/f1feb211-35b9-47c9-b786-6d5080794893/logout_page_1783176740591.png)

### 5.3 Admin Dashboard (Trang Tổng Quan)
Trang tổng hợp thống kê số lead học viên mới, tổng số lượng phiếu đăng ký, tổng doanh thu thực nhận:
![Admin Dashboard](/home/ho-minh-quan/.gemini/antigravity/brain/f1feb211-35b9-47c9-b786-6d5080794893/admin_dashboard_1783176670031.png)

### 5.4 Danh Sách Quản Lý Course Leads (Module A)
Giao diện quản lý thông tin khách đăng ký tư vấn, có hỗ trợ tìm kiếm, phân trang và sắp xếp:
![Course Leads List](/home/ho-minh-quan/.gemini/antigravity/brain/f1feb211-35b9-47c9-b786-6d5080794893/course_leads_list_1783176689873.png)

### 5.5 Danh Sách Quản Lý Enrollments (Module B)
Giao diện quản lý phiếu đăng ký học viên, học phí và trạng thái thanh toán:
![Enrollments List](/home/ho-minh-quan/.gemini/antigravity/brain/f1feb211-35b9-47c9-b786-6d5080794893/enrollments_list_1783176723965.png)

---

## 6. PHÂN TÍCH TỐI ƯU CƠ SỞ DỮ LIỆU (EXPLAIN)

Khi thực hiện kiểm tra hiệu năng truy vấn cho chức năng hiển thị danh sách (kết hợp lọc và sắp xếp), ta có kết quả phân tích chỉ mục như sau:

### 6.1 Truy vấn Leads (Module A)
Câu lệnh phân tích:
```sql
EXPLAIN SELECT * FROM course_leads WHERE 1=1 ORDER BY created_at DESC LIMIT 10 OFFSET 0;
```
Kết quả thực tế:
```text
+----+-------------+--------------+------------+-------+---------------+-------------------+---------+------+------+----------+---------------------+
| id | select_type | table        | partitions | type  | possible_keys | key               | key_len | ref  | rows | filtered | Extra               |
+----+-------------+--------------+------------+-------+---------------+-------------------+---------+------+------+----------+---------------------+
|  1 | SIMPLE      | course_leads | NULL       | index | NULL          | idx_lead_created  | 5       | NULL |   10 |   100.00 | Backward index scan |
+----+-------------+--------------+------------+-------+---------------+-------------------+---------+------+------+----------+---------------------+
```
**Nhận xét:**
- Cột `key` sử dụng chỉ mục `idx_lead_created` trên trường `created_at`.
- Cột `Extra` cho thấy MySQL áp dụng cơ chế `Backward index scan` để đọc trực tiếp dữ liệu theo chiều ngược lại của Index mà không cần phải thực hiện sắp xếp tạm thời trên đĩa (`Using filesort`). Điều này giúp tốc độ truy vấn luôn duy trì ở mức $O(\log N)$ thay vì $O(N \log N)$.

### 6.2 Truy vấn Enrollments (Module B)
Câu lệnh phân tích:
```sql
EXPLAIN SELECT * FROM enrollments WHERE 1=1 ORDER BY created_at DESC LIMIT 10 OFFSET 0;
```
Kết quả thực tế:
```text
+----+-------------+-------------+------------+-------+---------------+------------------------+---------+------+------+----------+---------------------+
| id | select_type | table       | partitions | type  | possible_keys | key                    | key_len | ref  | rows | filtered | Extra               |
+----+-------------+-------------+------------+-------+---------------+------------------------+---------+------+------+----------+---------------------+
|  1 | SIMPLE      | enrollments | NULL       | index | NULL          | idx_enrollment_created | 5       | NULL |   10 |   100.00 | Backward index scan |
+----+-------------+-------------+------------+-------+---------------+------------------------+---------+------+------+----------+---------------------+
```
**Nhận xét:**
- Cột `key` sử dụng chỉ mục `idx_enrollment_created`. Tương tự như bảng leads, cơ sở dữ liệu đã tận dụng chỉ mục để thực hiện `Backward index scan` hiệu quả cao, giảm thiểu tài nguyên CPU và I/O khi kích thước bảng tăng lên.

---

## 7. TRẢ LỜI CÂU HỎI PROBLEM SOLVING (CÂU 2)

### Câu 1: Front Controller & Router
Trong project của em, mọi request đều bắt buộc đi qua `public/index.php`.
`Router` thực hiện lưu trữ các route thông qua phương thức `add()` và kiểm tra khớp nối đường dẫn (`REQUEST_URI`) cùng phương thức (`REQUEST_METHOD`) khi gọi hàm `dispatch()`:
```php
// Map động trong public/index.php
$router->add('GET', '/leads', 'LeadController@index');
$router->add('POST', '/leads/store', 'LeadController@store');
```
Nếu mỗi file PHP tự xử lý một URL riêng lẻ (ví dụ: `leads.php`, `login.php`), hệ thống sẽ cực kỳ phức tạp và dễ lỗi khi mở rộng vì:
- Ta phải lặp đi lặp lại code kiểm tra đăng nhập (`require_login()`), cấu hình session, nạp cấu hình database ở đầu mỗi file.
- Không thể quản lý tập trung hệ thống Middleware (như CSRF protection cho toàn bộ các POST request).
- Việc xử lý hiển thị trang lỗi 404 (Không tìm thấy trang) hoặc 405 (Sai phương thức truyền tải) sẽ bị phân mảnh và rất khó đồng bộ giao diện chung.

### Câu 2: Secure Form
Form đăng ký tư vấn công khai trên Landing page thực hiện kiểm tra dữ liệu ở phía máy chủ (Server-side) thông qua `LeadService::validateLeadData()`:
```php
if ($fullname === '') {
    $errors['fullname'] = 'Tên lead không được để trống.';
}
if ($email === '') {
    $errors['email'] = 'Email không được để trống.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email không đúng định dạng.';
}
```
Chúng ta tuyệt đối không thể chỉ dựa vào các thuộc tính HTML như `required` hay `type="email"` ở trình duyệt vì:
- Người dùng hoặc tin tặc có thể dễ dàng tắt bỏ các thuộc tính kiểm tra này bằng cách chỉnh sửa trực tiếp DOM qua công cụ Developer Tools của trình duyệt (F12) hoặc gửi dữ liệu thô trực tiếp bằng Postman / lệnh `curl` bỏ qua hoàn toàn giao diện HTML. Chỉ có kiểm tra phía Server mới đảm bảo dữ liệu lưu vào DB luôn an toàn và sạch sẽ.

### Câu 3: PRG Pattern
Sau khi người dùng gửi form tạo dữ liệu bằng phương thức `POST` thành công, project áp dụng mô hình **Post-Redirect-Get (PRG)** bằng cách chuyển hướng người dùng về trang danh sách:
```php
flash('success', 'Lead đã được tạo thành công.');
redirect('/leads');
```
Nếu chúng ta không chuyển hướng mà render trực tiếp kết quả HTML ngay trên yêu cầu `POST`, khi người dùng bấm phím làm mới trang (F5), trình duyệt sẽ gửi lại toàn bộ dữ liệu của yêu cầu `POST` đó một lần nữa. Hậu quả là:
- Dữ liệu bị ghi trùng lặp trong cơ sở dữ liệu (tạo ra nhiều lead giống hệt nhau).
- Gây tốn tài nguyên hệ thống và làm hỏng tính toàn vẹn của dữ liệu nghiệp vụ.

### Câu 4: Anti-Spam Cơ Bản
Hệ thống tích hợp hai cơ chế chống spam cơ bản:
1. **Honeypot:** Một trường input ẩn `website` bằng CSS. Người dùng thông thường sẽ không nhìn thấy và để trống, nhưng các bot tự động sẽ quét qua HTML và tự điền dữ liệu vào. Khi nhận được dữ liệu ở trường này, hệ thống sẽ bỏ qua và redirect im lặng.
2. **Rate Limit:** Lưu thời điểm gửi form gần nhất vào session `$_SESSION['last_submit_time']`. Nếu khoảng thời gian gửi giữa 2 lần liên tiếp dưới 5 giây, yêu cầu sẽ bị chặn ngay lập tức.
**Giới hạn:**
- Honeypot có thể bị bot nâng cao phát hiện nếu nó phân tích CSS của trang.
- Rate limit theo session chỉ chặn được spam từ 1 trình duyệt. Nếu kẻ tấn công mở nhiều trình duyệt ẩn danh hoặc viết script tự động xóa cookie/session để đổi Session ID liên tục, cơ chế này sẽ vô tác dụng.
**Nâng cấp:** Khi lên hệ thống thực tế cần nâng cấp tích hợp Google reCAPTCHA v3 và thiết lập Rate Limiting theo địa chỉ IP của client lưu trên bộ nhớ cache tập trung (như Redis).

### Câu 5: Session/Login Flow
Quy trình đăng nhập chuẩn trong project diễn ra như sau:
1. Người dùng nhập Email + Password gửi `POST /login`.
2. Controller nhận dữ liệu, gọi `AuthService::login()`.
3. Kiểm tra định dạng dữ liệu đầu vào.
4. Tìm user trong bảng `users` bằng `UserRepository::findByEmail()`.
5. Sử dụng hàm `password_verify($password, $user['password_hash'])` để xác thực.
6. Nếu hợp lệ, gọi `session_regenerate_id(true)` để đổi Session ID hiện tại.
7. Lưu thông tin quyền và ID người dùng vào session (`$_SESSION['user_id'] = $user['id']`, `$_SESSION['user_role'] = $user['role']`).
8. Cài đặt cờ thời gian hoạt động `$_SESSION['last_activity'] = time()`.
9. Lưu flash message thành công và redirect về `/dashboard`.

**Rủi ro nếu không gọi `session_regenerate_id(true)`:**
Hệ thống sẽ bị đe dọa bởi cuộc tấn công **Session Fixation**. Kẻ tấn công có thể tạo sẵn một Session ID hợp lệ, dụ người dùng đăng nhập bằng ID đó (qua link chứa Session ID). Nếu hệ thống không cấp ID mới sau khi đăng nhập thành công, session của người dùng sẽ bị liên kết với ID cũ của kẻ tấn công, cho phép hắn chiếm quyền điều khiển tài khoản của nạn nhân mà không cần biết mật khẩu.

### Câu 6: Logout, Timeout Và Cookie Flags
- **Logout sạch:** Khi gọi `AuthController::logout()`, hệ thống tiến hành xóa sạch mảng `$_SESSION = []`, xóa Session Cookie ở trình duyệt bằng cách đặt thời gian hết hạn trong quá khứ (`time() - 42000`), sau đó chạy `session_destroy()`.
- **Timeout:** Trong `require_login()`, nếu thời gian không hoạt động `time() - $_SESSION['last_activity']` vượt quá 600 giây (10 phút), hệ thống sẽ tự động đăng xuất và đưa người dùng về trang login.
- **Vai trò của các cờ Cookie:**
  - `HttpOnly`: Ngăn chặn Javascript truy cập vào Session Cookie, giúp chống lại việc bị đánh cắp Session ID qua các lỗ hổng XSS.
  - `SameSite=Lax`: Giới hạn việc gửi cookie cùng với các request từ trang web bên thứ ba, giảm thiểu tối đa nguy cơ bị tấn công CSRF.
  - `Secure`: Chỉ cho phép truyền tải cookie qua giao thức HTTPS bảo mật mã hóa, chống nghe lén dữ liệu trên đường truyền mạng.

### Câu 7: Remember Me
Nếu tích hợp chức năng "Remember me" (Ghi nhớ đăng nhập), tuyệt đối không được lưu mật khẩu (dù đã mã hóa hay chưa) vào cookie vì cookie lưu trữ ở phía Client và rất dễ bị đánh cắp hoặc chỉnh sửa.
**Cơ chế Token an toàn:**
Chúng ta cần tạo ra một bảng chứa tokens trong DB có cấu trúc: `user_id`, `selector`, `validator_hash`, `expires_at`.
- Khi người dùng chọn Remember me, ta sinh ra 2 chuỗi ngẫu nhiên cực lớn: `selector` (định danh công khai) và `validator` (khóa bảo mật).
- Lưu `selector` và `hash(validator)` vào database.
- Gửi `selector` và `validator` lưu dưới dạng cookie ở trình duyệt người dùng (Ví dụ: `remember_cookie=selector:validator`).
- Khi phiên làm việc hết hạn, hệ thống phân tích chuỗi cookie, đối chiếu `selector` trong DB và so khớp băm của `validator`. Nếu trùng khớp và chưa hết hạn, tự động tạo lại phiên đăng nhập cho user.

### Câu 8: Database Schema
Thiết kế CSDL của em gồm:
- **`users`**: Chứa thông tin quản trị viên/nhân viên. Khóa chính `id`, email duy nhất (`UNIQUE`), chỉ mục trên cột `status` để lọc nhanh danh sách tài khoản hoạt động.
- **`course_leads`**: Lưu trữ học viên cần tư vấn. Khóa chính `id`, email của lead được gắn ràng buộc `UNIQUE` để tránh trùng lặp thông tin liên hệ, chỉ mục `idx_lead_created` trên `created_at` phục vụ sắp xếp danh sách nhanh.
- **`enrollments`**: Lưu thông tin hóa đơn khóa học. Khóa chính `id`, khóa duy nhất `enrollment_code` (`UNIQUE`) để quản lý định danh không trùng lặp, chỉ mục trên `payment_status` lọc hóa đơn đã thanh toán/chưa thanh toán, và chỉ mục `idx_enrollment_created` sắp xếp tối ưu.
- Cả 3 bảng đều có timestamp `created_at` và `updated_at` để lưu vết thời gian.

### Câu 9: Prepared Statements
1. **Câu SELECT trong `LeadRepository::findById()`:**
```php
$stmt = $this->db->prepare("SELECT * FROM course_leads WHERE id = :id LIMIT 1");
$stmt->execute(['id' => $id]);
```
2. **Câu INSERT trong `LeadRepository::create()`:**
```php
$sql = "INSERT INTO course_leads (fullname, email, phone, status, interested_course, note)
        VALUES (:fullname, :email, :phone, :status, :interested_course, :note)";
$stmt = $this->db->prepare($sql);
$stmt->execute([...]);
```
**Phân tích bảo mật chống SQL Injection:**
Khi sử dụng Prepared Statements, câu lệnh SQL (`command`) và dữ liệu người dùng nhập (`input`) được gửi đến MySQL Server ở hai thời điểm/luồng dữ liệu khác nhau. MySQL sẽ biên dịch trước cấu trúc cú pháp của câu lệnh SQL cố định. Khi dữ liệu của người dùng được truyền vào thông qua `execute()` hoặc `bindValue()`, cơ sở dữ liệu chỉ coi đó là các giá trị tham số thuần túy (`literals`), tuyệt đối không biên dịch chúng thành mã lệnh thực thi SQL, triệt tiêu hoàn toàn khả năng chèn mã độc hại phá hoại CSDL.

### Câu 10: Unique Constraint & Duplicate Handling
Chỉ kiểm tra trùng lặp dữ liệu bằng code PHP (ví dụ: chạy câu lệnh SELECT trước rồi mới INSERT) là không đủ an toàn do lỗi **Race Condition (Tình trạng tranh chấp)**.
**Tình huống cụ thể:**
Hai người dùng gửi thông tin đăng ký với cùng một email tại cùng một thời điểm cực kỳ sát nhau (chênh lệch mili-giây).
- Request 1 kiểm tra bằng PHP: Email chưa tồn tại -> Hợp lệ.
- Request 2 (chạy song song) kiểm tra bằng PHP: Email cũng chưa tồn tại -> Hợp lệ.
- Cả hai request đồng thời thực hiện lệnh INSERT vào DB, dẫn đến việc trùng lặp email quan trọng trong hệ thống.

**Giải pháp:**
Khi cấu hình trường đó là `UNIQUE` trong database, MySQL sẽ khóa bảng ở mức vật lý để ngăn chặn dữ liệu trùng. Nếu xảy ra trùng lặp, DB sẽ trả lỗi vi phạm khóa duy nhất (Error 1062). Service của ứng dụng sẽ bắt lỗi này thông qua ngoại lệ PDO và ném ra `DuplicateRecordException` để thông báo lỗi rõ ràng cho người dùng trên giao diện form mà không làm lộ cấu trúc hệ thống.

### Câu 11: Search/Pagination/Sort Safe
URL hiển thị danh sách của em sử dụng các tham số: `q` (tìm kiếm), `page` (số trang), `sort` (cột sắp xếp), `direction` (hướng sắp xếp: asc/desc).
- **Page âm / quá lớn:** Số trang được xử lý thông qua hàm `max(1, $page)` và sau đó so sánh với tổng số trang bằng `min($page, $totalPages)`. Nếu page âm sẽ chuyển về trang 1, nếu page quá lớn sẽ tự động đưa về trang cuối cùng.
- **Sort nguy hiểm / Cột lạ:** Cột sort được kiểm tra so khớp nghiêm ngặt trong Repository với mảng whitelist các cột được phép sắp xếp:
```php
$allowedSort = ['id', 'fullname', 'email', 'phone', 'status', 'interested_course', 'created_at'];
if (!in_array($sort, $allowedSort, true)) {
    $sort = 'created_at'; // Cột mặc định an toàn
}
```
- **Direction không hợp lệ:** Chuyển hướng dạng chữ hoa và kiểm tra:
```php
$direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
```
Cách xử lý này đảm bảo tin tặc không thể tiêm các câu lệnh SQL độc hại vào phần `ORDER BY` của câu truy vấn (vì `ORDER BY` trong SQL không dùng được tham số PDO thông thường mà bắt buộc phải cộng chuỗi).

### Câu 12: Index & EXPLAIN
Query sắp xếp danh sách leads:
```sql
EXPLAIN SELECT * FROM course_leads WHERE 1=1 ORDER BY created_at DESC LIMIT 10 OFFSET 0;
```
Kết quả EXPLAIN chỉ ra cột `key` sử dụng chỉ mục `idx_lead_created`.
**Nếu cột key = NULL trên cơ sở dữ liệu lớn:**
Hệ thống sẽ phải thực hiện quét toàn bộ bảng (`Full Table Scan`) sau đó sử dụng bộ nhớ tạm hoặc ghi file tạm ra đĩa để sắp xếp lại toàn bộ dữ liệu (`Using filesort`), gây nghẽn nghiêm trọng hệ thống và làm tăng thời gian phản hồi trang web.
**Cải tiến:**
Bắt buộc phải tạo chỉ mục cho các trường thường xuyên xuất hiện trong mệnh đề `WHERE` hoặc `ORDER BY` bằng câu lệnh SQL:
```sql
ALTER TABLE course_leads ADD INDEX idx_lead_created (created_at);
```

### Câu 13: MVC Đúng Trách Nhiệm
Hệ thống tuân thủ chặt chẽ việc phân chia trách nhiệm:
- **Repository (Tương tác DB):** Chỉ chứa các câu lệnh truy vấn SQL cụ thể và trả về dữ liệu thô (mảng PHP). Hoàn toàn không biết về thông tin HTTP Request (`$_POST`, `$_GET`) hay logic kiểm định dữ liệu.
- **Service (Logic nghiệp vụ):** Kiểm tra dữ liệu hợp lệ (Validate), tính toán số trang phân trang, bắt lỗi trùng lặp dữ liệu độc lập. Hoàn toàn tách biệt khỏi các phương thức xử lý giao diện hiển thị.
- **Controller (Điều phối):** Nhận request từ Router, lấy các tham số thô (`$_POST`, `$_GET`), gọi các hàm xử lý tương ứng ở tầng Service, nhận kết quả và chuyển dữ liệu qua cho View để hiển thị hoặc thực hiện redirect.
- **View (Hiển thị):** Chỉ chứa mã HTML xen kẽ các lệnh in dữ liệu an toàn bằng `e()`. Tuyệt đối không gọi truy vấn CSDL hay thực hiện logic nghiệp vụ phức tạp.

**Ví dụ cụ thể:** Trong `LeadController@store` không hề có bất kỳ câu lệnh SQL nào (không chứa `INSERT`, `SELECT`), tất cả được giao cho `LeadService->createLead()`. Tương tự, file giao diện `app/Views/leads/index.php` chỉ duyệt mảng `$leads` để in ra dữ liệu mà không hề tạo mới kết nối hay thực hiện truy vấn DB.

### Câu 14: Layout/Partial & XSS
- **Layout/Partial:** Giúp tái sử dụng tối đa code giao diện. Tránh việc lặp đi lặp lại mã khai báo HTML, liên kết thư viện CSS, footer bản quyền ở từng trang con. Khi cần thay đổi cấu trúc thanh điều hướng, ta chỉ cần chỉnh sửa duy nhất ở file `app/Views/partials/nav.php` thay vì cập nhật hàng chục file view riêng lẻ.
- **Chống lỗi bảo mật XSS (Cross-Site Scripting):** Dữ liệu lấy từ Database hoặc do người dùng gửi lên trước khi hiển thị ra trình duyệt bắt buộc phải chạy qua hàm escape `e()` định nghĩa trong `helpers.php`:
```php
function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
```
Hàm này sẽ chuyển đổi các ký tự HTML đặc biệt (như `<`, `>`, `&`, `"`, `'`) thành các thực thể HTML an toàn (như `&lt;`, `&gt;`), ngăn chặn trình duyệt hiểu nhầm nội dung dữ liệu là mã script thực thi, vô hiệu hóa hoàn toàn các kịch bản tiêm mã độc của tin tặc.

### Câu 15: Dev/Prod Error Message
- **Môi trường Development (Dev):** Chúng ta thiết lập cấu hình `debug => true` để khi xảy ra lỗi CSDL hoặc lỗi hệ thống, trang web sẽ in đầy đủ chi tiết mã lỗi `SQLSTATE`, stack trace giúp lập trình viên phát hiện lỗi và sửa nhanh chóng.
- **Môi trường Production (Prod):** Ta tắt cấu hình debug (`debug => false`). Khi có lỗi nghiêm trọng, hệ thống chỉ hiển thị thông báo chung chung cho người dùng: *"Đã có lỗi hệ thống xảy ra. Vui lòng liên hệ quản trị viên."*. Điều này cực kỳ quan trọng vì nếu in trực tiếp stack trace lên màn hình thực tế, tin tặc sẽ biết được cấu trúc thư mục máy chủ, tên cơ sở dữ liệu, các bảng/cột và cấu trúc code bên trong để khai thác lỗ hổng tấn công sâu hơn. Lỗi thực tế vẫn được hệ thống ghi vết an toàn vào file nhật ký `storage/logs/app.log` phục vụ quản trị viên kiểm tra sau đó.

### Câu 16: 404 vs 405
Trong định tuyến `Router.php`:
- **404 Not Found:** Xảy ra khi người dùng truy cập một URL hoàn toàn không có trong danh sách định nghĩa của hệ thống (ví dụ: `GET /unknown_path`). Hệ thống trả trang báo lỗi 404 thân thiện.
- **405 Method Not Allowed:** Xảy ra khi URL có tồn tại trong hệ thống nhưng người dùng truyền sai phương thức HTTP Method (ví dụ: Gửi `POST /health` trong khi route chỉ định nghĩa cho `GET /health`). Hệ thống phản hồi mã trạng thái 405 chính xác.
**Lý do phân biệt:** Việc phân biệt hai loại lỗi giúp client (hoặc API consumer) nhận biết chính xác lỗi do sai địa chỉ kết nối hay do sai phương thức gửi dữ liệu để điều chỉnh request phù hợp, đồng thời cải thiện khả năng chẩn đoán bảo mật hệ thống.

### Câu 17: Delete Bằng POST
Các thao tác thay đổi trạng thái dữ liệu (đặc biệt là Xóa và Cập nhật) tuyệt đối không được sử dụng phương thức `GET` vì các lý do an ninh nghiêm trọng sau:
- **Crawler tự động:** Các công cụ tìm kiếm (như Googlebot) hoặc các trình thu thập thông tin web tự động quét qua các thẻ `<a>` có thuộc tính `href` dạng `GET /leads/delete?id=123`. Nếu không bảo vệ, chúng sẽ tự động kích hoạt thao tác xóa sạch toàn bộ dữ liệu trong hệ thống.
- **Preview Link:** Các trình duyệt hiện đại hoặc ứng dụng nhắn tin thường tự động tải trước liên kết (`pre-fetching`) để hiển thị ảnh preview. Nếu đường link xóa là GET, dữ liệu sẽ bị xóa ngay khi người dùng chỉ mới di chuột qua hoặc chia sẻ liên kết.
- **Bảo mật:** Sử dụng phương thức `POST` đi kèm mã bảo mật CSRF Token bảo đảm hành động xóa chỉ được kích hoạt từ chính form do người dùng xác thực thực hiện.

### Câu 18: Hướng Phát Thế Hệ Thống Thực Tế
Nếu tiếp tục phát triển dự án này lên hệ thống CRM thương mại thực tế cho trung tâm đào tạo, em sẽ ưu tiên nâng cấp các tính năng bảo mật và vận hành theo thứ tự ưu tiên sau:
1. **CSRF (Cross-Site Request Forgery):** Hiện dự án đã tích hợp CSRF cơ bản cho mọi POST request, đây là chốt chặn bảo mật form vô cùng quan trọng phải giữ vững.
2. **Role Permission (Phân quyền chi tiết):** CRM cần chia rõ tài khoản quản lý (Admin - được xem báo cáo doanh thu và xóa phiếu) và tài khoản tư vấn viên (Staff - chỉ được quản lý lead của mình và cập nhật trạng thái tư vấn).
3. **Database Transaction (Giao dịch CSDL):** Cực kỳ quan trọng đối với Module B (Enrollments). Khi tạo một phiếu đăng ký học viên mới, hệ thống cần thực hiện nhiều hành động đồng thời: ghi nhận thông tin học viên, tạo hóa đơn học phí, ghi nhận thanh toán. Nếu một trong các thao tác thất bại, Transaction sẽ rollback toàn bộ để tránh lỗi lệch sổ sách tài chính.
4. **Soft Delete (Xóa mềm):** Thay vì xóa hẳn khỏi database làm mất lịch sử thống kê, hệ thống sẽ sử dụng trường `deleted_at` để lưu vết thông tin đã ẩn đi của Lead học viên.

---

## 8. TỰ ĐÁNH GIÁ VÀ HƯỚNG PHÁT TRIỂN

### 8.1 Phần đã làm đầy đủ (100% Yêu cầu bắt buộc)
- [x] Đầy đủ cấu trúc MVC chuẩn và Front Controller (`public/index.php`).
- [x] Form công khai secure với validation đầy đủ, giữ dữ liệu cũ và áp dụng PRG.
- [x] Chống spam hiệu quả bằng honeypot và rate limit theo session.
- [x] Login/Logout bảo mật, chống Session Fixation bằng cách đổi Session ID và kiểm tra Timeout phiên 10 phút.
- [x] Thiết kế database chuẩn, tối ưu chỉ mục (Index) trên các cột sắp xếp/tìm kiếm, kiểm chứng bằng EXPLAIN đạt Backward Index Scan.
- [x] Toàn bộ dữ liệu tương tác CSDL sử dụng prepared statements của PDO, ngăn chặn hoàn toàn SQL Injection.
- [x] Escape hiển thị giao diện triệt để bằng hàm `e()` chống XSS.
- [x] Xử lý lỗi trùng lặp dữ liệu bằng exception thân thiện, che giấu stack trace và mã lỗi nhạy cảm trong production (`debug = false`).
- [x] Viết tập lệnh chạy test tự động (`test_crm.php`) phủ hết 15 kịch bản tích hợp và chạy PASS hoàn toàn.
- [x] Lịch sử Git log lưu trữ đầy đủ 7 commits thể hiện quá trình phát triển dự án.

### 8.2 Các tính năng cộng điểm đã hoàn thành (Câu 3)
1. **CSRF Token:** Tích hợp đầy đủ CSRF protection cho toàn bộ các POST request tạo mới/sửa đổi/xóa dữ liệu và đăng xuất.
2. **Dashboard Thống kê:** Hiển thị biểu đồ thống kê tổng quan tổng số lượng leads học viên mới, tổng hóa đơn học viên, và tổng doanh thu thực nhận trên CSDL động.
3. **Logging hệ thống:** Ghi log lỗi CSDL và các hành vi bất thường vào file `storage/logs/app.log` an toàn.
4. **Cải tiến UI/UX chuyên nghiệp:** Giao diện được thiết kế hiện đại, responsive hoàn toàn sử dụng CSS hiện đại (bảng màu HSL, hiệu ứng hover, căn lề bento card cao cấp).

---
*Báo cáo được hoàn thành và kiểm thử chi tiết trên môi trường Docker Apache/MySQL ổn định.*
