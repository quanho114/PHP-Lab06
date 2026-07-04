<div style="margin-top: 10px; margin-bottom: 30px;">
    <h1 style="font-family: var(--font-serif); font-size: 1.8rem; margin-bottom: 5px; color: var(--text-main);">Dashboard Tổng Quan</h1>
    <p style="color: var(--text-secondary); font-size: 0.9rem;">Chào mừng quay trở lại, <strong><?= e($_SESSION['user_name']) ?></strong>. Dưới đây là thống kê hiện tại của trung tâm đào tạo.</p>
</div>

<!-- Stats Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px; margin-bottom: 40px;">
    <!-- Lead Count Card -->
    <div class="form-card-horizontal" style="display: flex; align-items: center; justify-content: space-between; padding: 24px; border-color: var(--border-light);">
        <div>
            <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 8px;">Course Leads</span>
            <span style="font-size: 2.2rem; font-weight: 800; color: var(--text-main); line-height: 1;"><?= e((string)$totalLeads) ?></span>
        </div>
        <div style="width: 48px; height: 48px; background: var(--primary-glow); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--primary);">
            <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
        </div>
    </div>

    <!-- Enrollment Count Card -->
    <div class="form-card-horizontal" style="display: flex; align-items: center; justify-content: space-between; padding: 24px; border-color: var(--border-light);">
        <div>
            <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 8px;">Enrollments</span>
            <span style="font-size: 2.2rem; font-weight: 800; color: var(--text-main); line-height: 1;"><?= e((string)$totalEnrollments) ?></span>
        </div>
        <div style="width: 48px; height: 48px; background: rgba(204, 90, 55, 0.08); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--accent);">
            <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
        </div>
    </div>

    <!-- Revenue Card -->
    <div class="form-card-horizontal" style="display: flex; align-items: center; justify-content: space-between; padding: 24px; border-color: var(--border-light);">
        <div>
            <span style="display: block; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 8px;">Học phí thu được</span>
            <span style="font-size: 1.8rem; font-weight: 800; color: var(--success-text); line-height: 1;"><?= number_format($revenue, 0, ',', '.') ?> VNĐ</span>
        </div>
        <div style="width: 48px; height: 48px; background: var(--success-bg); border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center; color: var(--success-text);">
            <svg style="width: 24px; height: 24px;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </div>
</div>

<!-- Shortcuts and Security status -->
<div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px;">
    <div class="requirements-card">
        <h3>Truy cập nhanh</h3>
        <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 16px;">Vui lòng chọn các tính năng quản lý ở dưới hoặc sử dụng menu phía trên.</p>
        <div style="display: flex; gap: 12px;">
            <a href="/leads" class="btn primary">Quản lý Course Leads</a>
            <a href="/enrollments" class="btn secondary">Quản lý Enrollments</a>
        </div>
    </div>
    
    <div class="requirements-card" style="border-color: var(--warning-border); background-color: var(--warning-bg);">
        <h3 style="color: var(--warning-text);">Trạng thái Bảo mật</h3>
        <ul style="list-style: none; padding-top: 8px;">
            <li style="font-size: 0.8rem; color: var(--warning-text); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 1rem;">🛡️</span> Session Cookie đã được bảo vệ với flags HttpOnly & Lax.
            </li>
            <li style="font-size: 0.8rem; color: var(--warning-text); margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 1rem;">🛡️</span> Tự động gia hạn ID session sau khi đăng nhập thành công.
            </li>
            <li style="font-size: 0.8rem; color: var(--warning-text); display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 1rem;">🛡️</span> Phiên làm việc sẽ hết hạn sau 10 phút không hoạt động.
            </li>
        </ul>
    </div>
</div>
