<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin('login.php?next=dashboard.php');

if (isAdmin()) {
    header('Location: admin/dashboard.php');
    exit;
}

// Handle customer order cancellation (POST-redirect-GET).
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    if (!csrfCheck(postString('csrf_token'))) {
        setFlash('error', 'Your session expired. Please try again.');
        header('Location: dashboard.php');
        exit;
    }

    $cancelId = filter_var($_POST['cancel_order_id'] ?? null, FILTER_VALIDATE_INT);

    if ($cancelId === false || $cancelId < 1) {
        setFlash('error', 'That order could not be found.');
        header('Location: dashboard.php');
        exit;
    }

    // Only allow cancellation while the order is still pending.
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$cancelId, (int) $_SESSION['user_id']]);

    if ($stmt->rowCount() > 0) {
        setFlash('success', 'Order #' . $cancelId . ' has been successfully cancelled.');
    } else {
        setFlash('error', 'That order could not be cancelled. It may already be processing.');
    }

    header('Location: dashboard.php');
    exit;
}

// Fetch all orders.
$stmt = $pdo->prepare('SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([(int) $_SESSION['user_id']]);
$orders = $stmt->fetchAll();

// Fetch all items.
$itemsByOrder = [];
if (!empty($orders)) {
    $orderIds = array_column($orders, 'id');
    $placeholders = str_repeat('?,', count($orderIds) - 1) . '?';
    $stmtItems = $pdo->prepare("SELECT order_id, product_name, price, quantity FROM order_items WHERE order_id IN ($placeholders)");
    $stmtItems->execute($orderIds);
    while ($row = $stmtItems->fetch()) {
        $itemsByOrder[$row['order_id']][] = $row;
    }
}

$pageTitle  = 'My Purchases';
$activePage = 'dashboard';
include __DIR__ . '/includes/header.php';
?>

<style>
/* Shopee-style Tracking Timeline CSS */
.track-line { display: flex; justify-content: space-between; position: relative; margin: 25px 0 15px; }
.track-line::before { content: ''; position: absolute; top: 12px; left: 10%; width: 80%; height: 3px; background: #eee; z-index: 1; }
.track-step { position: relative; z-index: 2; text-align: center; font-size: 0.8rem; color: #888; flex: 1; font-family: 'Barlow', sans-serif; }
.track-step .dot { width: 28px; height: 28px; background: #eee; border-radius: 50%; margin: 0 auto 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #aaa; border: 3px solid #fff; }
.track-step.active { color: #ff3333; font-weight: 600; }
.track-step.active .dot { background: #ff3333; color: #fff; }
.track-step.done .dot { background: #ff3333; color: #fff; }
</style>

<section class="page-hero">
    <span class="eyebrow">My Account</span>
    <h1>Recent Orders</h1>
    <p>Welcome back, <?= htmlspecialchars(currentUsername() ?? '') ?>. Track your purchases below.</p>
</section>

<section>
    <div class="container">
        <?php if (empty($orders)): ?>
            <div class="auth-card" id="no-orders" style="text-align: center; max-width: 500px; margin: 0 auto;">
                <h3>No orders yet</h3>
                <p>Looks like you haven't bought anything from the shop.</p>
                <a href="index.php#featured" class="btn" id="dashboard-shop-now" style="margin-top: 15px;">Go Shopping</a>
            </div>
        <?php else: ?>
            <div id="orders-list" style="max-width: 800px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($orders as $order): ?>
                    <?php
                        $status = (string) $order['status'];
                        $isCancelled = ($status === 'cancelled');

                        // Determine step levels for the progress bar.
                        $stepLevel = 1; // Pending
                        if ($status === 'processing') $stepLevel = 2;
                        if ($status === 'shipped') $stepLevel = 3;
                        if ($status === 'completed') $stepLevel = 4;
                    ?>
                    <div class="order-card" id="order-<?= (int) $order['id'] ?>" style="background: #fff; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <!-- Header -->
                        <div style="display: flex; justify-content: space-between; padding: 15px 20px; border-bottom: 1px solid #eee; background: #fafafa;">
                            <div>
                                <strong>Order ID: #<?= (int) $order['id'] ?></strong>
                                <span style="color: #888; font-size: 0.85rem; margin-left: 10px;"><?= date('M d, Y', strtotime((string) $order['created_at'])) ?></span>
                            </div>
                            <div id="order-status-<?= (int) $order['id'] ?>" class="order-status" style="text-transform: uppercase; font-size: 0.85rem; font-weight: bold; color: <?= $isCancelled ? '#dc3545' : '#ff3333'; ?>;">
                                <?= htmlspecialchars($status) ?>
                            </div>
                        </div>

                        <!-- Visual Tracker (Only show if not cancelled) -->
                        <?php if (!$isCancelled): ?>
                        <div class="track-line">
                            <div class="track-step <?= $stepLevel >= 1 ? 'active done' : '' ?>">
                                <div class="dot">1</div> Placed
                            </div>
                            <div class="track-step <?= $stepLevel >= 2 ? 'active done' : '' ?>">
                                <div class="dot">2</div> Preparing
                            </div>
                            <div class="track-step <?= $stepLevel >= 3 ? 'active done' : '' ?>">
                                <div class="dot">3</div> Shipped
                            </div>
                            <div class="track-step <?= $stepLevel >= 4 ? 'active done' : '' ?>">
                                <div class="dot">✓</div> Delivered
                            </div>
                        </div>
                        <div style="border-bottom: 1px dashed #eee; margin: 0 20px;"></div>
                        <?php endif; ?>

                        <!-- Items -->
                        <div style="padding: 10px 20px;">
                            <?php if (isset($itemsByOrder[$order['id']])): ?>
                                <?php foreach ($itemsByOrder[$order['id']] as $item): ?>
                                    <div style="display: flex; justify-content: space-between; padding: 10px 0;">
                                        <div>
                                            <h4 style="margin: 0; font-size: 1rem;"><?= htmlspecialchars((string) $item['product_name']) ?></h4>
                                            <span style="color: #666; font-size: 0.85rem;">Qty: <?= (int) $item['quantity'] ?></span>
                                        </div>
                                        <div style="font-weight: bold;">₱<?= number_format((float) $item['price'], 2) ?></div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Footer -->
                        <div style="padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; background: #fafafa; border-top: 1px solid #eee;">
                            <div>
                                <?php if ($status === 'pending'): ?>
                                    <form method="POST" id="cancel-form-<?= (int) $order['id'] ?>" data-confirm="Are you sure you want to cancel this order?">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                        <input type="hidden" name="cancel_order_id" value="<?= (int) $order['id'] ?>">
                                        <button type="submit" id="cancel-order-<?= (int) $order['id'] ?>" style="background: none; border: none; color: #888; text-decoration: underline; cursor: pointer; font-family: 'Barlow', sans-serif;">Cancel Order</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div style="font-size: 1rem;">
                                Order Total: <strong id="order-total-<?= (int) $order['id'] ?>" style="color: #ff3333; font-size: 1.3rem;">₱<?= number_format((float) $order['total_amount'], 2) ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
