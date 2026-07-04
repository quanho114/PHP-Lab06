<div class="form-card-horizontal" style="max-width: 420px; margin: 80px auto; border-radius: var(--radius-md); box-shadow: var(--shadow-lg); padding: 36px 32px;">
    <div style="text-align: center; margin-bottom: 24px;">
        <div style="width: 48px; height: 48px; background: var(--accent-glow); color: var(--accent); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; box-shadow: 0 4px 12px rgba(230, 92, 57, 0.15);">
            <svg style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
        </div>
        <h2 style="font-size: 1.5rem; margin-bottom: 6px; color: var(--text-main); font-weight: 800;">Đăng nhập Hệ thống</h2>
        <p style="font-size: 0.82rem; color: var(--text-muted);">Truy cập cổng quản trị Training Center CRM</p>
    </div>
    
    <?php if (isset($errors['general'])): ?>
        <div class="alert-error-banner" style="margin-bottom: 20px;"><?= e($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" action="/login">
        <?= csrf_field() ?>
        
        <div class="form-row" style="flex-direction: column; align-items: stretch; margin-bottom: 18px;">
            <label for="email" style="width: auto; margin-bottom: 6px; font-weight: 600; font-size: 0.8rem; color: var(--text-secondary);">Email đăng nhập</label>
            <div class="input-container">
                <input type="email" id="email" name="email" class="<?= isset($errors['email']) ? 'input-error' : '' ?>" value="<?= e($old['email'] ?? '') ?>" placeholder="admin@example.com" style="padding: 10px 14px;" required>
                <?php if (isset($errors['email'])): ?>
                    <div class="error" style="margin-top: 4px; font-size: 0.78rem; color: var(--danger-text);"><?= e($errors['email']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-row" style="flex-direction: column; align-items: stretch; margin-bottom: 24px;">
            <label for="password" style="width: auto; margin-bottom: 6px; font-weight: 600; font-size: 0.8rem; color: var(--text-secondary);">Mật khẩu</label>
            <div class="input-container">
                <input type="password" id="password" name="password" class="<?= isset($errors['password']) ? 'input-error' : '' ?>" placeholder="••••••••" style="padding: 10px 14px;" required>
                <?php if (isset($errors['password'])): ?>
                    <div class="error" style="margin-top: 4px; font-size: 0.78rem; color: var(--danger-text);"><?= e($errors['password']) ?></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions-horizontal" style="padding-left: 0; justify-content: center; margin-top: 28px;">
            <button type="submit" class="btn primary" style="width: 100%; height: 44px; font-weight: 600; font-size: 0.9rem;">Xác thực & Đăng nhập</button>
        </div>
    </form>
    
    <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border-light); text-align: center;">
        <span style="font-size: 0.72rem; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
            🔒 Kết nối bảo mật HTTPS & Chống tấn công CSRF chủ động
        </span>
    </div>
</div>
