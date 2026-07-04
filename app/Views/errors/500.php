<?php
$configPath = __DIR__ . '/../../../config/app.php';
$debug = false;
if (file_exists($configPath)) {
    $config = require $configPath;
    $debug = $config['debug'] ?? false;
}
?>
<div class="requirements-card" style="max-width: 600px; margin: 80px auto; border-color: var(--danger-border);">
    <h3 style="color: var(--danger-text); font-size: 1.5rem; margin-bottom: 15px;">500 Internal Server Error</h3>
    <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 0.9rem;">
        Đã xảy ra sự cố trên máy chủ. Vui lòng thử lại sau.
    </p>
    <?php if ($debug && !empty($message)): ?>
        <div class="db-rule-container" style="background: #fafafa; border-color: var(--border-light); margin-top: 15px;">
            <strong style="color: var(--text-main); font-size: 0.8rem; display: block; margin-bottom: 5px;">Dev Debug Info:</strong>
            <code style="display: block; font-family: monospace; font-size: 0.8rem; color: var(--danger-text); white-space: pre-wrap;"><?= e($message) ?></code>
        </div>
    <?php endif; ?>
    <div style="margin-top: 25px;">
        <a href="/" class="btn primary">Quay lại Trang chủ</a>
    </div>
</div>
