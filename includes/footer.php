</main>

<footer>
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col footer-brand">
                <a href="<?= $assetPath ?>index.php" class="logo"><img src="<?= $assetPath ?>images/rs8logo.webp" alt="RS8 Racing Logo"></a>
                <p>Taiwan-engineered CVT and drivetrain parts, built for riders who push past stock.</p>
            </div>
            <div class="footer-col">
                <h4>Navigate</h4>
                <a href="<?= $assetPath ?>index.php">Home</a>
                <a href="<?= $assetPath ?>about.php">About Us</a>
                <a href="<?= $assetPath ?>contact.php">Contact</a>
            </div>
            <div class="footer-col">
                <h4>Get In Touch</h4>
                <a href="mailto:earllawrencellego@gmail.com">earllawrencellego@gmail.com</a>
                <a href="tel:+639913484223">0991 348 4223</a>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; 2026 RS8 Racing. All rights reserved.</span>
            <span>Taiwan Speed Factory</span>
        </div>
    </div>
</footer>

<div id="site-toast" class="toast" role="status" aria-live="polite" hidden></div>

<div class="modal-backdrop" id="confirm-dialog" hidden>
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-message">
        <h3 id="confirm-title">Please confirm</h3>
        <p id="confirm-message">Are you sure?</p>
        <div class="modal-actions">
            <button type="button" class="btn btn-outline" id="confirm-cancel">Cancel</button>
            <button type="button" class="btn" id="confirm-ok">Yes, continue</button>
        </div>
    </div>
</div>

<script src="<?= $assetPath ?>js/main.js"></script>
</body>
</html>
