<div class="index-header">
    <h1><?= e($title) ?></h1>
    <a href="/enrollments" class="btn secondary">&larr; Quay lại danh sách</a>
</div>

<div class="form-card-horizontal" style="max-width: 700px; margin: 0 auto; border-color: var(--border-light);">
    <form method="POST" action="/enrollments/update">
        <?= csrf_field() ?>

        <!-- Hidden ID -->
        <input type="hidden" name="id" value="<?= e((string)$enrollment['id']) ?>">

        <!-- Enrollment Code -->
        <div class="form-row">
            <label for="enrollment_code">Mã Phiếu Đăng Ký <span style="color:var(--danger-text)">*</span></label>
            <div class="input-container">
                <input type="text" id="enrollment_code" name="enrollment_code" class="<?= isset($errors['enrollment_code']) ? 'input-error' : '' ?>" value="<?= e(old('enrollment_code', $old, $enrollment['enrollment_code'] ?? '')) ?>" placeholder="ENR-YYYY-XXXX">
                <?php if (isset($errors['enrollment_code'])): ?>
                    <div class="error"><?= e($errors['enrollment_code']) ?></div>
                <?php endif; ?>
                <small class="text-muted">Định dạng chuẩn: ENR-YYYY-XXXX (Ví dụ: ENR-2026-0001).</small>
            </div>
        </div>

        <!-- Student Name -->
        <div class="form-row">
            <label for="student_name">Tên Học Viên <span style="color:var(--danger-text)">*</span></label>
            <div class="input-container">
                <input type="text" id="student_name" name="student_name" class="<?= isset($errors['student_name']) ? 'input-error' : '' ?>" value="<?= e(old('student_name', $old, $enrollment['student_name'] ?? '')) ?>" placeholder="Nhập tên học viên...">
                <?php if (isset($errors['student_name'])): ?>
                    <div class="error"><?= e($errors['student_name']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Student Email -->
        <div class="form-row">
            <label for="student_email">Email Học Viên</label>
            <div class="input-container">
                <input type="email" id="student_email" name="student_email" class="<?= isset($errors['student_email']) ? 'input-error' : '' ?>" value="<?= e(old('student_email', $old, $enrollment['student_email'] ?? '')) ?>" placeholder="student@example.com">
                <?php if (isset($errors['student_email'])): ?>
                    <div class="error"><?= e($errors['student_email']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Course Fee -->
        <div class="form-row">
            <label for="course_fee">Học Phí (VNĐ) <span style="color:var(--danger-text)">*</span></label>
            <div class="input-container">
                <input type="number" step="1000" id="course_fee" name="course_fee" class="<?= isset($errors['course_fee']) ? 'input-error' : '' ?>" value="<?= e(old('course_fee', $old, $enrollment['course_fee'] ?? '0')) ?>" placeholder="Học phí...">
                <?php if (isset($errors['course_fee'])): ?>
                    <div class="error"><?= e($errors['course_fee']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Payment Status -->
        <div class="form-row">
            <label for="payment_status">Trạng thái thanh toán <span style="color:var(--danger-text)">*</span></label>
            <div class="input-container">
                <select id="payment_status" name="payment_status" class="<?= isset($errors['payment_status']) ? 'input-error' : '' ?>">
                    <?php
                    $statuses = [
                        'unpaid' => 'Chưa thanh toán (Unpaid)',
                        'paid' => 'Đã thanh toán (Paid)',
                        'refunded' => 'Đã hoàn tiền (Refunded)',
                        'cancelled' => 'Đã hủy (Cancelled)'
                    ];
                    $selectedStatus = old('payment_status', $old, $enrollment['payment_status'] ?? 'unpaid');
                    foreach ($statuses as $val => $label):
                    ?>
                        <option value="<?= e($val) ?>" <?= $selectedStatus === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['payment_status'])): ?>
                    <div class="error"><?= e($errors['payment_status']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions-horizontal">
            <button type="submit" class="btn primary">Cập nhật phiếu</button>
            <a href="/enrollments" class="btn secondary">Hủy</a>
        </div>
    </form>
</div>
