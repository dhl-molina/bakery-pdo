# Bakery Shop: One to Many Relationship in PHP PDO

A small PHP + MySQL web application for a bakery that demonstrates a **one to many relationship** using **PDO**:
**ONE CUSTOMER can have MANY ORDERS.**

| Version | What it is |
|---------|------------|
| [v1](#version-1-one-to-many-with-pdo) | One to many with PDO: customers and orders (CRUD, no login) |
| [v2](#version-2-users-login-and-who-did-it-tracking) | v1 + users (register, login, logout) + `added_by` / `updated_by` / `last_updated` tracking |

The files in this repository are **v2**. v1 is the code as of commit `c3159db`.

---

## Version 1: One to Many with PDO

### Relationship

| Table         | Role      | Description                                           |
|---------------|-----------|-------------------------------------------------------|
| `customers`   | Parent    | Registered bakery customers                           |
| `orders`      | Child     | Orders placed by a customer (linked by `customer_id`) |

Each order belongs to exactly one customer, while a customer can place any number of orders. The `orders.customer_id` column is a foreign key to `customers.customer_id` with `ON DELETE CASCADE`, so deleting a customer also deletes their orders.

### Features

- **Customers (parent):** insert, update, delete (their orders are deleted too)
- **Orders (child):** insert, update, delete, listed per customer

### Folder structure

```
bakery-pdo/
├── assets/
│   └── style.css
├── database/
│   └── schema.sql
├── includes/
│   ├── db.php
│   ├── header.php
│   └── footer.php
├── index.php            (customers: list + insert)
├── customer_edit.php    (customers: update)
├── customer_delete.php  (customers: delete)
├── orders.php           (orders: list + insert)
├── order_edit.php       (orders: update)
└── order_delete.php     (orders: delete)
```

---

## Version 2: Users, Login and "Who did it" Tracking

v2 adds a **user entity**. People must register and log in to use the system, and every customer and order remembers **who added it** and **who last updated it (and when)**.

### Features

- **Register** with first name, last name, address, age, email and password (stored as a bcrypt hash, never as plain text)
- **Login / Logout** using sessions
- **Private pages:** opening `index.php` (or any other page) while logged out redirects to `login.php`
- **Customers and orders:** same CRUD as v1, now with **Added by** and **Last updated** (user + time) shown in both tables
- **POST-only deletes** with a CSRF token on every form

New tracking columns on `customers` and `orders`:

| Column         | Meaning                                              |
|----------------|------------------------------------------------------|
| `added_by`     | `user_id` of the user who created the record         |
| `updated_by`   | `user_id` of the user who last changed the record    |
| `last_updated` | Timestamp of the last change                         |

### Requirements

- PHP 8.0 or newer
- MySQL / MariaDB
- XAMPP, WAMP, or Laragon (or any Apache + PHP + MySQL setup)

### Setup

**New install**

1. Copy this folder into your web server directory (for XAMPP: `C:\xampp\htdocs\bakery-pdo`).
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open phpMyAdmin (`http://localhost/phpmyadmin`), go to the **SQL** tab, and run `database/schema.sql`. This creates `bakery_db` with the `users`, `customers` and `orders` tables.
4. If your MySQL user or password is not the default (`root` with an empty password), edit `includes/db.php`.
5. Open `http://localhost/bakery-pdo/`. You will land on the login page. Click **Register** to create your first account.

**Upgrading a v1 database (keeps existing customers and orders)**

Run `database/migration.sql` in phpMyAdmin instead of `schema.sql`. Rows created in v1 show "—" under *Added by*, because nobody was recorded when they were created.

### Folder structure

```
bakery-pdo/
├── assets/
│   └── style.css
├── database/
│   ├── schema.sql          (full schema: users, customers, orders)
│   └── migration.sql       (upgrade a v1 database)
├── includes/
│   ├── db.php              (PDO connection)
│   ├── auth.php            (session, require_login(), CSRF and helper functions)
│   ├── header.php
│   └── footer.php
├── register.php            (users: register)
├── login.php               (users: login)
├── logout.php              (users: logout)
├── index.php               (HOMEPAGE, private. customers: list + insert)
├── customer_edit.php       (customers: update)
├── customer_delete.php     (customers: delete)
├── orders.php              (orders: list + insert)
├── order_edit.php          (orders: update)
└── order_delete.php        (orders: delete)
```

### What changed from v1

#### Removed

- **Deleting through a plain link.** In v1, `customer_delete.php?id=...` and `order_delete.php?id=...` deleted the record when opened as a link. In v2, opening them this way does nothing; deletes only work from the Delete button (a POST form with a token).
- **Public access.** In v1 every page could be opened by anyone. In v2 a visitor who is not logged in is redirected to `login.php`.
- Nothing else was removed. All v1 features (customer and order insert, update, delete, and the one to many link) still work.

#### Newly added

- **Files:** `register.php`, `login.php`, `logout.php`, `includes/auth.php`, `database/migration.sql`
- **`users` table** with `user_id`, `first_name`, `last_name`, `address`, `age`, `email` (unique), `password_hash`, `created_at`
- **Tracking columns** `added_by`, `updated_by`, `last_updated` on `customers` and `orders` (the first two are foreign keys to `users`, set to `NULL` if the user is ever deleted)
- **"Added by" and "Last updated" columns** in the customers and orders tables
- **Login guard** (`require_login()`), a **user bar** in the header ("Logged in as ... / Logout"), and a **CSRF token** on every form
- **Register validation:** required fields, valid email, unique email, password of at least 8 characters, matching confirmation
- **"Back" links** on the customer and order edit pages
- **CSS and layout polish:** login/register styling, alerts, the new table columns, empty-state messages and a phone-friendly table layout

#### Changed

| File | Change from v1 |
| ------ | ---------------- |
| `database/schema.sql` | Creates `users` first; `customers` and `orders` get the three tracking columns |
| `includes/header.php` | Shows the navigation, user name and Log out button only when logged in; adds the awning, the font links and a viewport meta tag |
| `index.php` | Requires login; inserts save `added_by` / `updated_by`; the query now `LEFT JOIN`s `users`; input is trimmed |
| `orders.php` | Same changes as `index.php`, for orders |
| `customer_edit.php`, `order_edit.php` | Require login; updates save `updated_by` and set `last_updated = CURRENT_TIMESTAMP` |
| `customer_delete.php`, `order_delete.php` | Require login and accept POST with a valid CSRF token only (was a GET link) |
| `assets/style.css` | Rewritten: new palette (blueberry accent), Young Serif + Public Sans, bakery-awning header, labelled form rows, tidier tables that stack on phones, visible keyboard focus |
| All pages | Output is escaped with the `e()` helper (`htmlspecialchars` with quotes escaped) |
| All pages with forms or tables | Cleaner HTML: every field has a visible label, tables use `thead`/`tbody`, empty tables show a short message, dates read like "Nov 5, 2026", buttons use sentence case ("Add customer", "Log out") |

## Author

David Hans Lancelotte M. Molina, UCOS 3-1
