<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/products.php';

$cart = $_SESSION['cart'] ?? [];
if (!is_array($cart)) {
    $cart = [];
}

// Keep only products that still exist in the catalogue, and use catalogue values.
$cart = rs8_normalize_cart($cart);
$_SESSION['cart'] = $cart;

$pageTitle  = 'My Cart';
$activePage = 'cart';
include __DIR__ . '/includes/header.php';

$total = 0;
?>

<section class="page-hero">
    <span class="eyebrow">Shopping Cart</span>
    <h1>Your Items</h1>
</section>

<section>
    <div class="container">
        <?php if (empty($cart)): ?>
            <div class="auth-card" id="cart-empty" style="text-align: center; max-width: 500px; margin: 0 auto;">
                <h3 id="cart-empty-title">Your cart is empty</h3>
                <p>Looks like you haven't added any parts to your build yet.</p>
                <a href="index.php#featured" class="btn" id="cart-shop-now" style="margin-top: 15px;">Shop Now</a>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-wrap: wrap; gap: 30px;">
                <!-- Cart Items -->
                <div style="flex: 2; min-width: 300px;">
                    <table id="cart-table" style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                        <tr style="border-bottom: 2px solid #333; text-align: left;">
                            <th style="padding: 10px;">Product</th>
                            <th style="padding: 10px;">Price</th>
                            <th style="padding: 10px;">Qty</th>
                            <th style="padding: 10px;">Subtotal</th>
                            <th style="padding: 10px;"></th>
                        </tr>
                        <?php foreach ($cart as $id => $item): ?>
                            <?php $subtotal = (float) $item['price'] * (int) $item['quantity']; $total += $subtotal; ?>
                            <tr id="cart-row-<?= htmlspecialchars($id) ?>" style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 15px; display: flex; align-items: center; gap: 15px;">
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="" style="width: 60px; border-radius: 4px;">
                                    <strong><?= htmlspecialchars($item['name']) ?></strong>
                                </td>
                                <td style="padding: 15px;">₱<?= number_format((float) $item['price'], 2) ?></td>
                                <td id="cart-qty-<?= htmlspecialchars($id) ?>" style="padding: 15px;"><?= (int) $item['quantity'] ?></td>
                                <td id="cart-subtotal-<?= htmlspecialchars($id) ?>" style="padding: 15px; color: #ff3333; font-weight: bold;">₱<?= number_format($subtotal, 2) ?></td>
                                <td style="padding: 15px;">
                                    <form action="cart_action.php" method="POST" id="remove-form-<?= htmlspecialchars($id) ?>">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
                                        <button type="submit" id="remove-<?= htmlspecialchars($id) ?>" style="background: none; border: none; color: #666; text-decoration: underline; cursor: pointer;">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>

                <!-- Checkout Summary -->
                <div style="flex: 1; min-width: 250px; background: #f9f9f9; padding: 25px; border-radius: 8px; height: fit-content; border: 1px solid #ddd;">
                    <h3>Order Summary</h3>
                    <div style="display: flex; justify-content: space-between; margin: 15px 0; border-bottom: 1px solid #ddd; padding-bottom: 15px;">
                        <span>Total:</span>
                        <span id="cart-total" style="font-size: 1.5rem; color: #ff3333; font-weight: bold;">₱<?= number_format($total, 2) ?></span>
                    </div>
                    <a href="checkout.php" class="btn" id="checkout-link" style="display: block; width: 100%; text-align: center;"><?= isLoggedIn() ? 'Proceed to Checkout' : 'Log in to Checkout' ?></a>

                    <?php if (!isLoggedIn()): ?>
                        <p class="cart-note" id="cart-guest-note">You can shop as a guest. Log in when you are ready to check out.</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
