<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$error   = '';
$name    = '';
$email   = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfCheck(postString('csrf_token'))) {
        $error = 'Your session expired. Please refresh and try again.';
    } else {
        $name    = postString('name');
        $email   = postString('email');
        $message = postString('message');

        if ($name === '' || $email === '' || $message === '') {
            $error = 'Please fill in every field.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $message]);

            setFlash('success', 'Thanks! Your message has been sent. We will get back to you shortly.');
            header('Location: contact.php');
            exit;
        }
    }
}

$pageTitle  = 'Contact';
$activePage = 'contact';
include __DIR__ . '/includes/header.php';
?>

    <section class="page-hero">
        <span class="eyebrow">Contact</span>
        <h1>Talk to the shop.</h1>
        <p>Fitment questions, bulk orders, or just want to know if a part suits your build — reach out directly.</p>
    </section>

    <section>
        <div class="container">
            <div class="contact-layout">

                <div class="contact-card">
                    <div class="hazard-rule"></div>
                    <div class="contact-card-body">
                        <h3>Contact Details</h3>

                        <div class="contact-line">
                            <span class="ic">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                            </span>
                            <div>
                                <span class="label">Email</span>
                                <a href="mailto:earllawrencellego@gmail.com">earllawrencellego@gmail.com</a>
                            </div>
                        </div>

                        <div class="contact-line">
                            <span class="ic">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.68 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.32 1.85.55 2.81.68A2 2 0 0 1 22 16.92z"/></svg>
                            </span>
                            <div>
                                <span class="label">Phone</span>
                                <a href="tel:+639913484223">0991 348 4223</a>
                            </div>
                        </div>

                        <div class="contact-line">
                            <span class="ic">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 3.5"/></svg>
                            </span>
                            <div>
                                <span class="label">Hours</span>
                                <span class="value">Mon–Sat, 9:00 AM – 6:00 PM</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-card">
                    <h3>Send a Message</h3>
                    <p>Fill this out and it'll go straight to the shop.</p>

                    <?php if ($error): ?>
                        <p class="form-message form-error" id="contact-error" role="alert"><?= htmlspecialchars($error) ?></p>
                    <?php endif; ?>

                    <form method="post" id="contact-form" novalidate>
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrfToken()) ?>">
                        <div class="field">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" placeholder="Your name" required>
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="you@email.com" required>
                        </div>
                        <div class="field">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" placeholder="Tell us what part or bike you're working with..." required><?= htmlspecialchars($message) ?></textarea>
                        </div>
                        <button type="submit" class="btn" id="contact-submit">Send Message</button>
                    </form>
                </div>

            </div>
        </div>
    </section>

<?php include __DIR__ . '/includes/footer.php'; ?>
