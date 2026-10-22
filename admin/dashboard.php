<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin('../login.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck(postString('csrf_token'))) {
        setFlash('error', 'Your session expired. Please try again.');
        header('Location: dashboard.php');
        exit;
    }

    $action    = postString('action');
    $orderId   = filter_var($_POST['order_id'] ?? null, FILTER_VALIDATE_INT);
    $userId    = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT);
    $messageId = filter_var($_POST['message_id'] ?? null, FILTER_VALIDATE_INT);

    try {
        if ($action === 'update_order') {
            if ($orderId === false || $orderId < 1) {
                setFlash('error', 'Invalid order.');
            } else {
                $status = postString('status');
                $allowedStatuses = ['pending', 'processing', 'shipped', 'completed', 'cancelled'];

                if (!in_array($status, $allowedStatuses, true)) {
                    setFlash('error', 'Invalid order status.');
                } else {
                    $exists = $pdo->prepare('SELECT id FROM orders WHERE id = ?');
                    $exists->execute([$orderId]);

                    if (!$exists->fetch()) {
                        setFlash('error', 'Order not found.');
                    } else {
                        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
                        $stmt->execute([$status, $orderId]);
                        setFlash('success', 'Order status updated.');
                    }
                }
            }
        } elseif ($action === 'delete_order') {
            if ($orderId === false || $orderId < 1) {
                setFlash('error', 'Invalid order.');
            } else {
                $stmt = $pdo->prepare('DELETE FROM orders WHERE id = ?');
                $stmt->execute([$orderId]);
                setFlash(
                    $stmt->rowCount() > 0 ? 'success' : 'error',
                    $stmt->rowCount() > 0 ? 'Order deleted permanently.' : 'Order not found.'
                );
            }
        } elseif ($action === 'delete_user') {
            if ($userId === false || $userId < 1) {
                setFlash('error', 'Invalid customer account.');
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
                $stmt->execute([$userId]);
                setFlash(
                    $stmt->rowCount() > 0 ? 'success' : 'error',
                    $stmt->rowCount() > 0 ? 'Customer account deleted.' : 'Customer account not found or cannot be deleted.'
                );
            }
        } elseif ($action === 'delete_message') {
            if ($messageId === false || $messageId < 1) {
                setFlash('error', 'Invalid contact message.');
            } else {
                $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = ?');
                $stmt->execute([$messageId]);
                setFlash(
                    $stmt->rowCount() > 0 ? 'success' : 'error',
                    $stmt->rowCount() > 0 ? 'Contact message deleted.' : 'Contact message not found.'
                );
            }
        } else {
            setFlash('error', 'Unknown admin action.');
        }
    } catch (Throwable $e) {
        error_log('RS8 admin action failed: ' . $e->getMessage());
        setFlash('error', 'The action could not be completed. Please try again.');
    }

    header('Location: dashboard.php');
    exit;
}

$orders   = $pdo->query('SELECT orders.*, users.username FROM orders JOIN users ON orders.user_id = users.id ORDER BY orders.created_at DESC')->fetchAll();
$users    = $pdo->query('SELECT id, username, email, role, created_at FROM users ORDER BY created_at DESC')->fetchAll();
$messages = $pdo->query('SELECT id, name, email, message, submitted_at FROM contact_messages ORDER BY submitted_at DESC')->fetchAll();

$stmtItems = $pdo->query('SELECT order_id, product_name, quantity FROM order_items');
$itemsByOrder = [];
while ($row = $stmtItems->fetch()) {
    $itemsByOrder[$row['order_id']][] = $row;
}

$assetPath  = '../';
$pageTitle  = 'Admin Dashboard';
$activePage = 'admin';
include __DIR__ . '/../includes/header.php';
?>

<section class="page-hero">
    <span class="eyebrow">Admin</span>
    <h1>Dashboard</h1>
    <p>Signed in as <?= htmlspecialchars(currentUsername() ?? '') ?>. You have full control over customer data.</p>
</section>

<section>
    <div class="container">
        <div class="section-title" style="text-align:left; margin-bottom:24px; margin-top:20px;">
            <h2 style="font-size:24px;">Store Orders (<?= count($orders) ?>)</h2>
        </div>
        <div class="table-wrap" id="admin-orders">
            <table class="admin-table">
                <thead>
                    <tr><th>Order Info</th><th>Delivery Details</th><th>Items Ordered</th><th>Total / Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php if (!$orders): ?>
                    <tr><td colspan="5">No orders yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($orders as $o): ?>
                    <tr id="admin-order-row-<?= (int) $o['id'] ?>">
                        <td style="font-size: 0.9rem;">
                            <strong>#<?= (int) $o['id'] ?></strong><br>
                            <?= htmlspecialchars((string) $o['username']) ?><br>
                            <span style="color: #666; font-size: 0.8rem;"><?= htmlspecialchars((string) $o['created_at']) ?></span>
                        </td>
                        <td style="font-size: 0.9rem;">
                            <strong>Tel:</strong> <?= htmlspecialchars((string) $o['contact_number']) ?><br>
                            <strong>To:</strong> <?= htmlspecialchars((string) $o['shipping_address']) ?>
                        </td>
                        <td>
                            <ul style="margin: 0; padding-left: 15px; font-size: 0.85rem;">
                                <?php if (isset($itemsByOrder[$o['id']])): ?>
                                    <?php foreach ($itemsByOrder[$o['id']] as $item): ?>
                                        <li><?= (int) $item['quantity'] ?>x <?= htmlspecialchars((string) $item['product_name']) ?></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </td>
                        <td>
                            <span style="font-weight: bold; color: #ff3333;">₱<?= number_format((float) $o['total_amount'], 2) ?></span><br>
                            <span id="admin-order-status-<?= (int) $o['id'] ?>" style="font-size: 0.85rem; text-transform: uppercase;"><?= htmlspecialchars((string) $o['status']) ?></span>
                        </td>
                        <td>
                            <form method="POST" id="status-form-<?= (int) $o['id'] ?>" style="display:flex; flex-direction: column; gap:5px;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="action" value="update_order">
                                <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                                <select name="status" id="status-<?= (int) $o['id'] ?>" class="order-status-select" style="padding: 2px;" aria-label="Order status for order <?= (int) $o['id'] ?>">
                                    <option value="pending" <?= $o['status'] === 'pending' ? 'selected' : '' ?>>Pending (Placed)</option>
                                    <option value="processing" <?= $o['status'] === 'processing' ? 'selected' : '' ?>>Processing (Preparing)</option>
                                    <option value="shipped" <?= $o['status'] === 'shipped' ? 'selected' : '' ?>>Shipped (In Transit)</option>
                                    <option value="completed" <?= $o['status'] === 'completed' ? 'selected' : '' ?>>Completed (Delivered)</option>
                                    <option value="cancelled" <?= $o['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="btn save-order-btn" id="save-status-<?= (int) $o['id'] ?>" style="padding: 2px 5px; font-size: 12px;">Save Status</button>
                            </form>
                            <form method="POST" id="delete-order-form-<?= (int) $o['id'] ?>" data-confirm="Are you sure you want to delete this order entirely?" style="margin-top: 5px;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="action" value="delete_order">
                                <input type="hidden" name="order_id" value="<?= (int) $o['id'] ?>">
                                <button type="submit" class="btn delete-order-btn" id="delete-order-<?= (int) $o['id'] ?>" style="background: #dc3545; width: 100%; padding: 2px 5px; font-size: 12px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- REGISTERED USERS TABLE -->
<section class="feature-strip">
    <div class="container">
        <div class="section-title" style="text-align:left; margin-bottom:24px;">
            <h2 style="font-size:24px;">Registered Customers (<?= count($users) ?>)</h2>
        </div>
        <div class="table-wrap" id="admin-users">
            <table class="admin-table">
                <thead>
                    <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Joined</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php if (!$users): ?>
                    <tr><td colspan="6">No users yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($users as $u): ?>
                    <tr id="admin-user-row-<?= (int) $u['id'] ?>">
                        <td><?= (int) $u['id'] ?></td>
                        <td><?= htmlspecialchars((string) $u['username']) ?></td>
                        <td><?= htmlspecialchars((string) $u['email']) ?></td>
                        <td><span class="role-badge role-<?= htmlspecialchars((string) $u['role']) ?>"><?= htmlspecialchars((string) $u['role']) ?></span></td>
                        <td><?= htmlspecialchars((string) $u['created_at']) ?></td>
                        <td>
                            <?php if ($u['role'] !== 'admin'): ?>
                            <form method="POST" id="delete-user-form-<?= (int) $u['id'] ?>" data-confirm="Are you sure you want to delete this customer?">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= (int) $u['id'] ?>">
                                <button type="submit" class="btn delete-user-btn" id="delete-user-<?= (int) $u['id'] ?>" style="background: #dc3545; padding: 2px 8px; font-size: 12px;">Delete</button>
                            </form>
                            <?php else: ?>
                                <span style="font-size: 12px; color: #888;">Admin (Locked)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- CONTACT MESSAGES TABLE -->
<section>
    <div class="container">
        <div class="section-title" style="text-align:left; margin-bottom:24px;">
            <h2 style="font-size:24px;">Contact Messages (<?= count($messages) ?>)</h2>
        </div>
        <div class="table-wrap" id="admin-messages">
            <table class="admin-table">
                <thead>
                    <tr><th>Name</th><th>Email</th><th>Message</th><th>Sent</th><th>Action</th></tr>
                </thead>
                <tbody>
                <?php if (!$messages): ?>
                    <tr><td colspan="5">No messages yet.</td></tr>
                <?php endif; ?>
                <?php foreach ($messages as $m): ?>
                    <tr id="admin-message-row-<?= (int) $m['id'] ?>">
                        <td><?= htmlspecialchars((string) $m['name']) ?></td>
                        <td><?= htmlspecialchars((string) $m['email']) ?></td>
                        <td><?= nl2br(htmlspecialchars((string) $m['message'])) ?></td>
                        <td><?= htmlspecialchars((string) $m['submitted_at']) ?></td>
                        <td>
                            <form method="POST" id="delete-message-form-<?= (int) $m['id'] ?>" data-confirm="Delete this message?">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                <input type="hidden" name="action" value="delete_message">
                                <input type="hidden" name="message_id" value="<?= (int) $m['id'] ?>">
                                <button type="submit" class="btn delete-message-btn" id="delete-message-<?= (int) $m['id'] ?>" style="background: #6c757d; padding: 2px 8px; font-size: 12px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
