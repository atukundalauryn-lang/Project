ALERTS;<?php
require_once '../includes/config.php';
requireLogin();
$db = getDB();

// Handle threshold update (admin only)
$user = getCurrentUser();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user['role'] === 'admin') {
    verifyCsrf();
    $threshold = max(1, intval($_POST['threshold'] ?? 5));
    // Store in a simple settings table or a flat file
    file_put_contents(__DIR__ . '/../includes/low_stock_threshold.txt', $threshold);
    $msg = "Low stock threshold updated to $threshold units.";
}

// Read threshold
$thresholdFile = __DIR__ . '/../includes/low_stock_threshold.txt';
$threshold = file_exists($thresholdFile) ? intval(file_get_contents($thresholdFile)) : 5;

// Fetch out of stock
$out_of_stock = $db->query("
    SELECT product_name, code, product_type, unit, current_stock, sales_rate
    FROM current_stock
    WHERE current_stock <= 0
    ORDER BY product_name
");

// Fetch low stock
$low_stock = $db->prepare("
    SELECT product_name, code, product_type, unit, current_stock, sales_rate
    FROM current_stock
    WHERE current_stock > 0 AND current_stock <= ?
    ORDER BY current_stock ASC
");
$low_stock->bind_param('i', $threshold);
$low_stock->execute();
$low_stock_result = $low_stock->get_result();
$low_stock->close();

// Counts
$out_count = $out_of_stock->num_rows;
$low_count = $low_stock_result->num_rows;

$pageTitle  = 'Stock Alerts';
$activePage = 'alerts';
include '../includes/header.php';
?>

<?php if (isset($msg)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<!-- Summary strip -->
<div class="stats-grid" style="margin-bottom:24px">
    <div class="stat-card red">
        <div class="stat-label">Out of Stock</div>
        <div class="stat-value"><?= $out_count ?></div>
        <div class="stat-sub">Needs restocking immediately</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-label">Low Stock</div>
        <div class="stat-value"><?= $low_count ?></div>
        <div class="stat-sub">Below <?= $threshold ?> units</div>
    </div>
    <div class="stat-card <?= ($out_count + $low_count) === 0 ? 'green' : '' ?>">
        <div class="stat-label">Total Alerts</div>
        <div class="stat-value"><?= $out_count + $low_count ?></div>
        <div class="stat-sub"><?= ($out_count + $low_count) === 0 ? 'All stock healthy ✅' : 'Items need attention' ?></div>
    </div>
    <?php if ($user['role'] === 'admin'): ?>
    <div class="stat-card">
        <div class="stat-label">Alert Threshold</div>
        <div class="stat-value"><?= $threshold ?></div>
        <div class="stat-sub">Units — <a href="#settings" style="color:var(--accent)">change</a></div>
    </div>
    <?php endif; ?>
</div>

<!-- Out of Stock -->
<div class="card">
    <div class="card-title" style="color:var(--danger)">🔴 Out of Stock (<?= $out_count ?>)</div>
    <?php if ($out_count === 0): ?>
        <p style="color:var(--text-muted);padding:10px 0">No products are out of stock. Great!</p>
    <?php else: ?>
        <?php while ($row = $out_of_stock->fetch_assoc()): ?>
        <div class="alert-card critical">
            <div class="alert-card-icon">📦</div>
            <div>
                <div class="alert-card-title"><?= htmlspecialchars($row['product_name']) ?></div>
                <div class="alert-card-sub">
                    <?= htmlspecialchars($row['product_type'] ?? '') ?>
                    <?= $row['code'] ? ' · Code: ' . htmlspecialchars($row['code']) : '' ?>
                    · Unit: <?= htmlspecialchars($row['unit'] ?? '-') ?>
                    · Rate: UGX <?= number_format($row['sales_rate'], 2) ?>
                </div>
            </div>
            <div class="alert-card-stock">
                <strong style="color:var(--danger)">0</strong>
                <div style="font-size:11px;color:var(--text-muted)">units left</div>
            </div>
        </div>
        <?php endwhile; ?>
        <div style="margin-top:14px">
            <a href="<?= BASE_URL ?>pages/purchases.php" class="btn btn-primary">+ Record Purchase to Restock</a>
        </div>
    <?php endif; ?>
</div>

<!-- Low Stock -->
<div class="card">
    <div class="card-title" style="color:var(--warning)">🟡 Low Stock — below <?= $threshold ?> units (<?= $low_count ?>)</div>
    <?php if ($low_count === 0): ?>
        <p style="color:var(--text-muted);padding:10px 0">No products are running low.</p>
    <?php else: ?>
        <?php while ($row = $low_stock_result->fetch_assoc()):
            $cs = floatval($row['current_stock']);
            $pct = min(100, ($cs / $threshold) * 100);
        ?>
        <div class="alert-card warning">
            <div class="alert-card-icon">⚠️</div>
            <div style="flex:1">
                <div class="alert-card-title"><?= htmlspecialchars($row['product_name']) ?></div>
                <div class="alert-card-sub">
                    <?= htmlspecialchars($row['product_type'] ?? '') ?>
                    <?= $row['code'] ? ' · Code: ' . htmlspecialchars($row['code']) : '' ?>
                    · Unit: <?= htmlspecialchars($row['unit'] ?? '-') ?>
                    · Rate: UGX <?= number_format($row['sales_rate'], 2) ?>
                </div>
                <!-- Stock level bar -->
                <div style="margin-top:8px;background:var(--bg3);border-radius:4px;height:6px;width:100%;max-width:200px">
                    <div style="height:6px;border-radius:4px;width:<?= $pct ?>%;background:var(--warning)"></div>
                </div>
            </div>
            <div class="alert-card-stock">
                <strong style="color:var(--warning)"><?= number_format($cs, 2) ?></strong>
                <div style="font-size:11px;color:var(--text-muted)">units left</div>
            </div>
        </div>
        <?php endwhile; ?>
        <div style="margin-top:14px">
            <a href="<?= BASE_URL ?>pages/purchases.php" class="btn btn-primary">+ Record Purchase to Restock</a>
        </div>
    <?php endif; ?>
</div>

<!-- Admin: change threshold -->
<?php if ($user['role'] === 'admin'): ?>
<div class="card" id="settings">
    <div class="card-title">⚙️ Alert Settings</div>
    <p style="color:var(--text-muted);font-size:13.5px;margin-bottom:16px">
        Set the minimum stock level that triggers a low-stock alert. Currently set to <strong><?= $threshold ?> units</strong>.
    </p>
    <form method="POST" style="display:flex;gap:12px;align-items:flex-end">
        <?= csrfField() ?>
        <div class="form-group">
            <label>Low Stock Threshold (units)</label>
            <input type="number" name="threshold" value="<?= $threshold ?>" min="1" max="1000" style="width:120px">
        </div>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>
<?php endif; ?>

<script>
// Bell toggle
document.getElementById('notifBell')?.addEventListener('click', function(e) {
    e.preventDefault();
    document.getElementById('notifPanel')?.classList.toggle('open');
});
document.addEventListener('click', function(e) {
    if (!e.target.closest('#notifBell') && !e.target.closest('#notifPanel')) {
        document.getElementById('notifPanel')?.classList.remove('open');
    }
});
</script>

<?php $db->close(); include '../includes/footer.php'; ?>