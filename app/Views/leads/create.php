<div style="max-width: 700px; margin: 0 auto;">
    <div class="index-header" style="margin-bottom: 24px;">
        <h1><?= e($title) ?></h1>
        <a href="/leads" class="btn secondary">&larr; Quay lại danh sách</a>
    </div>

    <div class="form-card-horizontal" style="border-color: var(--border-light);">
        <form method="POST" action="/leads/store">
            <?= csrf_field() ?>

            <!-- Full Name -->
            <div class="form-row">
                <label for="fullname">Họ & Tên <span style="color:var(--danger-text)">*</span></label>
                <div class="input-container">
                    <input type="text" id="fullname" name="fullname" class="<?= isset($errors['fullname']) ? 'input-error' : '' ?>" value="<?= e(old('fullname', $old)) ?>" placeholder="Nhập họ tên...">
                    <?php if (isset($errors['fullname'])): ?>
                        <div class="error"><?= e($errors['fullname']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Email -->
            <div class="form-row">
                <label for="email">Email <span style="color:var(--danger-text)">*</span></label>
                <div class="input-container">
                    <input type="email" id="email" name="email" class="<?= isset($errors['email']) ? 'input-error' : '' ?>" value="<?= e(old('email', $old)) ?>" placeholder="example@email.com">
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
                    <input type="text" id="interested_course" name="interested_course" class="<?= isset($errors['interested_course']) ? 'input-error' : '' ?>" value="<?= e(old('interested_course', $old)) ?>" placeholder="Ví dụ: PHP MVC Framework, ReactJS Frontend...">
                    <?php if (isset($errors['interested_course'])): ?>
                        <div class="error"><?= e($errors['interested_course']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status -->
            <div class="form-row">
                <label for="status">Trạng thái <span style="color:var(--danger-text)">*</span></label>
                <div class="input-container">
                    <select id="status" name="status" class="<?= isset($errors['status']) ? 'input-error' : '' ?>">
                        <?php
                        $statuses = [
                            'new' => 'Mới (New)',
                            'contacted' => 'Đã liên hệ (Contacted)',
                            'enrolled' => 'Đã nhập học (Enrolled)',
                            'lost' => 'Đã đóng/Không nhu cầu (Lost)'
                        ];
                        $selectedStatus = old('status', $old, 'new');
                        foreach ($statuses as $val => $label):
                        ?>
                            <option value="<?= e($val) ?>" <?= $selectedStatus === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['status'])): ?>
                        <div class="error"><?= e($errors['status']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Note -->
            <div class="form-row">
                <label for="note">Ghi chú</label>
                <div class="input-container">
                    <textarea id="note" name="note" class="<?= isset($errors['note']) ? 'input-error' : '' ?>" placeholder="Nhập nội dung ghi chú..."><?= e(old('note', $old)) ?></textarea>
                    <?php if (isset($errors['note'])): ?>
                        <div class="error"><?= e($errors['note']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-actions-horizontal">
                <button type="submit" class="btn primary">Tạo Lead Mới</button>
                <a href="/leads" class="btn secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
