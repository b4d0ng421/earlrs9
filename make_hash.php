<?php
/**
 * ONE-TIME HELPER: turns a password into a hash you can paste into phpMyAdmin.
 * It does not touch the database. DELETE THIS FILE after you use it.
 */
$hash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && is_string($_POST['pw'] ?? null) && $_POST['pw'] !== '') {
    $hash = password_hash($_POST['pw'], PASSWORD_DEFAULT);
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Password hash</title></head>
<body style="font-family:sans-serif;max-width:560px;margin:40px auto">
<h2>Make a password hash</h2>
<form method="post">
    <input type="text" name="pw" placeholder="new admin password" style="width:70%;padding:6px" autocomplete="off">
    <button type="submit" style="padding:6px 12px">Generate</button>
</form>
<?php if ($hash !== ''): ?>
    <p>Copy this into <code>users.password_hash</code> for the admin row:</p>
    <textarea readonly rows="3" style="width:100%"><?= htmlspecialchars($hash) ?></textarea>
<?php endif; ?>
<p style="color:#b00"><strong>Delete make_hash.php from htdocs when you are done.</strong></p>
</body></html>
