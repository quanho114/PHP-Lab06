# CÂU HỎI VÀ ĐÁP ÁN BÀI TẬP LỚN - CÂU 2: PROBLEM SOLVING
## ĐỀ TÀI: TRAINING CENTER CRM (SECURE MVC PORTAL)

**Họ và tên:** [Họ Tên Sinh Viên]  
**MSSV:** [MSSV]  
**Môn học:** Lập trình Web với PHP  

---

### Câu 1: Front Controller & Router
**Câu hỏi:** Trong project của em, mọi request có đi qua `public/index.php` không? Router đang map METHOD + PATH -> Controller@Action như thế nào? Vì sao nếu mỗi file PHP tự xử lý một URL riêng thì project sẽ rối khi thêm auth, middleware, 404/405?

**Em xin trả lời:**
1. **Mọi request đều đi qua `public/index.php`:** Dạ có ạ. Trong cấu hình file `public/.htaccess` của project, em đã thiết kế luật Rewrite để điều hướng toàn bộ mọi truy cập không trùng với tệp tin vật lý thực tế về tệp tin duy nhất là `public/index.php`:
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [L,QSA]
   ```
2. **Cách Router map:** Trong file `app/Core/Router.php`, lớp `Router` định nghĩa một mảng `$routes` hai chiều. Khi gọi hàm `$router->add($method, $route, $handler)`, router sẽ lưu đường dẫn:
   ```php
   $this->routes[$method][$route] = $handler;
   ```
   Khi chạy ứng dụng, Front Controller gọi `$router->dispatch($_SERVER['REQUEST_METHOD'], $path)`. Router sẽ quét qua bảng phương thức HTTP tương ứng, sử dụng Regex hoặc so khớp chuỗi tuyệt đối để tìm `$handler` (dạng `LeadController@index`), tách lớp controller và tên hàm để khởi tạo qua cơ chế `new $className()` và gọi hàm bằng `$controller->$action()`.
3. **Tại sao tự xử lý từng file PHP sẽ bị rối:**
   - Nếu mỗi chức năng là một file như `list_leads.php`, `delete_lead.php`, `login.php`... thì ở đầu mỗi file em đều phải viết lại code khởi tạo CSDL (`PDO`), cấu hình session (`session_start()`), và gọi kiểm tra đăng nhập (`require_login()`). Điều này vi phạm nghiêm trọng nguyên tắc DRY (Don't Repeat Yourself).
   - Khi muốn thêm cơ chế kiểm tra bảo mật (như kiểm tra CSRF Token cho tất cả request POST), em sẽ phải sửa đổi thủ công từng file một, rất dễ bỏ sót tạo ra lỗ hổng bảo mật.
   - Việc hiển thị trang lỗi 404 (Khi không có trang nào trùng khớp) hoặc lỗi 405 (Đúng URL nhưng gửi sai phương thức HTTP) sẽ trở nên chắp vá vì không có một bộ điều phối trung tâm để nắm bắt và hiển thị giao diện lỗi đồng bộ.

---

### Câu 2: Secure Form
**Câu hỏi:** Form công khai hoặc form tạo lead của em kiểm tra dữ liệu server-side như thế nào? Vì sao không thể chỉ dựa vào required/type=email trên HTML?

**Em xin trả lời:**
1. **Kiểm tra dữ liệu server-side:** Trong project của em, khi nhận yêu cầu thêm Lead từ form công khai, Controller chuyển tiếp dữ liệu đến lớp nghiệp vụ `LeadService::createLead()`. Tại đây, hàm `validateLeadData()` được kích hoạt để kiểm tra tính hợp lệ của dữ liệu phía máy chủ (Server-side):
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
   Nếu phát hiện lỗi, hệ thống sẽ lưu các lỗi này cùng dữ liệu người dùng đã nhập trước đó vào Session, rồi redirect trở lại form kèm thông báo lỗi cạnh từng ô nhập.
2. **Vì sao không thể chỉ dựa vào kiểm tra ở HTML:**
   - Các thuộc tính bảo mật ở giao diện giao diện như `required`, `pattern` hay `type="email"` chỉ mang tính chất nâng cao trải nghiệm người dùng (UX) để cảnh báo sớm ở trình duyệt.
   - Tin tặc hoặc người dùng am hiểu công nghệ hoàn toàn có thể nhấn phím F12 mở Developer Tools để xóa các thuộc tính này ra khỏi DOM, hoặc sử dụng các công cụ như Postman, lệnh `curl` gửi trực tiếp dữ liệu thô (raw HTTP Request) bỏ qua hoàn toàn giao diện kiểm tra của trình duyệt. Nếu server không kiểm tra lại, dữ liệu rác, mã độc SQL Injection hoặc dữ liệu trống sẽ chui trực tiếp vào cơ sở dữ liệu làm hỏng hệ thống.

---

### Câu 3: PRG Pattern
**Câu hỏi:** Sau POST thành công, project redirect về route nào? Nếu render kết quả trực tiếp trên request POST thì user bấm F5 có thể gây hậu quả gì?

**Em xin trả lời:**
1. **Chuyển hướng sau khi POST thành công:** Trong đồ án của em, sau khi thêm Lead thành công ở đường dẫn `POST /leads/store`, controller sẽ lưu thông báo flash vào session và redirect người dùng về trang danh sách thông qua route `GET /leads`:
   ```php
   flash('success', 'Lead đã được tạo thành công.');
   redirect('/leads');
   ```
2. **Hậu quả nếu render kết quả trực tiếp trên request POST:**
   - Nếu sau khi thực hiện ghi dữ liệu vào CSDL, em render luôn giao diện HTML kết quả ngay trên request POST đó mà không chuyển hướng (Redirect), trình duyệt của người dùng vẫn đang lưu giữ trạng thái của yêu cầu POST trước đó.
   - Lúc này, nếu người dùng vô tình bấm phím F5 (Làm mới trang), trình duyệt sẽ hiện hộp thoại cảnh báo và gửi lại chính xác toàn bộ dữ liệu POST cũ lên máy chủ một lần nữa. Điều này dẫn đến việc MySQL chạy lại câu lệnh `INSERT`, gây ghi trùng lặp dữ liệu (tạo ra nhiều Lead học viên hoặc phiếu đăng ký giống hệt nhau), đồng thời làm tiêu hao tài nguyên CPU và băng thông mạng không đáng có.

---

### Câu 4: Anti-Spam Cơ Bản
**Câu hỏi:** Honeypot và rate limit trong bài của em đang chặn hành vi nào? Hai kỹ thuật này có giới hạn gì và khi nào cần nâng cấp lên CSRF/reCAPTCHA/rate limit theo IP?

**Em xin trả lời:**
1. **Chặn hành vi:**
   - **Honeypot:** Chặn các spam bot tự động dò quét các form trên Internet. Em tạo một trường input tên là `website` và ẩn đi bằng CSS (`display: none;`). Người dùng bình thường không nhìn thấy nên sẽ để trống, còn bot quét mã nguồn HTML sẽ cố tình điền thông tin vào. Nếu trường này nhận được dữ liệu, server sẽ tự động từ chối yêu cầu một cách im lặng.
   - **Rate limit:** Chặn hành vi gửi form liên tiếp dồn dập (ví dụ do click đúp hoặc spam tool). Em so sánh thời gian gửi hiện tại với thời gian gửi trước đó được lưu trong `$_SESSION['last_submit_time']`. Nếu khoảng cách nhỏ hơn 5 giây, yêu cầu sẽ bị từ chối ngay lập tức.
2. **Giới hạn của hai kỹ thuật:**
   - Honeypot vô tác dụng với các bot nâng cao có khả năng phân tích CSS (biết được trường nào có `display:none` để tránh điền) hoặc bot chạy bằng headless browser thực tế.
   - Rate limit bằng session chỉ có tác dụng trên một trình duyệt cụ thể. Kẻ tấn công chỉ cần viết script xóa cookie/Session ID liên tục để giả lập hàng ngàn người dùng mới là có thể vượt qua bộ lọc dễ dàng.
3. **Khi nào cần nâng cấp:** Khi đưa ứng dụng chạy thực tế trên môi trường Internet công cộng, bị đối thủ hoặc spam bot tấn công dồn dập làm nghẽn DB. Khi đó, em cần nâng cấp lên:
   - **Google reCAPTCHA v3:** Để phân tích hành vi người dùng một cách thông minh mà không gây phiền hà.
   - **Rate limit theo địa chỉ IP (hoặc IP + User Agent) kết hợp Redis:** Để lưu trữ lượt truy cập tập trung, chặn đứng spam từ tầng hạ tầng mạng trước khi request đi sâu vào xử lý của mã nguồn PHP.

---

### Câu 5: Session/Login Flow
**Câu hỏi:** Hãy mô tả flow login đúng trong project: validate input -> verify password -> session_regenerate_id(true) -> set session -> flash -> redirect. Nếu không regenerate sau login thì rủi ro gì?

**Em xin trả lời:**
1. **Quy trình đăng nhập đúng trong project:**
   - **Bước 1 (Validate input):** Khi người dùng gửi form `POST /login`, Controller kiểm tra xem Email và Password có để trống hay sai định dạng email không.
   - **Bước 2 (Verify password):** Controller gọi `AuthService::login()`. Service sử dụng `UserRepository` truy vấn tài khoản theo email từ CSDL. Nếu tìm thấy, hệ thống dùng hàm `password_verify($password, $user['password_hash'])` để đối chiếu mật khẩu băm bảo mật.
   - **Bước 3 (Regenerate ID):** Nếu mật khẩu đúng, hệ thống gọi hàm `session_regenerate_id(true)` ngay lập tức để hủy bỏ Session ID cũ và tạo ra một ID hoàn toàn mới ngẫu nhiên.
   - **Bước 4 (Set session):** Thiết lập thông tin định danh và quyền của người dùng vào session mới (`$_SESSION['user_id'] = $user['id']`, `$_SESSION['user_role'] = $user['role']`, và cập nhật thời gian hoạt động `$_SESSION['last_activity'] = time()`).
   - **Bước 5 (Flash):** Lưu thông báo flash chào mừng vào session: `flash('success', 'Đăng nhập thành công! Chào mừng trở lại.')`.
   - **Bước 6 (Redirect):** Chuyển hướng người dùng về trang quản trị nội bộ `/dashboard`.
2. **Rủi ro nếu không gọi `session_regenerate_id(true)`:**
   Hệ thống sẽ đứng trước nguy cơ bị tấn công **Session Fixation (Cố định phiên làm việc)**. Kẻ tấn công có thể tạo trước một Session ID hợp lệ trên trang web (bằng cách truy cập vào trang login và lấy Session ID từ cookie), sau đó gửi đường link chứa Session ID đó cho nạn nhân. Nếu nạn nhân nhấn vào link và đăng nhập thành công mà hệ thống không đổi Session ID mới, phiên đăng nhập của nạn nhân sẽ bị liên kết trực tiếp với Session ID mà kẻ tấn công đang giữ. Kẻ tấn công có thể dùng chính Session ID đó để giả mạo nạn nhân và truy cập toàn bộ hệ thống quản trị mà không cần biết thông tin tài khoản.

---

### Câu 6: Logout, Timeout và Cookie Flags
**Câu hỏi:** Logout sạch trong project xóa những gì? Timeout xử lý thế nào? HttpOnly, SameSite, Secure giúp giảm rủi ro gì?

**Em xin trả lời:**
1. **Logout sạch:** Trong `AuthController::logout()`, em thực hiện xóa sạch hoàn toàn dấu vết phiên làm việc:
   ```php
   $_SESSION = []; // Xóa tất cả các biến lưu trong session
   if (ini_get("session.use_cookies")) {
       $params = session_get_cookie_params();
       setcookie(session_name(), '', time() - 42000,
           $params["path"], $params["domain"],
           $params["secure"], $params["httponly"]
       ); // Xóa Session Cookie ở trình duyệt bằng cách đặt thời gian hết hạn trong quá khứ
   }
   session_destroy(); // Hủy toàn bộ dữ liệu session trên máy chủ
   ```
2. **Xử lý Timeout:** Trong hàm helper `require_login()`, hệ thống kiểm tra:
   ```php
   if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 600)) {
       // Quá 10 phút không hoạt động
       // Thực hiện logout sạch và redirect về /login kèm flash thông báo hết hạn phiên
   }
   $_SESSION['last_activity'] = time(); // Cập nhật thời điểm hoạt động mới nhất
   ```
3. **Các cờ Cookie giúp giảm rủi ro:**
   - **`HttpOnly`:** Ngăn chặn tuyệt đối mã Javascript chạy ở Client (như các mã độc XSS) đọc được cookie này thông qua câu lệnh `document.cookie`. Giúp giảm thiểu rủi ro bị đánh cắp phiên làm việc kể cả khi trang web dính lỗi XSS.
   - **`SameSite=Lax`:** Hạn chế trình duyệt tự động đính kèm Session Cookie khi thực hiện các yêu cầu cross-site (yêu cầu từ một trang web độc hại khác dẫn link đến trang của mình). Giúp chặn đứng nguy cơ tấn công **CSRF (Cross-Site Request Forgery)**.
   - **`Secure`:** Bắt buộc trình duyệt chỉ được truyền cookie này qua kết nối được mã hóa HTTPS. Giảm thiểu rủi ro bị kẻ tấn công nghe lén dữ liệu truyền tải trên mạng trung gian để đánh cắp Session ID (Man-in-the-middle attack).

---

### Câu 7: Remember Me
**Câu hỏi:** Nếu có checkbox remember me, vì sao không được lưu password vào cookie? Nếu muốn làm thật, em sẽ dùng token như thế nào?

**Em xin trả lời:**
1. **Vì sao không được lưu password vào cookie:**
   - Cookie được lưu trữ hoàn toàn dưới dạng tệp tin văn bản thuần ở máy của người dùng (Client-side).
   - Nếu lưu password (kể cả mật khẩu đã băm) vào cookie, kẻ tấn công có quyền truy cập máy tính vật lý hoặc kẻ tấn công sử dụng mã độc XSS có thể dễ dàng đọc được cookie này. Khi đã có mật khẩu băm, kẻ tấn công có thể dùng kỹ thuật Pass-the-Hash hoặc dùng máy tính cá nhân giải mã ngược mật khẩu để chiếm đoạt tài khoản vĩnh viễn.
2. **Cơ chế Token an toàn nếu làm thật:**
   Em sẽ áp dụng cơ chế **Token-Based Remember Me** với cấu trúc 3 thông tin gồm `selector` (định danh), `validator` (khóa xác thực) và bảng lưu trữ trong DB:
   - Tạo một bảng `user_tokens` trong CSDL với các cột: `id`, `user_id`, `selector` (UNIQUE), `token_hash` (băm của validator), và `expires_at`.
   - Khi người dùng chọn "Remember me" và đăng nhập thành công:
     - Tạo ngẫu nhiên 2 chuỗi ký tự cực dài: một chuỗi làm `selector` (ví dụ 12 ký tự) và một chuỗi làm `validator` (ví dụ 64 ký tự).
     - Lưu `selector` và `hash('sha256', validator)` vào bảng `user_tokens` trong CSDL kèm thời hạn hết hạn (ví dụ: 30 ngày).
     - Gửi một cookie xuống trình duyệt với định dạng kết hợp: `remember_me = selector:validator`.
   - Khi người dùng quay lại trang web sau khi session đã hết hạn:
     - Đọc cookie `remember_me`, tách thành 2 phần `selector` và `validator`.
     - Tìm kiếm dòng dữ liệu trong bảng `user_tokens` trùng khớp với `selector`.
     - Nếu tìm thấy và token chưa hết hạn, sử dụng hàm băm so sánh `hash_equals($token_in_db['token_hash'], hash('sha256', validator))`.
     - Nếu trùng khớp hoàn toàn, cấp Session ID mới đăng nhập tự động cho người dùng mà không cần nhập lại password, đồng thời xoay vòng (regenerate) token mới cho lần đăng nhập tiếp theo nhằm ngăn ngừa replay attack.

---

### Câu 8: Database Schema
**Câu hỏi:** Vì sao em thiết kế các bảng hiện tại? Hãy chỉ ra primary key, foreign key nếu có, unique constraint, index và timestamp trong schema của em.

**Em xin trả lời:**
1. **Lý do thiết kế các bảng hiện tại:**
   Để giải quyết bài toán quản lý đào tạo (Training Center CRM), em cần tối thiểu 3 bảng thực thể:
   - Bảng `users` để quản lý các nhân viên/tư vấn viên có quyền đăng nhập hệ thống nội bộ.
   - Bảng `course_leads` để lưu giữ thông tin khách hàng đăng ký tư vấn học viên tiềm năng (Module A). Đây là đầu phễu của trung tâm đào tạo.
   - Bảng `enrollments` đại diện cho các phiếu nhập học chính thức và học phí của học viên (Module B).
2. **Các thành phần trong Schema:**
   - **Primary Key (Khóa chính):** Cột `id` (INT, AUTO_INCREMENT) trong cả 3 bảng `users`, `course_leads`, và `enrollments` để định danh duy nhất cho từng hàng dữ liệu.
   - **Foreign Key (Khóa ngoại):** Trong bài toán thu gọn này, thông tin học viên đăng ký được liên kết logic qua trường `student_email` trong `enrollments` khớp với `email` của `course_leads`. (Nếu phát triển hệ thống lớn, em sẽ tạo khóa ngoại từ `enrollments.lead_id` tham chiếu đến `course_leads.id` để ràng buộc chặt chẽ).
   - **Unique Constraint (Ràng buộc duy nhất):**
     - Cột `email` trong bảng `users` để không cho phép hai nhân viên đăng ký trùng email.
     - Cột `email` trong bảng `course_leads` để ngăn chặn một khách hàng đăng ký tư vấn nhiều lần gây trùng lặp dữ liệu cần xử lý.
     - Cột `enrollment_code` trong bảng `enrollments` để bảo đảm mã phiếu đăng ký học viên (ví dụ: `ENR-2026-0001`) là độc bản toàn hệ thống.
   - **Index (Chỉ mục):**
     - `idx_lead_status` trên `course_leads(status)` và `idx_enrollment_status` trên `enrollments(payment_status)` để tăng tốc độ lọc tìm kiếm dữ liệu.
     - `idx_lead_created` trên `course_leads(created_at)` và `idx_enrollment_created` trên `enrollments(created_at)` để tăng tốc câu lệnh sắp xếp danh sách theo thời gian tạo mới nhất.
   - **Timestamp (Thời gian ghi nhận):** Cột `created_at` và `updated_at` (kiểu `TIMESTAMP`) trong cả 3 bảng để tự động lưu vết thời gian bản ghi được tạo và chỉnh sửa lần cuối cùng.

---

### Câu 9: Prepared Statements
**Câu hỏi:** Chọn 1 câu INSERT và 1 câu SELECT trong Repository của em. Phân tích SQL command và user input được tách riêng như thế nào để tránh SQL Injection.

**Em xin trả lời:**
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
   $stmt->execute([
       'fullname' => $data['fullname'],
       'email' => $data['email'],
       'phone' => $data['phone'] ?? null,
       'status' => $data['status'] ?? 'new',
       'interested_course' => $data['interested_course'] ?? null,
       'note' => $data['note'] ?? null
   ]);
   ```
3. **Phân tích cơ chế tránh SQL Injection:**
   - Khi em gọi hàm `$this->db->prepare($sql)`, câu lệnh SQL chứa các tham số giữ chỗ (`:id`, `:fullname`, `:email`...) được gửi trước tới MySQL Server. Cơ sở dữ liệu sẽ phân tích cú pháp (parse), tối ưu hóa cấu trúc truy vấn, và biên dịch sẵn câu lệnh đó độc lập với dữ liệu đầu vào. Cấu trúc câu lệnh lúc này đã cố định và không thể thay đổi.
   - Khi em gọi hàm `execute([...])`, các giá trị dữ liệu từ người dùng nhập mới được gửi lên. MySQL chỉ coi các giá trị này là tham số dữ liệu thô (literals) để lấp vào các tham số giữ chỗ tương ứng.
   - Kể cả khi người dùng cố tình nhập chuỗi mã độc hại (như `' OR '1'='1` hoặc `; DROP TABLE users;`), MySQL cũng không bao giờ biên dịch lại chuỗi đó thành mã lệnh thực thi, do đó triệt tiêu hoàn toàn khả năng bị tấn công SQL Injection.

---

### Câu 10: Unique Constraint & Duplicate Handling
**Câu hỏi:** Vì sao chỉ kiểm tra trùng bằng PHP là chưa đủ? Hãy mô tả tình huống 2 request cùng submit dữ liệu trùng và database unique constraint giúp gì.

**Em xin trả lời:**
1. **Vì sao chỉ kiểm tra trùng bằng PHP là chưa đủ:**
   Nếu chỉ dùng code PHP thực hiện câu lệnh `SELECT` để kiểm tra sự tồn tại của dữ liệu trước khi thực hiện `INSERT`, hệ thống sẽ gặp phải lỗi đồng thì hay lỗi **Race Condition (Tình trạng tranh chấp)**. PHP hoạt động theo cơ chế đa luồng hoặc đa tiến trình xử lý các request đồng thời, nên việc kiểm tra và ghi dữ liệu không phải là một hành động nguyên tử duy nhất (non-atomic operation).
2. **Mô tả tình huống 2 request submit trùng:**
   Giả sử có hai người dùng gửi yêu cầu đăng ký tư vấn học viên với cùng một email là `test@example.com` tại cùng một thời điểm chênh lệch nhau chỉ vài mili-giây:
   - **Luồng 1 (Request A):** Thực hiện kiểm tra trùng bằng PHP -> Gửi câu lệnh `SELECT COUNT(*) FROM course_leads WHERE email = 'test@example.com'`. Kết quả trả về `0` (Email chưa tồn tại).
   - **Luồng 2 (Request B - chạy song song):** Cũng thực hiện kiểm tra trùng bằng PHP -> Gửi câu lệnh `SELECT COUNT(*) FROM course_leads WHERE email = 'test@example.com'`. Lúc này Request A chưa kịp ghi dữ liệu xong, nên kết quả trả về của Request B cũng là `0` (Email chưa tồn tại).
   - **Luồng 1 (Request A):** Thấy hợp lệ, thực hiện câu lệnh `INSERT INTO course_leads ...`. Ghi dữ liệu thành công.
   - **Luồng 2 (Request B):** Cũng thấy hợp lệ từ bước kiểm tra trước, tiếp tục thực hiện câu lệnh `INSERT INTO course_leads ...`. Ghi dữ liệu thành công lần hai.
   - **Hậu quả:** Trong database xuất hiện 2 hàng dữ liệu có email trùng nhau, phá vỡ tính toàn vẹn thông tin.
3. **Database Unique Constraint giúp gì:**
   Khi ta cấu hình trường `email` là `UNIQUE` ở mức Database:
   - MySQL sẽ đảm bảo việc kiểm tra và ghi là một giao dịch nguyên tử được kiểm soát khóa vật lý (Locking).
   - Khi Request B thực hiện chèn dữ liệu sau Request A, MySQL sẽ phát hiện vi phạm ràng buộc duy nhất vật lý và từ chối ghi dữ liệu, ném ra mã lỗi SQLSTATE `23000` (Error Code 1062 - Duplicate entry).
   - Trong Repositories của em, lỗi này được bắt lại một cách an toàn và ném ra ngoại lệ `DuplicateRecordException` để thông báo lỗi thân thiện cho người dùng trên màn hình mà không làm sập hệ thống.

---

### Câu 11: Search/Pagination/Sort Safe
**Câu hỏi:** URL list của em dùng những tham số nào? Em xử lý page âm, page quá lớn, sort/direction không hợp lệ và sort nguy hiểm như thế nào?

**Em xin trả lời:**
1. **Các tham số dùng trong URL:** Trang danh sách Lead học viên (`/leads`) sử dụng các tham số truy vấn: `q` (từ khóa tìm kiếm), `status` (trạng thái lọc), `page` (số trang hiện tại), `sort` (cột sắp xếp), và `direction` (chiều sắp xếp: `asc` hoặc `desc`).
2. **Cách xử lý an toàn:**
   - **Page âm:** Trong `LeadService::getPaginatedLeads()`, số trang truyền vào được chuyển kiểu sang số nguyên và chuẩn hóa bằng hàm `max(1, (int)$page)`. Nếu người dùng truyền vào trang âm (ví dụ `page=-5`) thì giá trị tự động quy về trang `1`.
   - **Page quá lớn:** Hệ thống tính toán tổng số trang thực tế `$totalPages = ceil($totalItems / $perPage)`. Số trang yêu cầu được chuẩn hóa bằng hàm `min($page, $totalPages)`. Nếu người dùng truyền vào trang quá lớn (ví dụ `page=999999`) thì trang tự động nhảy về trang cuối cùng có dữ liệu.
   - **Sort/direction không hợp lệ & sort nguy hiểm:**
     Mệnh đề `ORDER BY` trong SQL không thể sử dụng cơ chế ràng buộc tham số truyền thống của PDO (`bindValue`), do đó nếu cộng chuỗi trực tiếp từ URL sẽ mở ra lỗ hổng SQL Injection cực kỳ nguy hiểm. Để triệt tiêu rủi ro này, em áp dụng cơ chế **Whitelist** ở Repository:
     ```php
     // Whitelist các cột được phép sắp xếp thực tế
     $allowedSort = ['id', 'fullname', 'email', 'phone', 'status', 'interested_course', 'created_at'];
     if (!in_array($sort, $allowedSort, true)) {
         $sort = 'created_at'; // Fallback cột mặc định an toàn nếu nhập cột lạ hoặc mã độc
     }
     
     // Chuẩn hóa chiều sắp xếp
     $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';
     ```
     Nhờ vậy, nếu tin tặc cố tình truyền mã độc (như `sort=id;DROP TABLE users;--`), tham số này không nằm trong danh sách whitelists nên sẽ bị gạt bỏ hoàn toàn và thay thế bằng `'created_at'`.

---

### Câu 12: Index & EXPLAIN
**Câu hỏi:** Đưa 1 query list/filter/sort trong project và kết quả EXPLAIN. Cột key có dùng index không? Nếu key=NULL trên bảng lớn, em sẽ cải tiến gì?

**Em xin trả lời:**
1. **Query phân tích:**
   ```sql
   EXPLAIN SELECT * FROM course_leads WHERE 1=1 ORDER BY created_at DESC LIMIT 10 OFFSET 0;
   ```
2. **Kết quả EXPLAIN thực tế:**
   - Cột `key` trong kết quả hiển thị: `idx_lead_created`.
   - Cột `Extra` hiển thị: `Backward index scan`.
   Điều này chứng tỏ MySQL đã sử dụng thành công chỉ mục `idx_lead_created` tạo trên cột `created_at` để truy xuất trực tiếp dữ liệu theo thứ tự giảm dần mà không cần phải thực hiện quét toàn bộ bảng và sắp xếp lại trong bộ nhớ tạm (`filesort`).
3. **Cải tiến nếu `key = NULL` trên bảng lớn:**
   - Khi `key = NULL`, MySQL sẽ phải thực hiện quét toàn bảng (`Full Table Scan`) rồi thực hiện thuật toán sắp xếp đĩa rất tốn CPU và I/O khi dữ liệu lên tới hàng trăm ngàn dòng.
   - **Giải pháp cải tiến của em:** Tiến hành phân tích các câu lệnh truy vấn thường dùng và thêm chỉ mục phù hợp.
     - Nếu chỉ có sắp xếp đơn giản: Thêm chỉ mục đơn trên cột sắp xếp:
       ```sql
       ALTER TABLE course_leads ADD INDEX idx_lead_created (created_at);
       ```
     - Nếu có lọc theo trạng thái kết hợp sắp xếp (ví dụ: `WHERE status = 'new' ORDER BY created_at DESC`), em sẽ tạo một **chỉ mục tổng hợp (composite index)** chứa cả hai trường theo đúng thứ tự lọc trước - sắp xếp sau:
       ```sql
       ALTER TABLE course_leads ADD INDEX idx_status_created (status, created_at);
       ```

---

### Câu 13: MVC Đúng Trách Nhiệm
**Câu hỏi:** Trong project của em, Controller, Service, Repository, View đang làm gì? Hãy chỉ ra một ví dụ cụ thể chứng minh Controller không viết SQL và View không query DB.

**Em xin trả lời:**
1. **Phân chia trách nhiệm trong project:**
   - **Controller (Điều phối):** Tiếp nhận yêu cầu HTTP từ Router, trích xuất dữ liệu đầu vào thô, gọi Service xử lý nghiệp vụ tương ứng và chuyển tiếp dữ liệu kết quả cho View hiển thị hoặc thực hiện Redirect.
   - **Service (Nghiệp vụ - Business Logic):** Thực hiện kiểm định dữ liệu (validate), áp dụng các quy tắc nghiệp vụ chuyên sâu (ví dụ: tự động tạo mã định danh, tính toán số trang), bắt lỗi và ném ngoại lệ nếu có.
   - **Repository (Tương tác CSDL):** Đóng gói toàn bộ các thao tác đọc ghi dữ liệu vật lý với MySQL bằng PDO.
   - **View (Giao diện):** Nhận biến dữ liệu từ Controller truyền qua và render HTML an toàn.
2. **Ví dụ chứng minh sự tách biệt:**
   Xem xét chức năng hiển thị danh sách Lead học viên:
   - Trong `LeadController::index()`, hoàn toàn không có một dòng code SQL nào. Controller chỉ lấy các biến trên URL thông qua `$_GET` và gọi dịch vụ:
     ```php
     $data = $this->leadService->getPaginatedLeads($_GET);
     $this->render('leads/index', $data);
     ```
   - Lệnh SQL thực tế nằm trọn vẹn trong `LeadRepository::findAll()` để thực thi câu lệnh prepared:
     ```php
     $stmt = $this->db->prepare("SELECT * FROM course_leads ...");
     ```
   - Ở tệp tin giao diện View `app/Views/leads/index.php`, code chỉ sử dụng vòng lặp foreach để in dữ liệu mảng học viên ra màn hình bằng lệnh `<?= e($lead['fullname']) ?>`. View hoàn toàn không chứa bất kỳ kết nối CSDL nào hay gọi hàm query trực tiếp nào từ MySQL.

---

### Câu 14: Layout/Partial & XSS
**Câu hỏi:** Vì sao nên dùng layout/partial thay vì lặp header/menu/footer ở nhiều view? Dữ liệu từ DB/user input được escape ở đâu để tránh XSS?

**Em xin trả lời:**
1. **Vì sao dùng layout/partial:**
   - Tránh trùng lặp mã nguồn (DRY). Thay vì phải sao chép hàng trăm dòng code khai báo HTML, import CSS, và thẻ Script footer sang tất cả các view, em chỉ cần khai báo một lần duy nhất tại file layout dùng chung `app/Views/layouts/main.php`.
   - Giúp việc bảo trì cực kỳ nhanh chóng. Khi cần thêm một menu mới vào thanh điều hướng, em chỉ cần chỉnh sửa một file duy nhất là `app/Views/partials/nav.php` thay vì phải vào từng view con để cập nhật thủ công.
2. **Dữ liệu được escape để tránh XSS ở đâu:**
   - Toàn bộ dữ liệu hiển thị lấy từ Database hoặc do người dùng nhập vào đều được escape trực tiếp **tại tầng hiển thị (View)** ngay khi in ra trình duyệt.
   - Em sử dụng hàm helper `e()` định nghĩa trong `helpers.php`:
     ```php
     function e(?string $value): string {
         return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
     }
     ```
   - Khi in dữ liệu ngoài view, em bắt buộc phải bọc biến trong hàm này: `<td><?= e($lead['fullname']) ?></td>`. Hàm này sẽ chuyển hóa các ký tự nhạy cảm như `<` thành `&lt;`, `>` thành `&gt;` giúp vô hiệu hóa hoàn toàn mã script độc hại trước khi trình duyệt biên dịch nó.

---

### Câu 15: Dev/Prod Error Message
**Câu hỏi:** Vì sao production không nên hiển thị $e->getMessage(), SQLSTATE hoặc stack trace? Project của em ghi log và hiển thị safe message như thế nào?

**Em xin trả lời:**
1. **Vì sao môi trường Production phải che giấu thông tin lỗi chi tiết:**
   Các thông tin như `$e->getMessage()`, `SQLSTATE` hay Stack trace chứa rất nhiều thông tin nhạy cảm của hệ thống: cấu trúc đường dẫn thư mục vật lý trên máy chủ (ví dụ `/var/www/html/...`), cấu trúc bảng cơ sở dữ liệu, tên các cột, tên hàm PHP và thậm chí cả các đoạn truy vấn chứa biến số. Nếu in trực tiếp thông tin này lên màn hình chạy thực tế, tin tặc sẽ dựa vào sơ đồ cấu trúc này để tìm ra điểm yếu và thực hiện các cuộc tấn công khai thác sâu hơn.
2. **Cách xử lý trong project của em:**
   Trong tệp cấu hình `config/app.php`, em khai báo biến debug:
   ```php
   return [
       'debug' => false, // Đặt thành false ở môi trường production thực tế
   ];
   ```
   Tại hàm Front Controller hoặc các điểm bắt exception tập trung (như trong View Error 500), hệ thống kiểm tra cấu hình này:
   - Nếu `debug => true` (môi trường Dev): Hệ thống in ra chi tiết lỗi giúp lập trình viên debug nhanh.
   - Nếu `debug => false` (môi trường Prod):
     - Hệ thống ghi lại toàn bộ dấu vết chi tiết lỗi, mã lỗi và stack trace vào tệp tin nhật ký nội bộ an toàn trên server là `/storage/logs/app.log` để quản trị viên có thể tải về phân tích sau.
     - Hiển thị lên trình duyệt một thông báo lỗi chung chung, an toàn (Safe Message): *"Đã có lỗi hệ thống xảy ra. Vui lòng liên hệ quản trị viên để được hỗ trợ."* để bảo vệ an toàn thông tin mã nguồn.

---

### Câu 16: 404 vs 405
**Câu hỏi:** Hãy đưa ví dụ route không tồn tại trả 404 và route tồn tại nhưng sai method trả 405 trong project của em. Vì sao cần phân biệt hai loại lỗi này?

**Em xin trả lời:**
1. **Ví dụ trong project:**
   - **Lỗi 404 (Not Found):** Khi người dùng gõ đường dẫn không hề có trong định nghĩa route, ví dụ `GET /lead-registrations-invalid`. Bộ định tuyến `Router.php` quét qua bảng route và không tìm thấy địa chỉ này, lập tức trả về mã trạng thái HTTP 404 cùng trang lỗi 404 tương ứng.
   - **Lỗi 405 (Method Not Allowed):** Khi truy cập vào route có tồn tại là `/health` (vốn chỉ định nghĩa cho phương thức `GET`), nhưng người dùng lại gửi yêu cầu bằng phương thức `POST` (`POST /health`). Router nhận diện được URL có tồn tại nhưng sai phương thức truyền tải dữ liệu, lập tức trả về mã trạng thái HTTP 405.
2. **Vì sao cần phân biệt:**
   - **Về mặt kỹ thuật và chuẩn RESTful API:** Phân biệt rõ hai lỗi giúp lập trình viên phía client hoặc các ứng dụng tích hợp (API Consumer) biết chính xác nguyên nhân lỗi là do gõ sai URL địa chỉ (404) hay do gửi sai phương thức giao tiếp (405) để điều chỉnh mã nguồn phía Client cho đúng.
   - **Về mặt bảo mật:** Lỗi 405 giúp phát hiện sớm các hành vi cố tình dò quét cổng hoặc gửi dữ liệu bừa bãi bằng các tool tự động của tin tặc.

---

### Câu 17: Delete Bằng POST
**Câu hỏi:** Vì sao delete/update không nên dùng GET? Phân tích rủi ro nếu crawler, preview link hoặc user click nhầm vào URL delete.

**Em xin trả lời:**
1. **Vì sao không dùng GET cho Delete/Update:**
   Theo đặc tả HTTP, phương thức `GET` được định nghĩa là phương thức **Safe** và **Idempotent** (chỉ dùng để đọc thông tin và không được phép làm thay đổi trạng thái dữ liệu trên hệ thống). Các thao tác làm thay đổi dữ liệu như thêm, sửa, xóa bắt buộc phải sử dụng các phương thức có tính chất sửa đổi trạng thái như `POST`, `PUT`, `DELETE`.
2. **Phân tích rủi ro nếu dùng GET:**
   - **Crawler (Trình thu thập thông tin tự động):** Các công cụ tìm kiếm như Googlebot, Bingbot sẽ tự động đi theo tất cả các liên kết `<a>` có trong mã nguồn HTML. Nếu đường link xóa Lead là `GET /leads/delete?id=5`, khi bot quét qua danh sách, nó sẽ kích hoạt yêu cầu GET này và vô tình xóa sạch toàn bộ cơ sở dữ liệu của hệ thống.
   - **Preview Link (Tải trước liên kết):** Nhiều ứng dụng chat (như Slack, Skype) hoặc trình duyệt hiện đại có tính năng tự động tải trước nội dung liên kết để hiển thị thông tin xem trước (pre-fetching). Nếu gửi link qua chat, hệ thống sẽ thực thi lệnh xóa ngay khi liên kết vừa được gửi mà người dùng chưa hề nhấn vào.
   - **Click nhầm:** Người dùng có thể click nhầm vào link hoặc nhập URL trực tiếp trên trình duyệt, hành động xóa xảy ra lập tức mà không có sự kiểm soát từ form bảo mật chống CSRF Token đi kèm.

---

### Câu 18: Hướng Phát Triển Thật
**Câu hỏi:** Nếu phát triển project thành hệ thống thật, em sẽ ưu tiên nâng cấp gì trước: CSRF, role permission, soft delete, transaction, logging, audit trail, API, tests, hay Docker? Giải thích theo bài toán của em.

**Em xin trả lời:**
Đối với bài toán **Training Center CRM (Quản lý đào tạo & học phí)**, nếu đưa hệ thống này chạy thực tế thương mại, em sẽ ưu tiên nâng cấp các tính năng theo thứ tự sau:

1. **Role Permission (Phân quyền chi tiết - RBAC) [Ưu tiên 1]:**
   Hệ thống quản lý đào tạo có sự tham gia của nhiều phòng ban: bộ phận Marketing (chỉ được xem và chăm sóc leads), bộ phận Kế toán (chỉ được xem và cập nhật trạng thái đóng tiền của phiếu học phí), và Ban giám đốc (xem báo cáo doanh thu tổng quan). Việc phân quyền giúp bảo vệ dữ liệu khách hàng không bị rò rỉ và nhân viên không can thiệp sai chuyên môn.
2. **Database Transaction (Giao dịch CSDL) [Ưu tiên 2]:**
   Đặc thù quản lý học phí (Enrollments) liên quan trực tiếp đến dòng tiền thực tế. Khi học viên đóng tiền và nhập học, hệ thống phải thực hiện đồng thời nhiều thao tác: chuyển trạng thái lead sang "enrolled", tạo phiếu thu ghi nhận doanh thu, và tạo tài khoản học tập cho học viên. Sử dụng Database Transactions bảo đảm nếu một bước bị lỗi, toàn bộ tiến trình sẽ rollback để tránh thất thoát tiền bạc hoặc lỗi lệch sổ sách kế toán.
3. **CSRF (Cross-Site Request Forgery) [Ưu tiên 3]:**
   Mặc dù dự án hiện tại đã tích hợp sẵn CSRF token cơ bản cho mọi POST request, nhưng khi đưa lên hệ thống thật, việc bảo vệ các form nghiệp vụ tài chính và leads tránh bị gửi lén từ các trang web giả mạo là vô cùng sống còn.
4. **Soft Delete (Xóa mềm):**
   Trong CRM, thông tin Lead học viên và hóa đơn là tài sản dữ liệu quý giá của trung tâm dùng để phân tích hiệu quả Marketing theo năm. Việc xóa cứng (Hard Delete) sẽ làm hỏng toàn bộ dữ liệu lịch sử thống kê doanh thu. Sử dụng xóa mềm (`deleted_at`) giúp ẩn dữ liệu khỏi màn hình làm việc của nhân viên nhưng vẫn lưu trữ lịch sử thống kê an toàn.
5. **Logging & Audit Trail:**
   Để ghi vết xem nhân viên nào đã sửa học phí hoặc xóa thông tin học viên, phục vụ mục đích hậu kiểm và phát hiện gian lận nội bộ.
