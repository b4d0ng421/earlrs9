<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/products.php';

$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart)) {
    $cart = [];
}

$cart = rs8_normalize_cart($cart);
$_SESSION['cart'] = $cart;

if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

requireLogin('login.php?next=checkout.php');

$total = 0.0;
foreach ($cart as $item) {
    $total += (float) $item['price'] * (int) $item['quantity'];
}

$error    = '';
$contact  = '';
$province = '';
$city     = '';
$barangay = '';
$postal   = '';
$street   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck(postString('csrf_token'))) {
        $error = 'Your session expired. Please refresh the page and try again.';
    } else {
        $contact  = postString('contact_number');
        $province = postString('province');
        $city     = postString('city');
        $barangay = postString('barangay');
        $postal   = postString('postal');
        $street   = postString('street');

        if (!preg_match('/^09[0-9]{9}$/', $contact)) {
            $error = 'Please enter a valid 11-digit Philippine mobile number starting with 09 (e.g., 09123456789).';
        } elseif ($province === '' || $city === '' || $barangay === '' || $street === '') {
            $error = 'Please fill in all required address fields.';
        } elseif ($postal !== '' && !preg_match('/^[0-9]{4}$/', $postal)) {
            $error = 'Please enter a valid 4-digit postal code.';
        } else {
            $fullAddress = $street . ', Brgy. ' . $barangay . ', ' . $city . ', ' . $province
                . ($postal !== '' ? ' ' . $postal : '');

            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare(
                    "INSERT INTO orders (user_id, total_amount, shipping_address, contact_number, status)
                     VALUES (?, ?, ?, ?, 'pending')"
                );
                $stmt->execute([(int) $_SESSION['user_id'], $total, $fullAddress, $contact]);
                $orderId = (int) $pdo->lastInsertId();

                $itemStmt = $pdo->prepare(
                    'INSERT INTO order_items (order_id, product_id, product_name, price, quantity)
                     VALUES (?, ?, ?, ?, ?)'
                );
                foreach ($cart as $id => $item) {
                    $itemStmt->execute([
                        $orderId,
                        $id,
                        $item['name'],
                        (float) $item['price'],
                        (int) $item['quantity'],
                    ]);
                }

                $pdo->commit();

                unset($_SESSION['cart']);
                setFlash('success', 'Order placed successfully.');
                header('Location: dashboard.php');
                exit;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }

                error_log('RS8 checkout failed: ' . $e->getMessage());
                $error = 'We could not place your order. Please try again.';
            }
        }
    }
}

$pageTitle  = 'Checkout';
$activePage = 'checkout';
include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <span class="eyebrow">Checkout</span>
    <h1>Finalize Order</h1>
</section>

<section>
    <div class="container">
        <div class="auth-card" style="max-width: 600px; margin: 0 auto;">
            <h3>Delivery Details</h3>
            <p class="cart-note">Order total: <strong id="checkout-total">₱<?= number_format($total, 2) ?></strong> — payment on delivery (COD).</p>

            <?php if ($error): ?>
                <p class="form-message form-error" id="checkout-error" role="alert"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" id="checkout-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">

                <div class="field">
                    <label for="contact_number">Phone Number</label>
                    <input type="text" id="contact_number" name="contact_number" value="<?= htmlspecialchars($contact) ?>" required pattern="09[0-9]{9}" inputmode="numeric" maxlength="11" placeholder="09XXXXXXXXX (11 digits)" autocomplete="tel" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="field" style="flex: 1;">
                        <label for="province">Province</label>
                        <input type="text" id="province" name="province" value="<?= htmlspecialchars($province) ?>" required placeholder="e.g., Metro Manila" autocomplete="address-level1">
                    </div>
                    <div class="field" style="flex: 1;">
                        <label for="city">City / Municipality</label>
                        <input type="text" id="city" name="city" value="<?= htmlspecialchars($city) ?>" required placeholder="e.g., Quezon City" autocomplete="address-level2">
                    </div>
                </div>

                <div style="display: flex; gap: 15px;">
                    <div class="field" style="flex: 1;">
                        <label for="barangay">Barangay</label>
                        <input type="text" id="barangay" name="barangay" value="<?= htmlspecialchars($barangay) ?>" required placeholder="e.g., San Antonio" autocomplete="address-level3">
                    </div>
                    <div class="field" style="flex: 1;">
                        <label for="postal">Postal Code</label>
                        <input type="text" id="postal" name="postal" value="<?= htmlspecialchars($postal) ?>" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" placeholder="e.g., 1105" autocomplete="postal-code" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    </div>
                </div>

                <div class="field">
                    <label for="street">Street Name, Building, House No.</label>
                    <textarea id="street" name="street" required placeholder="Street Name, Building, House No." style="height: 80px;" autocomplete="street-address"><?= htmlspecialchars($street) ?></textarea>
                </div>

                <button type="submit" class="btn" id="place-order" style="width: 100%; margin-top: 10px;">Confirm &amp; Place Order (COD)</button>
            </form>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
