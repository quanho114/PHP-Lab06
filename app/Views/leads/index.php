<div class="index-header">
    <h1><?= e($title) ?></h1>
    <a href="/leads/create" class="btn primary">+ Thêm Lead Mới</a>
</div>

<div class="data-card">
    <!-- Search Toolbar -->
    <div class="data-card-header">
        <form method="GET" action="/leads" class="toolbar">
            <div class="search-input-group">
                <svg class="search-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="text" name="q" value="<?= e($keyword) ?>" placeholder="Tìm theo tên, email, điện thoại, khóa học...">
            </div>
            
            <div class="filter-group">
                <select name="status">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="new" <?= $status === 'new' ? 'selected' : '' ?>>Mới (New)</option>
                    <option value="contacted" <?= $status === 'contacted' ? 'selected' : '' ?>>Đã liên hệ (Contacted)</option>
                    <option value="enrolled" <?= $status === 'enrolled' ? 'selected' : '' ?>>Đã nhập học (Enrolled)</option>
                    <option value="lost" <?= $status === 'lost' ? 'selected' : '' ?>>Đã đóng/Không nhu cầu (Lost)</option>
                </select>
            </div>

            <!-- Preserve sorting parameters during search -->
            <input type="hidden" name="sort" value="<?= e($sort) ?>">
            <input type="hidden" name="direction" value="<?= e($direction) ?>">
            
            <button type="submit" class="btn btn-search">Tìm kiếm</button>
            <?php if ($keyword !== '' || $status !== ''): ?>
                <a href="/leads" class="btn secondary">Xóa bộ lọc</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Table content -->
    <div class="table-core">
        <table>
            <thead>
                <tr>
                    <?php
                    $toggleDirection = ($direction === 'asc') ? 'desc' : 'asc';
                    $headers = [
                        'id' => 'ID',
                        'fullname' => 'Họ & Tên',
                        'email' => 'Email',
                        'phone' => 'Số điện thoại',
                        'interested_course' => 'Khóa học quan tâm',
                        'status' => 'Trạng thái',
                        'created_at' => 'Ngày tạo'
                    ];
                    foreach ($headers as $col => $label):
                        $isActive = ($sort === $col);
                        $arrow = '';
                        if ($isActive) {
                            $arrow = ($direction === 'asc') ? ' ▲' : ' ▼';
                        }
                        $sortUrl = "/leads?q=" . urlencode($keyword) . "&status=" . urlencode($status) . "&sort={$col}&direction=" . ($isActive ? $toggleDirection : 'asc');
                    ?>
                        <th>
                            <a href="<?= $sortUrl ?>" style="color: inherit; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                                <?= e($label) ?><span style="font-size: 0.7rem;"><?= $arrow ?></span>
                            </a>
                        </th>
                    <?php endforeach; ?>
                    <th class="text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($leads)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding: 30px;">Không có dữ liệu phù hợp.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($leads as $lead): ?>
                        <tr>
                            <td style="font-family: var(--font-mono); font-size: 0.8rem; color: var(--text-muted); font-weight: 600;">#<?= e((string)$lead['id']) ?></td>
                            <td style="white-space: nowrap; font-weight: 600; color: var(--text-main);"><?= e($lead['fullname']) ?></td>
                            <td style="font-size: 0.82rem; color: var(--text-secondary);"><?= e($lead['email']) ?></td>
                            <td style="font-family: var(--font-mono); font-size: 0.82rem; color: var(--text-secondary);"><?= e($lead['phone'] ?: '-') ?></td>
                            <td style="font-weight: 500; color: var(--text-main);"><?= e($lead['interested_course'] ?: '-') ?></td>
                            <td>
                                <?php
                                $statusClass = 'badge-other';
                                if ($lead['status'] === 'new') $statusClass = 'badge-pending';
                                elseif ($lead['status'] === 'contacted') $statusClass = 'badge-confirmed';
                                elseif ($lead['status'] === 'enrolled') $statusClass = 'badge-completed';
                                elseif ($lead['status'] === 'lost') $statusClass = 'badge-cancelled';
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst(e($lead['status'])) ?></span>
                            </td>
                            <td style="font-family: var(--font-mono); font-size: 0.82rem; color: var(--text-muted);"><?= e(date('d/m/Y H:i', strtotime($lead['created_at']))) ?></td>
                            <td class="text-right">
                                <div class="actions-wrapper">
                                    <a href="/leads/edit?id=<?= e((string)$lead['id']) ?>" class="btn edit-btn">Sửa</a>
                                    <form method="POST" action="/leads/delete" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa lead này?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= e((string)$lead['id']) ?>">
                                        <button type="submit" class="btn danger-btn">Xóa</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer -->
    <?php if ($totalPages > 1): ?>
        <div class="data-card-footer">
            <div class="pagination">
                <span class="text-muted" style="font-size: 0.8rem;">
                    Hiển thị trang <?= e((string)$page) ?> / <?= e((string)$totalPages) ?> (Tổng <?= e((string)$totalItems) ?> leads)
                </span>
                <div class="page-numbers">
                    <!-- Previous page link -->
                    <?php if ($page > 1): ?>
                        <a href="/leads?page=<?= $page - 1 ?>&q=<?= urlencode($keyword) ?>&status=<?= urlencode($status) ?>&sort=<?= e($sort) ?>&direction=<?= e($direction) ?>" class="page-link">&laquo;</a>
                    <?php endif; ?>

                    <!-- Page numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="/leads?page=<?= $i ?>&q=<?= urlencode($keyword) ?>&status=<?= urlencode($status) ?>&sort=<?= e($sort) ?>&direction=<?= e($direction) ?>" class="page-link <?= $page === $i ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Next page link -->
                    <?php if ($page < $totalPages): ?>
                        <a href="/leads?page=<?= $page + 1 ?>&q=<?= urlencode($keyword) ?>&status=<?= urlencode($status) ?>&sort=<?= e($sort) ?>&direction=<?= e($direction) ?>" class="page-link">&raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
