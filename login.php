<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$allowedNext = ['index.php', 'about.php', 'contact.php', 'cart.php', 'checkout.php', 'dashboard.php'];
$next = postString('next') !== '' ? postString('next') : queryString('next');
if (!in_array($next, $allowedNext, true)) {
    $next = '';
}

// Already logged in? Send them where they belong.
if (isLoggedIn()) {
    $redirect = $next !== ''
        ? $next
        : (isAdmin() ? 'admin/dashboard.php' : 'dashboard.php');

    header('Location: ' . $redirect);
    exit;
}

$error    = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck(postString('csrf_token'))) {
        $error = 'Your session expired. Please try again.';
    } else {
        $username = postString('username');
        $password = postRawString('password');

        if ($username === '' || $password === '') {
            $error = 'Please enter both your username and password.';
        } else {
            $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, (string) $user['password_hash'])) {
                session_regenerate_id(true);

                $_SESSION['user_id']  = (int) $user['id'];
                $_SESSION['username'] = (string) $user['username'];
                $_SESSION['role']     = (string) $user['role'];

                setFlash('success', 'Welcome back, ' . $user['username'] . '.');

                $redirect = $next !== ''
                    ? $next
                    : ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php');

                header('Location: ' . $redirect);
                exit;
            }

            $error = 'Invalid username or password.';
        }
    }
}

$logoutMessage = queryString('logged_out') === '1' ? 'You have been logged out.' : '';

$pageTitle  = 'Login';
$activePage = 'login';
include __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <span class="eyebrow">Account</span>
    <h1>Log In</h1>
    <p>Access your RS8 account, or the admin dashboard.</p>
</section>

<section>
    <div class="container">
        <div class="auth-card">
            <?php if ($logoutMessage): ?>
                <p class="form-message form-success" id="logout-message" role="status"><?= htmlspecialchars($logoutMessage) ?></p>
            <?php endif; ?>

            <?php if ($error): ?>
                <p class="form-message form-error" id="login-error" role="alert"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="post" id="login-form" novalidate>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                <?php if ($next !== ''): ?>
                    <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
                <?php endif; ?>

                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" autocomplete="username" required>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn" id="login-submit">Log In</button>
            </form>

            <p class="auth-alt">Don't have an account? <a href="register.php" id="register-link">Register here</a></p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
