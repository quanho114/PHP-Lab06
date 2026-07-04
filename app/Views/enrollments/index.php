<div class="index-header">
    <h1><?= e($title) ?></h1>
    <a href="/enrollments/create" class="btn primary">+ Thêm Phiếu Đăng Ký</a>
</div>

<div class="data-card">
    <!-- Search Toolbar -->
    <div class="data-card-header">
        <form method="GET" action="/enrollments" class="toolbar">
            <div class="search-input-group">
                <span class="search-icon">🔍</span>
                <input type="text" name="q" value="<?= e($keyword) ?>" placeholder="Tìm theo mã, họ tên, email học viên...">
            </div>
            
            <div class="filter-group">
                <select name="payment_status">
                    <option value="">-- Tất cả trạng thái --</option>
                    <option value="unpaid" <?= $payment_status === 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán (Unpaid)</option>
                    <option value="paid" <?= $payment_status === 'paid' ? 'selected' : '' ?>>Đã thanh toán (Paid)</option>
                    <option value="refunded" <?= $payment_status === 'refunded' ? 'selected' : '' ?>>Đã hoàn tiền (Refunded)</option>
                    <option value="cancelled" <?= $payment_status === 'cancelled' ? 'selected' : '' ?>>Đã hủy (Cancelled)</option>
                </select>
            </div>

            <!-- Preserve sorting parameters during search -->
            <input type="hidden" name="sort" value="<?= e($sort) ?>">
            <input type="hidden" name="direction" value="<?= e($direction) ?>">
            
            <button type="submit" class="btn btn-search">Tìm kiếm</button>
            <?php if ($keyword !== '' || $payment_status !== ''): ?>
                <a href="/enrollments" class="btn secondary">Xóa bộ lọc</a>
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
                        'enrollment_code' => 'Mã Đăng Ký',
                        'student_name' => 'Tên Học Viên',
                        'student_email' => 'Email',
                        'course_fee' => 'Học Phí',
                        'payment_status' => 'Thanh Toán',
                        'created_at' => 'Ngày tạo'
                    ];
                    foreach ($headers as $col => $label):
                        $isActive = ($sort === $col);
                        $arrow = '';
                        if ($isActive) {
                            $arrow = ($direction === 'asc') ? ' ▲' : ' ▼';
                        }
                        $sortUrl = "/enrollments?q=" . urlencode($keyword) . "&payment_status=" . urlencode($payment_status) . "&sort={$col}&direction=" . ($isActive ? $toggleDirection : 'asc');
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
                <?php if (empty($enrollments)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted" style="padding: 30px;">Không có dữ liệu phù hợp.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($enrollments as $item): ?>
                        <tr>
                            <td><?= e((string)$item['id']) ?></td>
                            <td class="font-semibold" style="color: var(--accent);"><?= e($item['enrollment_code']) ?></td>
                            <td class="font-semibold"><?= e($item['student_name']) ?></td>
                            <td><?= e($item['student_email'] ?: '-') ?></td>
                            <td class="font-semibold"><?= e(number_format($item['course_fee'], 0, ',', '.')) ?> VNĐ</td>
                            <td>
                                <?php
                                $statusClass = 'badge-other';
                                if ($item['payment_status'] === 'unpaid') $statusClass = 'badge-pending';
                                elseif ($item['payment_status'] === 'paid') $statusClass = 'badge-completed';
                                elseif ($item['payment_status'] === 'refunded') $statusClass = 'badge-female';
                                elseif ($item['payment_status'] === 'cancelled') $statusClass = 'badge-cancelled';
                                ?>
                                <span class="badge <?= $statusClass ?>"><?= ucfirst(e($item['payment_status'])) ?></span>
                            </td>
                            <td><?= e(date('d/m/Y H:i', strtotime($item['created_at']))) ?></td>
                            <td class="text-right">
                                <div class="actions-wrapper">
                                    <a href="/enrollments/edit?id=<?= e((string)$item['id']) ?>" class="btn edit-btn">Sửa</a>
                                    <form method="POST" action="/enrollments/delete" style="display: inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phiếu đăng ký này?');">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= e((string)$item['id']) ?>">
                                        <button type="submit" class="link danger">Xóa</button>
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
                    Hiển thị trang <?= e((string)$page) ?> / <?= e((string)$totalPages) ?> (Tổng <?= e((string)$totalItems) ?> phiếu)
                </span>
                <div class="page-numbers">
                    <!-- Previous page link -->
                    <?php if ($page > 1): ?>
                        <a href="/enrollments?page=<?= $page - 1 ?>&q=<?= urlencode($keyword) ?>&payment_status=<?= urlencode($payment_status) ?>&sort=<?= e($sort) ?>&direction=<?= e($direction) ?>" class="page-link">&laquo;</a>
                    <?php endif; ?>

                    <!-- Page numbers -->
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="/enrollments?page=<?= $i ?>&q=<?= urlencode($keyword) ?>&payment_status=<?= urlencode($payment_status) ?>&sort=<?= e($sort) ?>&direction=<?= e($direction) ?>" class="page-link <?= $page === $i ? 'active' : '' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <!-- Next page link -->
                    <?php if ($page < $totalPages): ?>
                        <a href="/enrollments?page=<?= $page + 1 ?>&q=<?= urlencode($keyword) ?>&payment_status=<?= urlencode($payment_status) ?>&sort=<?= e($sort) ?>&direction=<?= e($direction) ?>" class="page-link">&raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
