# AAR9SPEED Sales & Inventory System

A web-based sales and inventory management system built for a motorcycle shop (Philippines). Manages customers, products, staff, suppliers, purchase orders, and transactions.

---

## Features

| Module | Description |
|---|---|
| **Dashboard** | Total sales (PHP), transaction count, best-sellers bar chart, live notification bell |
| **Customers** | Full CRUD — name, email, phone, address |
| **Products / Inventory** | Full CRUD with image upload, category/supplier linking, product code, status workflow |
| **Inventory Status** | ACTIVE → INACTIVE trigger at 0 qty; separate views for pending and inactive items |
| **Categories** | CRUD for product categories |
| **Suppliers** | CRUD for supplier companies and contacts |
| **Staff** | Admin-gated CRUD with role assignment (ADMIN / STAFF) |
| **Purchase Orders** | Admin-gated stock replenishment; automatically increments on-hand quantity |
| **Sales / Transactions** | Records customer, staff, item, qty, price, payment, and generates a transaction code |
| **Invoices** | Lists all transactions grouped by transaction code |
| **Receipts** | Printable receipt with itemized totals, payment, and change |
| **Notifications** | AJAX-polled dropdown showing depleted (<6 qty), pending, and inactive items |
| **Account Update** | Logged-in staff can update their own profile |

---

## Tech Stack

- **Backend**: PHP 8.1, PDO (MariaDB/MySQL)
- **Frontend**: Bootstrap 5.2, jQuery 3.6.3, Chart.js, DataTables, SweetAlert, Particles.js
- **Database**: MariaDB 10.4 / MySQL

---

## Requirements

- PHP 8.1+
- MySQL / MariaDB
- A web server (Apache with mod_rewrite, or Nginx, or XAMPP/Laragon locally)

---

## Setup

1. **Clone or copy** the project into your web server's document root (e.g., `htdocs/` for XAMPP).

2. **Import the database** — import the SQL dump into a database named `aar9speed`:
   ```bash
   mysql -u root -p aar9speed < aar9speed.sql
   ```

3. **Configure database credentials** — copy `.env.example` to `.env` and fill in your values:
   ```
   DB_HOST=localhost
   DB_USER=your_db_user
   DB_PASS=your_db_password
   DB_NAME=aar9speed
   ```
   `.env` is excluded from git by `.gitignore` so credentials stay off version control.

4. **Set folder permissions** — the `images/` directory must be writable:
   ```bash
   chmod 755 images/
   ```

5. **Open in browser** — navigate to `http://localhost/AAR9SPEED-sales-and-Invertory-System/login.php`

---

## Project Structure

```
├── .env                             # DB credentials (excluded from git)
├── .gitignore
├── config/
│   └── Database/Database.php        # PDO connection class (reads from .env)
├── Model/                           # Data access classes (extends Database)
│   ├── Customer.php                 # Customer queries
│   ├── Category.php                 # Category queries
│   ├── Order.php                    # Purchase order queries
│   ├── Product.php                  # Product/inventory queries
│   ├── Supplier.php                 # Supplier queries
│   ├── Staff.php                    # Staff queries
│   ├── Dashboard.php                # Dashboard aggregates
│   ├── Invoice.php                  # Invoice/transaction queries
│   ├── Notification.php             # Notification queries
│   └── logout.php                   # Session destroy
├── ajax/                            # AJAX endpoint handlers
│   ├── operation.php                # Customer/Supplier/Staff/Category CRUD
│   ├── orderOperation.php           # Purchase order operations
│   ├── ProdOperation.php            # Product CRUD
│   ├── itemInsert.php               # Insert new product (with upload validation)
│   ├── updateProduct.php            # Update product (with upload validation)
│   ├── inserPurchase.php            # Insert purchase record
│   ├── deleteOrder.php              # Delete purchase order
│   ├── notification.php             # Notification data
│   └── updatingUser.php             # Staff profile update
├── site/                            # Shared UI partials
│   ├── protected.php                # Auth guard
│   ├── sidebar.php                  # Navigation sidebar
│   ├── hamburger.php                # Offcanvas toggle
│   ├── dataUser.php                 # Account update modal
│   ├── loads.php                    # Loading overlay
│   └── title.php                    # <title> and favicon
├── styles/                          # CSS files (including des.css)
├── scripts/                         # Local jQuery + SweetAlert
├── images/                          # Uploaded product images (auto-created, gitignored)
├── img/                             # Static assets (logo, etc.)
│
├── Dashboard.php                    # Dashboard page
├── Customer.php                     # Customer management
├── Product.php                      # Product management
├── ProductView.php                  # Product detail view
├── InactiveProduct.php              # Inactive products view
├── pendingProduct.php               # Pending products view
├── Category.php                     # Category management
├── Supplier.php                     # Supplier management
├── Staff.php                        # Staff management
├── SecurityCheck.php                # Admin gate for Staff
├── Securitylogin.php                # Admin gate for Purchase Orders
├── Order.php                        # Purchase order management
├── invoice.php                      # Invoice listing
├── transaction.php                  # Printable receipt
├── print_order.php                  # Printable purchase order
└── login.php                        # Login page
```

---

## Recommended Improvements

The system works but was built as a college capstone project. The following improvements will bring it up to production quality.

### Priority 1 — Security (do these first)

| # | Issue | Fix |
|---|---|---|
| 1 | ~~**Passwords stored in plaintext**~~ | ✅ Done — `password_hash` / `password_verify` with auto-migration |
| 2 | ~~**Hardcoded DB credentials in source**~~ | ✅ Done — moved to `.env`, gitignored |
| 3 | **No CSRF protection** | Add CSRF tokens to all state-changing forms and AJAX calls |
| 4 | ~~**Unrestricted file upload**~~ | ✅ Done — MIME + extension whitelist + 5 MB limit |
| 5 | **SecurityCheck admin gate is bypassable** | Fix OR logic — check that BOTH role AND password match, never trust a hidden form field for the role |
| 6 | **No HTTPS enforcement** | Redirect HTTP to HTTPS; set `secure` and `httponly` flags on the session cookie |
| 7 | **PDO error messages exposed** | Log to file in production instead of echoing to the browser |
| 8 | **Remaining XSS risks** | Apply `htmlspecialchars()` everywhere DB data is echoed into HTML |

### Priority 2 — Code Quality

| # | Issue | Fix |
|---|---|---|
| 1 | ~~**Class names are typos**~~ (`Cstomer`, `Prduct`, `Ordr`, etc.) | ✅ Done — renamed to proper names |
| 2 | **`extract()` on DB results** | Replace with explicit variable assignments |
| 3 | **HTML built by string concatenation in PHP** | Extract into proper view templates |
| 4 | **No autoloading / no Composer** | Add `composer.json` and PSR-4 autoloading |
| 5 | **SecurityCheck and Securitylogin are identical files** | Merge into one reusable component |

### Priority 3 — Features & UX

| # | Feature | Notes |
|---|---|---|
| 1 | **Role-based access control** | Add proper role checks on every action, not just page entry |
| 2 | **PDF invoices / reports** | Use DOMPDF or FPDF to generate downloadable invoices and sales reports |
| 3 | **Barcode / QR on receipts** | Scan product codes at point of sale |
| 4 | **Monthly sales analytics** | Expand the dashboard with trend charts and inventory turnover |
| 5 | **Low-stock email/SMS alerts** | PHPMailer or SMS gateway when qty drops below threshold |
| 6 | **Audit log** | Record who changed what and when |
| 7 | **Return / refund workflow** | Currently there is no way to reverse a transaction |
| 8 | **Server-side pagination** | DataTables is already loaded — enable server-side mode for large datasets |

### Priority 4 — Modern Stack (long-term)

- Adopt **Laravel** or **Slim** for routing, middleware, ORM, and built-in security helpers
- Use **Vite** instead of manually loading CDN scripts
- Write unit tests for Model classes with **PHPUnit**
- Set up **CI/CD** (GitHub Actions) to lint and test on every push

---

## Bugs Fixed (June 2025 cleanup)

### Security pass
- SQL injection in `login.php` — switched from MySQLi raw queries to PDO prepared statements
- `showProductActive()` — broken SQL `WHERE ITEM_STATUS = 'ACTIVE' >= 1` corrected
- `Quantity()` — `AND` replaced with `,` in SET clause; missing `:SUPPLIER_ID` binding added
- `showDeplated()` — invalid `GROUP BY subject DESC` corrected
- `notification.php` — pending items gated behind wrong method (`showdepleted` instead of `showPending`)
- `filterItem()`, `showDetails()`, `getITEM()`, `placeInput()`, `placeInput2()`, `updateQuantity()` — SQL injection via string interpolation replaced with bound parameters
- Blank `alert()` firing on every page load removed from `sidebar.php`
- Debug `echo $_POST[...]` removed from `inserPurchase.php` and `itemInsert.php`
- Session auth guard added to all unprotected AJAX endpoints
- Duplicate `particles.js`, Bootstrap, jQuery loads cleaned up in `SecurityCheck.php`
- `htmlspecialchars()` added to session username and notification item name output
- Removed dead files: `shActive.php`, `particle.css`, `Model/Login.php`, `image.php`, `fpdf17/`, `customfont/`, `styles/updateAccount.php`

### Structure & further security pass
- **Model class names corrected** — renamed 9 typo classes (`Cstomer`→`Customer`, `Prduct`→`Product`, `Ordr`→`Order`, etc.) with all 28 referencing files updated
- **AJAX handlers moved to `ajax/`** — 9 handler files relocated; all JS call sites and form actions updated
- **`des.css` moved to `styles/`** — eliminated root-level CSS clutter
- **Password hashing** — `login.php` now uses `password_verify()` with seamless auto-rehash for existing plaintext passwords; new staff inserted/updated with `password_hash()`
- **DB credentials moved to `.env`** — `Database.php` reads from `.env` via a no-Composer env loader; `.env` is gitignored
- **File upload validation** — MIME type check (`finfo`), extension whitelist (jpg/png/webp/gif), 5 MB limit added to `ajax/itemInsert.php` and `ajax/updateProduct.php`
- **`transaction.php` broken path** — `../FinalProject/styles/stylish.php` corrected to `styles/stylish.php`

---

*Built by Jez — AAR9SPEED capstone project, Jan 2023. Cleaned up June 2025.*
