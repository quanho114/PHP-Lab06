<div class="form-card-horizontal" style="max-width: 450px; margin: 60px auto; border-color: var(--border-light); box-shadow: var(--shadow-lg);">
    <h2 style="font-size: 1.6rem; font-family: var(--font-serif); text-align: center; margin-bottom: 10px; color: var(--text-main);">Đăng Nhập</h2>
    <p style="font-size: 0.85rem; color: var(--text-muted); text-align: center; margin-bottom: 24px;">Truy cập cổng quản trị Training Center CRM</p>
    
    <?php if (isset($errors['general'])): ?>
        <div class="alert-error-banner"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" action="/login">
        <?= csrf_field() ?>
        
        <div class="form-row" style="flex-direction: column; align-items: stretch; margin-bottom: 20px;">
            <label for="email" style="width: auto; margin-bottom: 6px; font-weight: 600;">Email đăng nhập</label>
            <div class="input-container">
                <input type="email" id="email" name="email" class="<?= isset($errors['email']) ? 'input-error' : '' ?>" value="<?= e($old['email'] ?? '') ?>" placeholder="admin@example.com" required>
                <?php if (isset($errors['email'])): ?>
                    <div class="error"><?= e($errors['email']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-row" style="flex-direction: column; align-items: stretch; margin-bottom: 20px;">
            <label for="password" style="width: auto; margin-bottom: 6px; font-weight: 600;">Mật khẩu</label>
            <div class="input-container">
                <input type="password" id="password" name="password" class="<?= isset($errors['password']) ? 'input-error' : '' ?>" placeholder="••••••••" required>
                <?php if (isset($errors['password'])): ?>
                    <div class="error"><?= e($errors['password']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions-horizontal" style="padding-left: 0; justify-content: center; margin-top: 30px;">
            <button type="submit" class="btn primary" style="width: 100%; height: 40px; font-weight: 600;">Đăng nhập hệ thống</button>
        </div>
    </form>
</div>
