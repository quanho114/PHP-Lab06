<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Secure CRM') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/style.css?v=1.0.9">
</head>
<body>
<div class="app-layout">
    <?php partial('nav'); ?>
    
    <div class="main-area">
        <main class="main-content">
            <?php partial('flash'); ?>
            <?= $content ?? '' ?>
        </main>
        
        <footer class="footer" style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 0.8rem; border-top: 1px solid var(--border-light); background: var(--bg-surface); margin-top: auto;">
            <p>&copy; <?= date('Y') ?> - Secure CRM Control Center. Built with MVC architecture.</p>
        </footer>
    </div>
</div>
</body>
</html>
