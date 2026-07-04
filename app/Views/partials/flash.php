<?php if (!empty($flash['success'])): ?>
    <div class="alert success">
        <span class="alert-icon">✓</span>
        <span class="alert-message"><?= e($flash['success']) ?></span>
    </div>
<?php endif; ?>

<?php if (!empty($flash['error'])): ?>
    <div class="alert-error-banner">
        <span class="alert-message"><?= e($flash['error']) ?></span>
    </div>
<?php endif; ?>
