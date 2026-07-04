<div class="form-container-grid" style="margin-top: 20px;">
    <div class="form-card-horizontal">
        <h2 style="font-size: 1.6rem; font-family: var(--font-serif); margin-bottom: 10px; color: var(--text-main);">Đăng ký Nhận Tư vấn Khóa học</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 24px;">Vui lòng điền đầy đủ thông tin bên dưới để được chuyên viên liên hệ tư vấn trong vòng 24h.</p>
        
        <?php if (!empty($errors['general'])): ?>
            <div class="alert-error-banner"><?= e($errors['general']) ?></div>
        <?php endif; ?>

        <form method="POST" action="/leads/public-store">
            <?= csrf_field() ?>

            <!-- Honeypot Field (Anti-Spam) -->
            <div style="display: none;">
                <label for="website">Please leave this blank</label>
                <input type="text" id="website" name="website" value="">
            </div>

            <!-- Full Name -->
            <div class="form-row">
                <label for="fullname">Họ & Tên <span style="color:var(--danger-text)">*</span></label>
                <div class="input-container">
                    <input type="text" id="fullname" name="fullname" class="<?= isset($errors['fullname']) ? 'input-error' : '' ?>" value="<?= e(old('fullname', $old)) ?>" placeholder="Nguyễn Văn A">
                    <?php if (isset($errors['fullname'])): ?>
                        <div class="error"><?= e($errors['fullname']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Email -->
            <div class="form-row">
                <label for="email">Email <span style="color:var(--danger-text)">*</span></label>
                <div class="input-container">
                    <input type="email" id="email" name="email" class="<?= isset($errors['email']) ? 'input-error' : '' ?>" value="<?= e(old('email', $old)) ?>" placeholder="nguyenvana@example.com">
                    <?php if (isset($errors['email'])): ?>
                        <div class="error"><?= e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Phone -->
            <div class="form-row">
                <label for="phone">Số điện thoại</label>
                <div class="input-container">
                    <input type="text" id="phone" name="phone" class="<?= isset($errors['phone']) ? 'input-error' : '' ?>" value="<?= e(old('phone', $old)) ?>" placeholder="0909000000">
                    <?php if (isset($errors['phone'])): ?>
                        <div class="error"><?= e($errors['phone']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Interested Course -->
            <div class="form-row">
                <label for="interested_course">Khóa học quan tâm</label>
                <div class="input-container">
                    <input type="text" id="interested_course" name="interested_course" class="<?= isset($errors['interested_course']) ? 'input-error' : '' ?>" value="<?= e(old('interested_course', $old)) ?>" placeholder="Ví dụ: Lập trình PHP nâng cao, ReactJS...">
                    <?php if (isset($errors['interested_course'])): ?>
                        <div class="error"><?= e($errors['interested_course']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Note -->
            <div class="form-row">
                <label for="note">Ghi chú thêm</label>
                <div class="input-container">
                    <textarea id="note" name="note" class="<?= isset($errors['note']) ? 'input-error' : '' ?>" placeholder="Nhập thêm thời gian liên hệ phù hợp..."><?= e(old('note', $old)) ?></textarea>
                    <?php if (isset($errors['note'])): ?>
                        <div class="error"><?= e($errors['note']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions-horizontal">
                <button type="submit" class="btn primary">Gửi thông tin đăng ký</button>
            </div>
        </form>
    </div>

    <div class="requirements-card">
        <h3>Hệ thống CRM Trung tâm Đào tạo</h3>
        <p style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 20px; line-height: 1.6;">
            Cung cấp các công cụ quản trị quy trình tiếp cận học viên (Leads) và quản lý hóa đơn học phí (Enrollments):
        </p>
        <ul class="requirements-list">
            <li class="checked">Quản lý Course Leads từ trang đăng ký tư vấn công khai.</li>
            <li class="checked">Lưu vết liên hệ, phân loại trạng thái học viên quan tâm.</li>
            <li class="checked">Quản lý Học viên & Phiếu Đăng Ký (Enrollment) kèm mã định danh ENR-YYYY-XXXX.</li>
            <li class="checked">Theo dõi thanh toán học phí (Paid, Unpaid, Refunded, Cancelled).</li>
            <li class="checked">Báo cáo thống kê tổng quan doanh thu trung tâm đào tạo.</li>
        </ul>
    </div>
</div>
