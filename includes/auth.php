<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Is anyone logged in? */
function isLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

/** Is the logged-in user an admin? */
function isAdmin(): bool
{
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

/** Username of the logged-in user, or null. */
function currentUsername(): ?string
{
    return $_SESSION['username'] ?? null;
}

/** Redirect to $redirect unless a user is logged in. */
function requireLogin(string $redirect = 'login.php'): void
{
    if (!isLoggedIn()) {
        header('Location: ' . $redirect);
        exit;
    }
}

/** Redirect to $redirect unless the logged-in user is an admin. */
function requireAdmin(string $redirect = 'login.php'): void
{
    if (!isAdmin()) {
        header('Location: ' . $redirect);
        exit;
    }
}

/** Get (or create) a CSRF token for the current session. */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Check a submitted CSRF token against the session's token. */
function csrfCheck(?string $token): bool
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string) $token);
}

/** Store a one-shot message for the next page load. */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = [
        'type'    => $type === 'error' ? 'error' : 'success',
        'message' => $message,
    ];
}

/** Read and clear the one-shot flash message. */
function getFlash(): ?array
{
    if (empty($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        unset($_SESSION['flash']);

        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

/** Safely read a trimmed string from POST. */
function postString(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

/** Safely read an untrimmed string from POST (passwords). */
function postRawString(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? $value : '';
}

/** Safely read a trimmed string from the query string. */
function queryString(string $key): string
{
    $value = $_GET[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}
