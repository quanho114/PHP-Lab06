<div class="db-wrapper">
    <!-- Welcome Header Panel -->
    <div class="db-welcome-panel">
        <div class="db-welcome-glow"></div>
        <div>
            <span style="display: inline-block; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 1.5px; color: var(--accent); margin-bottom: 8px;">Hệ thống CRM trung tâm</span>
            <h1 class="db-welcome-title">Chào mừng quay trở lại, <?= e($_SESSION['user_name']) ?></h1>
            <p class="db-welcome-subtitle">Hệ thống quản lý thông tin đào tạo bảo mật. Dưới đây là kết quả thống kê hoạt động tính đến thời điểm hiện tại.</p>
        </div>
        <div class="db-welcome-time">
            <span class="db-time-label">Giờ hệ thống</span>
            <div class="db-time-val"><?= date('d/m/Y H:i') ?></div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="db-stats-grid">
        <!-- Lead Count Card -->
        <div class="db-stat-card">
            <div class="db-stat-info">
                <span class="db-stat-label">Course Leads</span>
                <span class="db-stat-val"><?= e((string)$totalLeads) ?></span>
                <span class="db-stat-trend success">
                    <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-9 9-4-4-6 6" />
                    </svg>
                    +12% so với tháng trước
                </span>
            </div>
            <div class="db-stat-icon-wrapper" style="background-color: var(--primary-glow); color: var(--primary);">
                <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
        </div>

        <!-- Enrollment Count Card -->
        <div class="db-stat-card">
            <div class="db-stat-info">
                <span class="db-stat-label">Enrollments</span>
                <span class="db-stat-val"><?= e((string)$totalEnrollments) ?></span>
                <span class="db-stat-trend success">
                    <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-9 9-4-4-6 6" />
                    </svg>
                    +8.5% so với tháng trước
                </span>
            </div>
            <div class="db-stat-icon-wrapper" style="background-color: var(--accent-glow); color: var(--accent);">
                <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            </div>
        </div>

        <!-- Revenue Card -->
        <div class="db-stat-card">
            <div class="db-stat-info">
                <span class="db-stat-label">Học phí thu được</span>
                <span class="db-stat-val" style="color: var(--success-text);"><?= number_format($revenue, 0, ',', '.') ?> <span style="font-size: 1.2rem; font-weight: 600;">VNĐ</span></span>
                <span class="db-stat-trend success">
                    <svg style="width: 14px; height: 14px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-9 9-4-4-6 6" />
                    </svg>
                    Đạt 95.2% mục tiêu
                </span>
            </div>
            <div class="db-stat-icon-wrapper" style="background-color: var(--success-bg); color: var(--success-text);">
                <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Bento Section -->
    <div class="db-bento-grid">
        <!-- Quick Actions Card -->
        <div class="db-bento-card">
            <h3>Truy cập nhanh</h3>
            <p class="db-bento-card-subtitle">Vui lòng chọn các tính năng quản lý ở dưới hoặc sử dụng menu phía trên.</p>
            <div class="db-action-grid">
                <a href="/leads" class="db-action-item">
                    <div class="db-action-title">
                        <span>👥 Danh sách Leads</span>
                        <span style="font-size: 0.8rem; color: var(--text-muted); margin-left: auto;">&rarr;</span>
                    </div>
                    <span class="db-action-desc">Quản lý danh sách học viên tiềm năng, lọc và tìm kiếm nhanh chóng.</span>
                </a>
                
                <a href="/enrollments" class="db-action-item">
                    <div class="db-action-title">
                        <span>📝 Phiếu Đăng Ký</span>
                        <span style="font-size: 0.8rem; color: var(--text-muted); margin-left: auto;">&rarr;</span>
                    </div>
                    <span class="db-action-desc">Xem danh sách đăng ký học tập, doanh thu học phí và trạng thái thanh toán.</span>
                </a>

                <a href="/leads/create" class="db-action-item">
                    <div class="db-action-title">
                        <span>➕ Thêm Lead Mới</span>
                        <span style="font-size: 0.8rem; color: var(--text-muted); margin-left: auto;">&rarr;</span>
                    </div>
                    <span class="db-action-desc">Tạo thông tin học viên tiềm năng mới để đội ngũ chăm sóc và tư vấn.</span>
                </a>

                <a href="/enrollments/create" class="db-action-item">
                    <div class="db-action-title">
                        <span>➕ Thêm Phiếu Đăng Ký</span>
                        <span style="font-size: 0.8rem; color: var(--text-muted); margin-left: auto;">&rarr;</span>
                    </div>
                    <span class="db-action-desc">Ghi nhận học viên đăng ký lớp học mới và theo dõi hóa đơn học phí.</span>
                </a>
            </div>
        </div>

        <!-- Security Status Card -->
        <div class="db-bento-card" style="border-color: var(--success-border); background: linear-gradient(135deg, var(--bg-surface) 0%, rgba(237, 245, 241, 0.2) 100%);">
            <h3 style="color: var(--success-text); display: flex; align-items: center; gap: 8px;">
                <svg style="width: 22px; height: 22px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Trạng thái Bảo mật
            </h3>
            <p class="db-bento-card-subtitle">Hệ thống của bạn đang được bảo mật với các lớp tiêu chuẩn an toàn cao.</p>
            
            <ul class="db-sec-list">
                <li class="db-sec-item">
                    <span class="db-sec-icon">✓</span>
                    <div>
                        <strong style="color: var(--text-main); display: block; margin-bottom: 2px;">Session Cookie an toàn</strong>
                        <span>Đã cấu hình các cờ HttpOnly, Secure và SameSite=Lax giúp ngăn chặn tấn công đánh cắp session (XSS/CSRF).</span>
                    </div>
                </li>
                <li class="db-sec-item">
                    <span class="db-sec-icon">✓</span>
                    <div>
                        <strong style="color: var(--text-main); display: block; margin-bottom: 2px;">Chống Session Fixation</strong>
                        <span>ID phiên làm việc (Session ID) được tự động tái cấp phát (regenerated) ngay sau khi đăng nhập thành công.</span>
                    </div>
                </li>
                <li class="db-sec-item">
                    <span class="db-sec-icon">✓</span>
                    <div>
                        <strong style="color: var(--text-main); display: block; margin-bottom: 2px;">Tự động Đăng xuất (Timeout)</strong>
                        <span>Hệ thống tự động hủy phiên làm việc và đăng xuất sau 10 phút người dùng không có hoạt động để đảm bảo an toàn.</span>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
