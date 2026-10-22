# RS8 Racing — Selenium IDE suite

`earlrs9.side` is a Selenium IDE 3/4 project with eight tests covering the main storefront flows. The tests use stable element IDs instead of brittle text/position selectors.

## Prerequisites

1. Start **Apache** and **MySQL** in XAMPP.
2. Make sure the database exists. For a fresh install, import `database/rs8.sql`. If your database was created from the old schema, run `database/migrations/2026-10-22_orders_status.sql` once so `orders.status` accepts `processing` and `shipped`.
3. Confirm the health check returns JSON:
   `http://localhost/earlrs9/health.php`
   Expected: `{"status":"ok","database":"rs8_racing"}`
4. Admin credentials: **earl** / **earl123**.
5. The project URL in the suite is `http://localhost/earlrs9/` (trailing slash required). If your folder name or host differs, update the project URL in Selenium IDE (or `url` in the `.side` file); the `open` targets are relative so they will follow the new folder.

## Run the suite

1. Open Selenium IDE in Chrome or Firefox.
2. Choose **Open Project** and select `tests/earlrs9.side`.
3. Select the suite **RS8 Racing — Full Selenium IDE suite**.
4. Click **Play**.

Run the tests in the listed order. The suite is intentionally ordered so the checkout test creates an order before the admin test changes its status.

Each storefront test starts with `open logout.php`, which clears the PHP session and cart before the test begins. This keeps guest tests independent without relying on browser cookie commands.

## Tests

| Test | What it verifies |
| --- | --- |
| Health check | PHP can connect to MySQL and the expected database is selected. |
| Home, navigation, and product details | The storefront loads, product cards/details work, and nav links reach About, Contact, and the empty cart. |
| Guest cart add and remove | A guest can set a quantity, add an item, see the inline confirmation/cart count, and remove it. |
| Contact form | A message is inserted and the success flash appears. |
| Register, login, and logout | Registration redirects to login with the success flash, login redirects to the dashboard, and logout redirects to login. |
| Invalid login | A bad password shows the inline error instead of a native dialog. |
| Checkout creates an order | A new buyer logs in, adds a product, fills the delivery form, and places a COD order. |
| Admin updates the newest order | The admin dashboard loads and the newest order's status is changed to shipped. |

## Test data and dependencies

The contact, registration, and checkout tests generate unique data using `Date.now()`:

- Contact messages: `sel_contact_...@example.com`
- Customer accounts: `sel_qa_...`
- Checkout buyer accounts: `sel_buyer_...`

The admin test updates the **newest** order, which is the one created by the checkout test. If you run the admin test by itself, place an order first or it will fail with "No orders yet".

The tests do **not** use native `alert()`, `prompt()`, or `confirm()` dialogs. Destructive actions use the site's custom confirmation modal, so commands such as `choose ok on next confirmation` are not needed.

## Clean up test data

From the project root (PowerShell):

```powershell
Get-Content .\tests\cleanup.sql | & 'C:\xampp\mysql\bin\mysql.exe' -u root rs8_racing
```

Or from `cmd.exe`:

```bat
C:\xampp\mysql\bin\mysql.exe -u root rs8_racing < tests\cleanup.sql
```

This removes the `sel_...` test customers, their orders (cascade), and contact messages. It is intended for local development only.
