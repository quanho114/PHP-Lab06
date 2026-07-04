<div class="landing-page-wrapper">
    <!-- Hero Banner Header -->
    <div style="text-align: center; max-width: 800px; margin: 12px auto 20px; padding: 0 16px;">
        <span style="display: inline-block; font-size: 0.75rem; font-weight: 750; text-transform: uppercase; letter-spacing: 1.5px; color: var(--accent); margin-bottom: 8px; background: var(--accent-glow); padding: 4px 10px; border-radius: 12px;">Cổng thông tin tuyển sinh trực tuyến</span>
        <h1 style="font-size: 2.3rem; font-weight: 800; color: var(--text-main); margin-bottom: 10px; line-height: 1.15; letter-spacing: -1px; text-wrap: balance;">Training Center CRM</h1>
        <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.5; text-wrap: pretty; margin: 0;">
            Đăng ký tư vấn lộ trình học tập, tiếp cận các khóa học chuyên sâu và quản lý hồ sơ của bạn với quy trình hiện đại, bảo mật.
        </p>
    </div>

    <div class="form-container-grid">
        <!-- Registration Form -->
        <div class="form-card-horizontal" style="padding: 24px 28px; border-radius: var(--radius-md); box-shadow: var(--shadow-md);">
            <h2 style="font-size: 1.25rem; margin-bottom: 4px; color: var(--text-main);">Đăng ký tư vấn khóa học</h2>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 16px;">Vui lòng điền thông tin để chuyên viên liên hệ trong vòng 24h.</p>
            
            <?php if (!empty($errors['general'])): ?>
                <div class="alert-error-banner" style="margin-bottom: 16px;"><?= e($errors['general']) ?></div>
            <?php endif; ?>

            <form method="POST" action="/leads/public-store">
                <?= csrf_field() ?>

                <!-- Honeypot Field (Anti-Spam) -->
                <div style="display: none;">
                    <label for="website">Please leave this blank</label>
                    <input type="text" id="website" name="website" value="">
                </div>

                <!-- Row 1: Full Name & Email -->
                <div class="form-row-group">
                    <!-- Full Name -->
                    <div class="form-row" style="flex: 1; margin-bottom: 12px;">
                        <label for="fullname" style="font-weight: 600;">Họ & Tên <span style="color:var(--danger-text)">*</span></label>
                        <div class="input-container">
                            <input type="text" id="fullname" name="fullname" class="<?= isset($errors['fullname']) ? 'input-error' : '' ?>" value="<?= e(old('fullname', $old)) ?>" placeholder="Nguyễn Văn A" style="padding: 9px 12px;">
                            <?php if (isset($errors['fullname'])): ?>
                                <div class="error" style="margin-top: 4px; font-size: 0.78rem; color: var(--danger-text);"><?= e($errors['fullname']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="form-row" style="flex: 1; margin-bottom: 12px;">
                        <label for="email" style="font-weight: 600;">Email <span style="color:var(--danger-text)">*</span></label>
                        <div class="input-container">
                            <input type="email" id="email" name="email" class="<?= isset($errors['email']) ? 'input-error' : '' ?>" value="<?= e(old('email', $old)) ?>" placeholder="nguyenvana@example.com" style="padding: 9px 12px;">
                            <?php if (isset($errors['email'])): ?>
                                <div class="error" style="margin-top: 4px; font-size: 0.78rem; color: var(--danger-text);"><?= e($errors['email']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Phone & Course -->
                <div class="form-row-group">
                    <!-- Phone -->
                    <div class="form-row" style="flex: 1; margin-bottom: 12px;">
                        <label for="phone" style="font-weight: 600;">Số điện thoại</label>
                        <div class="input-container">
                            <input type="text" id="phone" name="phone" class="<?= isset($errors['phone']) ? 'input-error' : '' ?>" value="<?= e(old('phone', $old)) ?>" placeholder="0909000000" style="padding: 9px 12px;">
                            <?php if (isset($errors['phone'])): ?>
                                <div class="error" style="margin-top: 4px; font-size: 0.78rem; color: var(--danger-text);"><?= e($errors['phone']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Interested Course -->
                    <div class="form-row" style="flex: 1; margin-bottom: 12px;">
                        <label for="interested_course" style="font-weight: 600;">Khóa học quan tâm</label>
                        <div class="input-container">
                            <input type="text" id="interested_course" name="interested_course" class="<?= isset($errors['interested_course']) ? 'input-error' : '' ?>" value="<?= e(old('interested_course', $old)) ?>" placeholder="Ví dụ: PHP, ReactJS..." style="padding: 9px 12px;">
                            <?php if (isset($errors['interested_course'])): ?>
                                <div class="error" style="margin-top: 4px; font-size: 0.78rem; color: var(--danger-text);"><?= e($errors['interested_course']) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Note -->
                <div class="form-row" style="margin-bottom: 16px;">
                    <label for="note" style="font-weight: 600;">Ghi chú thêm</label>
                    <div class="input-container">
                        <textarea id="note" name="note" class="<?= isset($errors['note']) ? 'input-error' : '' ?>" placeholder="Nhập thêm thời gian liên hệ phù hợp..." style="padding: 9px 12px; min-height: 52px; height: 52px; resize: vertical;"><?= e(old('note', $old)) ?></textarea>
                        <?php if (isset($errors['note'])): ?>
                            <div class="error" style="margin-top: 4px; font-size: 0.78rem; color: var(--danger-text);"><?= e($errors['note']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-actions-horizontal" style="padding-left: 0; margin-top: 16px;">
                    <button type="submit" class="btn primary" style="width: 100%; height: 42px; font-weight: 600;">Gửi thông tin đăng ký tư vấn</button>
                </div>
            </form>
        </div>

        <!-- Info Column -->
        <div class="requirements-card" style="padding: 24px 28px; border-radius: var(--radius-md); background: linear-gradient(135deg, var(--bg-surface) 0%, rgba(238, 234, 225, 0.2) 100%);">
            <h3 style="font-size: 1.2rem; margin-bottom: 10px; color: var(--text-main);">Hệ thống Đăng ký & Tuyển sinh</h3>
            <p style="font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 16px; line-height: 1.5; margin-top: 0;">
                Chào mừng bạn đến với Cổng đăng ký khóa học của chúng tôi. Hệ thống tự động kết nối trực tiếp với CRM quản trị giúp xử lý hồ sơ nhanh chóng:
            </p>
            
            <ul class="requirements-list" style="display: flex; flex-direction: column; gap: 10px; list-style: none; padding-left: 0; margin: 0;">
                <li class="checked" style="font-size: 0.8rem; line-height: 1.4; color: var(--text-secondary); display: flex; align-items: flex-start; gap: 8px;">
                    <span style="color: var(--accent); font-weight: bold; flex-shrink: 0; font-size: 1rem; line-height: 1;">✦</span>
                    <span><strong>Tiếp nhận tức thời:</strong> Thông tin đăng ký được chuyển thẳng đến bộ phận hỗ trợ học tập.</span>
                </li>
                <li class="checked" style="font-size: 0.8rem; line-height: 1.4; color: var(--text-secondary); display: flex; align-items: flex-start; gap: 8px;">
                    <span style="color: var(--accent); font-weight: bold; flex-shrink: 0; font-size: 1rem; line-height: 1;">✦</span>
                    <span><strong>Mã định danh riêng:</strong> Tự động cấp mã hồ sơ định danh duy nhất khi nhập học chính thức.</span>
                </li>
                <li class="checked" style="font-size: 0.8rem; line-height: 1.4; color: var(--text-secondary); display: flex; align-items: flex-start; gap: 8px;">
                    <span style="color: var(--accent); font-weight: bold; flex-shrink: 0; font-size: 1rem; line-height: 1;">✦</span>
                    <span><strong>An toàn tuyệt đối:</strong> Bảo mật thông tin liên hệ và dữ liệu đăng ký mã hóa hiện đại.</span>
                </li>
                <li class="checked" style="font-size: 0.8rem; line-height: 1.4; color: var(--text-secondary); display: flex; align-items: flex-start; gap: 8px;">
                    <span style="color: var(--accent); font-weight: bold; flex-shrink: 0; font-size: 1rem; line-height: 1;">✦</span>
                    <span><strong>Theo dõi trạng thái:</strong> Cập nhật trạng thái thanh toán và phân bổ lớp học nhanh chóng.</span>
                </li>
            </ul>
            
            <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light); text-align: center;">
                <p style="font-size: 0.76rem; color: var(--text-muted); margin: 0 0 6px;">Bạn là nhân viên quản trị?</p>
                <a href="/login" class="btn secondary" style="font-size: 0.78rem; padding: 6px 14px;">Đăng nhập Cổng Quản Trị</a>
            </div>
        </div>
    </div>
</div>
