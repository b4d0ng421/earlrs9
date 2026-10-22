<?php
require_once __DIR__ . '/auth.php';

$assetPath  = $assetPath  ?? '';
$pageTitle  = $pageTitle  ?? 'RS8 Racing';
$activePage = $activePage ?? '';

function navClass(string $page, string $active): string
{
    return $page === $active ? ' class="active"' : '';
}

$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $cartItem) {
        if (is_array($cartItem)) {
            $cartCount += (int) ($cartItem['quantity'] ?? 0);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= htmlspecialchars(csrfToken()) ?>">
<title><?= htmlspecialchars($pageTitle) ?> | RS8 Racing</title>
<link rel="icon" type="image/webp" href="<?= $assetPath ?>images/rs8icon.webp">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Barlow:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $assetPath ?>css/style.css">
</head>
<body>

<header class="site-header" id="site-header">
    <a href="<?= $assetPath ?>index.php" class="logo"><img src="<?= $assetPath ?>images/rs8logo.webp" alt="RS8 Racing Logo"></a>
    <button class="nav-toggle" id="nav-toggle" aria-label="Toggle menu" aria-expanded="false">
        <span></span><span></span><span></span>
    </button>
    <nav id="site-nav">
        <ul>
            <li><a href="<?= $assetPath ?>index.php" id="nav-home" data-testid="nav-home"<?= navClass('home', $activePage) ?>>Home</a></li>
            <li><a href="<?= $assetPath ?>about.php" id="nav-about" data-testid="nav-about"<?= navClass('about', $activePage) ?>>About Us</a></li>
            <li><a href="<?= $assetPath ?>contact.php" id="nav-contact" data-testid="nav-contact"<?= navClass('contact', $activePage) ?>>Contact</a></li>
            <li><a href="<?= $assetPath ?>cart.php" id="nav-cart" data-testid="nav-cart"<?= navClass('cart', $activePage) ?>>Cart (<span id="cart-counter" data-testid="cart-counter" aria-live="polite"><?= (int) $cartCount ?></span>)</a></li>
            <?php if (isLoggedIn()): ?>
                <li><a href="<?= $assetPath ?>dashboard.php" id="nav-dashboard" data-testid="nav-dashboard"<?= navClass('dashboard', $activePage) ?>><?= htmlspecialchars(currentUsername() ?? '') ?></a></li>
                <?php if (isAdmin()): ?>
                    <li><a href="<?= $assetPath ?>admin/dashboard.php" id="nav-admin" data-testid="nav-admin"<?= navClass('admin', $activePage) ?>>Admin</a></li>
                <?php endif; ?>
                <li><a href="<?= $assetPath ?>logout.php" id="nav-logout" data-testid="nav-logout">Logout</a></li>
            <?php else: ?>
                <li><a href="<?= $assetPath ?>login.php" id="nav-login" data-testid="nav-login"<?= navClass('login', $activePage) ?>>Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<div class="hazard-rule"></div>
<main>
<?php $flash = getFlash(); ?>
<?php if ($flash): ?>
    <div class="container">
        <p class="form-message form-<?= $flash['type'] === 'error' ? 'error' : 'success' ?> flash-message" id="flash-message" role="status"><?= htmlspecialchars((string) ($flash['message'] ?? '')) ?></p>
    </div>
<?php endif; ?>
