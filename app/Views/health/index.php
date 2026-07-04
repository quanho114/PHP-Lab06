<div class="index-header" style="margin-bottom: 24px;">
    <h1>Giám Sát Hệ Thống</h1>
    <button onclick="window.location.reload();" class="btn primary" style="display: flex; align-items: center; gap: 6px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="refresh-icon">
            <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
        </svg>
        Kiểm Tra Lại
    </button>
</div>

<div class="data-card" style="padding: 32px; border-color: var(--border-light);">
    <!-- Overall Status Banner -->
    <div style="display: flex; align-items: center; gap: 16px; padding: 20px; background-color: <?= $db_status === 'connected' ? '#f0fdf4' : '#fef2f2' ?>; border: 1px solid <?= $db_status === 'connected' ? '#bbf7d0' : '#fecaca' ?>; border-radius: 12px; margin-bottom: 32px;">
        <div class="pulse-container">
            <span class="status-pulse-dot" style="background-color: <?= $db_status === 'connected' ? '#22c55e' : '#ef4444' ?>;"></span>
        </div>
        <div>
            <h3 style="margin: 0 0 4px; font-weight: 700; color: <?= $db_status === 'connected' ? '#166534' : '#991b1b' ?>; font-size: 1.1rem;">
                <?= $db_status === 'connected' ? 'Hệ thống hoạt động bình thường' : 'Hệ thống gặp sự cố' ?>
            </h3>
            <p style="margin: 0; font-size: 0.85rem; color: <?= $db_status === 'connected' ? '#15803d' : '#b91c1c' ?>;">
                <?= $db_status === 'connected' ? 'Tất cả các dịch vụ cốt lõi đang vận hành ổn định.' : 'Không thể kết nối tới cơ sở dữ liệu.' ?>
            </p>
        </div>
    </div>

    <!-- Metrics Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
        <!-- App Status -->
        <div style="background-color: var(--bg-surface-alt); border: 1px solid var(--border-light); border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
            <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Ứng dụng</span>
            <div style="margin-top: 12px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-weight: 700; font-size: 1.25rem; color: var(--text-main);">Healthy</span>
                <span class="badge badge-completed">Operational</span>
            </div>
        </div>

        <!-- Database Status -->
        <div style="background-color: var(--bg-surface-alt); border: 1px solid var(--border-light); border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
            <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Cơ sở dữ liệu</span>
            <div style="margin-top: 12px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-weight: 700; font-size: 1.25rem; color: var(--text-main);"><?= $db_status === 'connected' ? 'MySQL' : 'Lỗi kết nối' ?></span>
                <span class="badge <?= $db_status === 'connected' ? 'badge-completed' : 'badge-cancelled' ?>">
                    <?= $db_status === 'connected' ? 'Connected' : 'Disconnected' ?>
                </span>
            </div>
            <?php if ($db_error): ?>
                <small style="color: var(--danger-text); margin-top: 8px; font-family: var(--font-mono); font-size: 0.75rem; word-break: break-all;"><?= e($db_error) ?></small>
            <?php endif; ?>
        </div>

        <!-- PHP Version -->
        <div style="background-color: var(--bg-surface-alt); border: 1px solid var(--border-light); border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
            <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Môi trường PHP</span>
            <div style="margin-top: 12px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-family: var(--font-mono); font-weight: 700; font-size: 1.25rem; color: var(--text-main);">v<?= e($php_version) ?></span>
                <span class="badge badge-other">PHP Runtime</span>
            </div>
        </div>

        <!-- Memory Usage -->
        <div style="background-color: var(--bg-surface-alt); border: 1px solid var(--border-light); border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
            <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Bộ nhớ sử dụng</span>
            <div style="margin-top: 12px;">
                <div style="display: flex; align-items: baseline; gap: 6px; margin-bottom: 6px;">
                    <span style="font-family: var(--font-mono); font-weight: 700; font-size: 1.25rem; color: var(--text-main);"><?= round($memory_usage / (1024 * 1024), 2) ?> MB</span>
                    <span style="font-size: 0.72rem; color: var(--text-muted); font-weight: 500;">(Đỉnh: <?= round($memory_peak / (1024 * 1024), 2) ?> MB)</span>
                </div>
                <div style="width: 100%; height: 6px; background-color: var(--border-light); border-radius: 3px; overflow: hidden;">
                    <div style="width: <?= min(100, round(($memory_usage / $memory_peak) * 100)) ?>%; height: 100%; background-color: var(--accent); border-radius: 3px;"></div>
                </div>
            </div>
        </div>

        <!-- Server Time -->
        <div style="background-color: var(--bg-surface-alt); border: 1px solid var(--border-light); border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
            <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Thời gian máy chủ</span>
            <div style="margin-top: 12px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-family: var(--font-mono); font-weight: 700; font-size: 1.1rem; color: var(--text-main);"><?= e($server_time) ?></span>
                <span class="badge badge-other">UTC/Local</span>
            </div>
        </div>

        <!-- Server Engine -->
        <div style="background-color: var(--bg-surface-alt); border: 1px solid var(--border-light); border-radius: 10px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
            <span style="font-size: 0.78rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Phần mềm máy chủ</span>
            <div style="margin-top: 12px; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-family: var(--font-mono); font-weight: 700; font-size: 0.88rem; color: var(--text-main); text-overflow: ellipsis; overflow: hidden; white-space: nowrap; max-width: 160px;" title="<?= e($server_software) ?>"><?= e($server_software) ?></span>
                <span class="badge badge-other">Web Engine</span>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom Pulse CSS */
.pulse-container {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    position: relative;
}

.status-pulse-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    position: relative;
}

.status-pulse-dot::after {
    content: '';
    position: absolute;
    top: -6px;
    left: -6px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background-color: inherit;
    opacity: 0.4;
    animation: pulse-ring 1.5s cubic-bezier(0.215, 0.610, 0.355, 1) infinite;
}

@keyframes pulse-ring {
    0% {
        transform: scale(0.3);
        opacity: 0.8;
    }
    80%, 100% {
        transform: scale(1.3);
        opacity: 0;
    }
}

.refresh-icon {
    animation: none;
    transition: transform 0.3s ease;
}

.btn:active .refresh-icon {
    transform: rotate(180deg);
}
</style>
