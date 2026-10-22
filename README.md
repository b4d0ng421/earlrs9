# RS8 Racing — PHP + MySQL Backend

## What's in here
```
earlrs9/
├── index.php, about.php, contact.php   ← storefront pages
├── login.php, register.php, logout.php ← account system
├── dashboard.php                       ← customer order history
├── cart.php, cart_action.php           ← guest cart + add/remove actions
├── checkout.php                        ← COD checkout
├── health.php                          ← JSON database health check
├── admin/dashboard.php                 ← admin-only panel (orders, users, messages)
├── config/db.php                       ← database connection settings
├── includes/                           ← auth, product catalogue, header/footer
├── database/rs8.sql                    ← fresh-install schema
├── database/migrations/                ← upgrades for existing databases
├── tests/earlrs9.side                  ← Selenium IDE suite
├── css/style.css
├── js/main.js
└── images/
```

## Setup (local — XAMPP / MAMP / Laragon)
1. Copy this whole folder into your server's web root (for XAMPP: `htdocs/earlrs9`).
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open phpMyAdmin, click **Import**, and import `database/rs8.sql`. This creates the `rs8_racing` database and seeds the admin account.
4. If the database already existed from an older copy of this project, run `database/migrations/2026-10-22_orders_status.sql` once. It adds the `processing` and `shipped` order statuses.
5. Check `config/db.php` if your local MySQL setup differs (XAMPP defaults are `localhost`, user `root`, empty password).
6. Visit `http://localhost/earlrs9/index.php`.
7. Confirm the database connection at `http://localhost/earlrs9/health.php` — it should return:
   ```json
   {"status":"ok","database":"rs8_racing"}
   ```

## Setup (real hosting / cPanel or InfinityFree)
1. In the hosting control panel, create a database and a database user, and give that user full privileges on the database.
2. Open **phpMyAdmin**, select the new database, click **Import**, and upload `database/rs8-infinityfree.sql`. That file deliberately has no `CREATE DATABASE`/`USE` statements, so it works when the host has already created the database and prefixed its name. Do not import `database/rs8.sql` unchanged on shared hosting.
3. Edit `config/db.php` with the real host, database name, username, and password from the control panel. On InfinityFree the host is `sqlXXX.infinityfree.com` (not `localhost`) and the database/user names are prefixed.
4. Upload only the production files (the PHP pages, `admin/`, `config/`, `includes/`, `css/`, `js/`, and `images/`) to `public_html` or `htdocs`. Do **not** upload `database/`, `tests/`, `README.md`, `layout.txt`, or `.git/`: the tests and README contain the seeded admin password, and the SQL folder is not needed on the live server.

## Logging in
- **Admin** — username `earl`, password `earl123`, at `login.php`. You land on `admin/dashboard.php`, which lists orders, registered users, and contact messages.
- **Customers** — anyone can create an account at `register.php` and log in at `login.php`; they land on `dashboard.php`.
- Guests can browse and add items to the cart. Logging in is required only when they open `checkout.php`.

## Orders
- Checkout is cash on delivery (COD) and creates the order plus its line items in one database transaction.
- New orders start as `pending`. The admin can move them through `pending`, `processing`, `shipped`, `completed`, or `cancelled`.
- Customers can cancel only their own `pending` orders from `dashboard.php`.
- Product names and prices are read from `includes/products.php`, not from hidden form fields, so a visitor cannot tamper with the cart total.

## Run the Selenium IDE tests
Open `tests/earlrs9.side` in Selenium IDE, select **RS8 Racing — Full Selenium IDE suite**, and press Play. See `tests/README.md` for prerequisites, test order, and cleanup.

## Troubleshooting

### "Database connection failed"
This means PHP could not reach MySQL; it is usually not a PHP bug.
1. Start **MySQL** in the XAMPP Control Panel and wait for it to show as running.
2. Open `http://localhost/earlrs9/health.php`. A `200` with `"status":"ok"` means the app can connect; a `503` means MySQL is still down or the credentials/database name are wrong.
3. If MySQL refuses to start, check whether another MySQL service (for example `MySQL80`) is already using port 3306. Stop it or change XAMPP's port.
4. Check `config/db.php` against the database/user you actually created.
5. Details are written to the PHP/Apache error log; visitors see only a generic message.

### Selenium IDE does nothing after clicking Add to Cart
The site no longer uses native `alert()` / `prompt()` / `confirm()` dialogs. Add-to-cart uses inline feedback, a toast, and the cart counter. Wait for `id=site-toast` to become visible before asserting the inline message.

### Images or video return 404
The storefront uses local assets under `images/`. Make sure the `images/` folder was copied with the rest of the project, including `rs8-hero.mp4`.

## Please do this before going live
1. **Change the admin password.** `earl123` is fine for testing, not for production. Easiest way: log in, then run this once in phpMyAdmin's SQL tab with a password of your choice:
   ```sql
   UPDATE users SET password_hash = '<paste a new hash here>' WHERE username = 'earl';
   ```
   Generate that hash by running `php -r "echo password_hash('YOUR-NEW-PASSWORD', PASSWORD_DEFAULT);"` from a terminal.
2. **Serve the site over HTTPS.** Login and registration send passwords in the request body — without HTTPS those are readable in transit.
3. **Don't commit `config/db.php` with real credentials to a public GitHub repo.** If you're using version control, add it to `.gitignore` and keep a `config/db.example.php` template instead.

## How the security pieces work
- Passwords are never stored or compared as plain text — `password_hash()` / `password_verify()` (bcrypt) handle that.
- Every database query uses PDO prepared statements, so form input can't be used to manipulate SQL.
- Every POST form includes a CSRF token tied to the session, checked before anything is saved.
- All output (`htmlspecialchars()`) is escaped before it's echoed back into HTML, to prevent stored/reflected XSS from usernames, messages, etc.
- `admin/dashboard.php` calls `requireAdmin()` before it queries or renders anything — a logged-in customer who guesses the URL is redirected to `login.php`.
- Cart and checkout totals are recalculated from the server-side catalogue, so changing product IDs or prices in the browser has no effect.
- Successful POST actions redirect back with a one-shot flash message (POST-redirect-GET), which prevents duplicate form submissions when a page is refreshed.
