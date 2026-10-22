<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/products.php';

function cartRespond(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    cartRespond(['status' => 'error', 'message' => 'Invalid request method.'], 405);
}

$action = postString('action');
$token  = postString('csrf_token');

if (!csrfCheck($token)) {
    if ($action === 'remove') {
        setFlash('error', 'Your session expired. Please try again.');
        header('Location: cart.php');
        exit;
    }

    cartRespond([
        'status'  => 'error',
        'message' => 'Your session expired. Please refresh the page.',
    ], 419);
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($action === 'add') {
    $id      = postString('id');
    $product = rs8_product($id);

    if ($product === null) {
        cartRespond([
            'status'  => 'error',
            'message' => 'That product could not be found.',
        ], 404);
    }

    $quantityRaw = $_POST['quantity'] ?? 1;
    $quantity    = is_numeric($quantityRaw) ? (int) $quantityRaw : 1;
    $quantity    = max(1, min(99, $quantity));

    if (isset($_SESSION['cart'][$id]) && is_array($_SESSION['cart'][$id])) {
        $currentQuantity = (int) ($_SESSION['cart'][$id]['quantity'] ?? 0);
        $_SESSION['cart'][$id]['quantity'] = min(99, $currentQuantity + $quantity);
    } else {
        $_SESSION['cart'][$id] = [
            'name'     => $product['name'],
            'price'    => (float) $product['price'],
            'image'    => $product['image'],
            'quantity' => $quantity,
        ];
    }

    // Catalogue values always win over anything sent by the browser.
    $_SESSION['cart'][$id]['name']  = $product['name'];
    $_SESSION['cart'][$id]['price'] = (float) $product['price'];
    $_SESSION['cart'][$id]['image'] = $product['image'];

    $totalItems = 0;
    foreach ($_SESSION['cart'] as $item) {
        if (is_array($item)) {
            $totalItems += (int) ($item['quantity'] ?? 0);
        }
    }

    cartRespond([
        'status'     => 'success',
        'message'    => 'Item added to cart.',
        'item'       => [
            'id'   => $id,
            'name' => $product['name'],
        ],
        'totalItems' => $totalItems,
        'cartCount'  => count($_SESSION['cart']),
    ]);
}

if ($action === 'remove') {
    $id = postString('id');

    if ($id !== '' && isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
        setFlash('success', 'Item removed from cart.');
    } else {
        setFlash('error', 'That item is no longer in your cart.');
    }

    header('Location: cart.php');
    exit;
}

cartRespond([
    'status'  => 'error',
    'message' => 'Unknown cart action.',
], 400);
