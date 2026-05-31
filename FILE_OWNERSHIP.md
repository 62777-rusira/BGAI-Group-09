# BGAI E-Commerce - File Ownership & CRUD Mapping
## Each member commits ONLY their files + shared files to their GitHub

---

## SHARED FILES (Everyone needs these - commit to all repos)
```
bgai/
├── config/db.php                    ← Database connection + helper functions
├── database/seed.php                ← Database schema + seed data
├── includes/
│   ├── header.php                   ← Shared navbar
│   ├── footer.php                   ← Shared footer
│   ├── admin-header.php             ← Admin sidebar layout
│   └── admin-footer.php             ← Admin layout close
├── assets/css/style.css             ← Main stylesheet
├── index.php                        ← Homepage
└── uploads/                         ← Product images (all .jpg, .png files)
```

---

## 1. PRAVEEN - User Account & Authentication
### Folder: `auth/`

| CRUD   | File                    | What it does                          |
|--------|-------------------------|---------------------------------------|
| CREATE | `auth/register.php`     | Register new user account             |
| READ   | `auth/login.php`        | Read user credentials for login       |
| READ   | `auth/profile.php`      | Read & display user profile           |
| UPDATE | `auth/update-profile.php` | Update user name, email, phone, etc.|
| UPDATE | `auth/change-password.php` | Update user password                |
| DELETE | `auth/delete-account.php` | Delete user account permanently      |
| -      | `auth/logout.php`       | Destroy session & logout              |

**Database tables owned:** `users`
**Total files: 7**

---

## 2. RUSIRA - Product Catalogue & Inventory Management
### Folder: `products/`

| CRUD   | File                           | What it does                          |
|--------|--------------------------------|---------------------------------------|
| CREATE | `products/create.php`          | Admin: Add new product                |
| CREATE | `products/create-category.php` | Admin: Add new category               |
| CREATE | `products/submit-review.php`   | Customer: Submit product review       |
| READ   | `products/shop.php`            | Customer: Browse/filter/search products|
| READ   | `products/detail.php`          | Customer: View single product + reviews|
| READ   | `products/manage.php`          | Admin: List all products              |
| READ   | `products/categories.php`      | Admin: List all categories            |
| READ   | `products/wishlist.php`        | Customer: View wishlist               |
| UPDATE | `products/edit.php`            | Admin: Edit product details           |
| UPDATE | `products/edit-category.php`   | Admin: Edit category                  |
| UPDATE | `products/edit-review.php`     | Customer: Edit own review             |
| UPDATE | `products/wishlist-toggle.php` | Toggle product in/out of wishlist     |
| DELETE | `products/delete.php`          | Admin: Delete product                 |
| DELETE | `products/delete-category.php` | Admin: Delete category                |
| DELETE | `products/delete-review.php`   | Delete review (owner or admin)        |

**Database tables owned:** `products`, `categories`, `reviews`, `wishlists`
**Total files: 15**

---

## 3. THIMIRA - Shopping Cart & Orders
### Folder: `cart/`

| CRUD   | File                           | What it does                          |
|--------|--------------------------------|---------------------------------------|
| CREATE | `cart/add.php`                 | Add product to cart                   |
| CREATE | `cart/checkout.php`            | Create order from cart items          |
| READ   | `cart/index.php`               | View shopping cart                    |
| READ   | `cart/orders.php`              | View all my orders                    |
| READ   | `cart/order-detail.php`        | View single order details             |
| UPDATE | `cart/update.php`              | Update cart item quantity              |
| UPDATE | `cart/cancel-order.php`        | Cancel pending order                  |
| UPDATE | `cart/update-order-status.php` | Admin: Update order status            |
| DELETE | `cart/remove.php`              | Remove item from cart                 |

**Database tables owned:** `cart_items`, `orders`, `order_items`
**Total files: 9**

---

## 4. RUKSHAN - Pricing, Payments & Country Logic
### Folder: `payments/`

| CRUD   | File                           | What it does                          |
|--------|--------------------------------|---------------------------------------|
| CREATE | `payments/process.php`         | Create payment record                 |
| CREATE | `payments/add-currency.php`    | Admin: Add new currency               |
| CREATE | `payments/add-country.php`     | Admin: Add new country                |
| READ   | `payments/history.php`         | Customer: View payment history        |
| READ   | `payments/detail.php`          | View single payment details           |
| READ   | `payments/currencies.php`      | Admin: List all currencies            |
| READ   | `payments/countries.php`       | Admin: List all countries             |
| READ   | `payments/manage.php`          | Admin: Manage all payments            |
| UPDATE | `payments/edit-currency.php`   | Admin: Edit currency rate/details     |
| UPDATE | `payments/edit-country.php`    | Admin: Edit country tax/shipping      |
| UPDATE | `payments/refund.php`          | Admin: Refund a payment               |
| DELETE | `payments/delete-currency.php` | Admin: Delete currency                |
| DELETE | `payments/delete-country.php`  | Admin: Delete country                 |
| -      | `payments/set-currency.php`    | AJAX: Set active currency in session  |

**Database tables owned:** `currencies`, `countries`, `payments`
**Total files: 14**

---

## 5. AKELA - Admin Dashboard & Reports
### Folder: `admin/`

| CRUD   | File                            | What it does                         |
|--------|---------------------------------|--------------------------------------|
| CREATE | `admin/create-report.php`       | Generate new report                  |
| READ   | `admin/index.php`               | Dashboard with stats & charts        |
| READ   | `admin/users.php`               | View/manage all users                |
| READ   | `admin/orders.php`              | View/manage all orders               |
| READ   | `admin/reports.php`             | List all reports                     |
| READ   | `admin/view-report.php`         | View single report                   |
| READ   | `admin/activity-log.php`        | View activity log                    |
| UPDATE | `admin/settings.php`            | Update site settings                 |
| UPDATE | `admin/toggle-user.php`         | Toggle user active/inactive          |
| UPDATE | `admin/update-order-status.php` | Update order status                  |
| DELETE | `admin/delete-report.php`       | Delete a report                      |

**Database tables owned:** `reports`, `activity_logs`, `site_settings`
**Total files: 11**
