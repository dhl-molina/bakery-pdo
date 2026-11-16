# Bakery Shop: One to Many Relationship in PHP PDO

A small PHP + MySQL web application for a bakery that demonstrates a **one to many relationship** using **PDO**:
**ONE CUSTOMER can have MANY ORDERS.**

| Version | What it is |
|---------|------------|
| [v1](#version-1-one-to-many-with-pdo) | One to many with PDO: customers and orders (CRUD, no login) |
| [v2](#version-2-users-login-and-who-did-it-tracking) | v1 + users (register, login, logout) + `added_by` / `updated_by` / `last_updated` tracking |
| [v3](#version-3-search) | v2 + search on customers and orders, `date_added`, and response messages |

The files in this repository are **v3**. v2 is the code as of commit `9da23f9`, and v1 is the code as of commit `c3159db`.

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
|------|----------------|
| `database/schema.sql` | Creates `users` first; `customers` and `orders` get the three tracking columns |
| `includes/header.php` | Shows the navigation, user name and Log out button only when logged in; adds the awning, the font links and a viewport meta tag |
| `index.php` | Requires login; inserts save `added_by` / `updated_by`; the query now `LEFT JOIN`s `users`; input is trimmed |
| `orders.php` | Same changes as `index.php`, for orders |
| `customer_edit.php`, `order_edit.php` | Require login; updates save `updated_by` and set `last_updated = CURRENT_TIMESTAMP` |
| `customer_delete.php`, `order_delete.php` | Require login and accept POST with a valid CSRF token only (was a GET link) |
| `assets/style.css` | Rewritten: new palette (blueberry accent), Young Serif + Public Sans, bakery-awning header, labelled form rows, tidier tables that stack on phones, visible keyboard focus |
| All pages | Output is escaped with the `e()` helper (`htmlspecialchars` with quotes escaped) |
| All pages with forms or tables | Cleaner HTML: every field has a visible label, tables use `thead`/`tbody`, empty tables show a short message, dates read like "Nov 5, 2026", buttons use sentence case ("Add customer", "Log out") |

---

## Version 3: Search

v3 lets users **search for a customer or an order, then update or delete the record they found**. After the edit or delete, the user returns to the same search results with a response message such as "Customer updated."

### Features

- **Search customers** (Customers page): type any part of a name, phone, email, customer ID or date added (for example `2026-11-16`)
- **Search orders** (Orders page): type any part of an item, customer name, price, pickup date, order ID or date added
  - With a customer selected (`orders.php?customer_id=3`), the search stays inside that customer's orders
  - With the **Orders** link in the header, the search covers the orders of every customer
- **Search, then update or delete:** every result row has Edit and Delete. Afterwards you land back on the same results
- **Response messages** after add, update and delete ("Order updated.", "Customer and their orders deleted.")
- **`date_added`** on customers and orders: the timestamp of when the record was created, saved automatically by the database
- Search results are shown with a count ("2 customers match ...") and a clear message when nothing matches

Tracking columns on `customers` and `orders` (v3 names):

| Column            | Meaning                                              |
|-------------------|------------------------------------------------------|
| `date_added`      | Timestamp of when the record was created             |
| `added_by`        | `user_id` of the user who created the record         |
| `last_updated`    | Timestamp of the last change                         |
| `last_updated_by` | `user_id` of the user who last changed the record    |

### Setup

**New install:** run `database/schema.sql` in phpMyAdmin, then open `http://localhost/bakery-pdo/` and register (same as v2).

**Upgrading a v2 database (keeps your data):** run `database/migration_v3.sql` in phpMyAdmin. It adds `date_added` and renames `updated_by` to `last_updated_by`. Records that already exist get the time you ran the script as their `date_added`.

**Upgrading a v1 database:** run `database/migration.sql` first, then `database/migration_v3.sql`.

After copying the new files, press **Ctrl+Shift+R** once so the browser loads the new stylesheet.

### Folder structure

```
bakery-pdo/
├── assets/
│   └── style.css
├── database/
│   ├── schema.sql
│   ├── migration.sql        (v1 -> v2)
│   └── migration_v3.sql     (v2 -> v3)
├── includes/
│   ├── db.php
│   ├── auth.php             (session, login guard, CSRF, response messages, search helpers)
│   ├── header.php
│   └── footer.php
├── register.php
├── login.php
├── logout.php
├── index.php                (customers: search + list + insert)
├── customer_edit.php
├── customer_delete.php
├── orders.php               (orders: search + list + insert; one customer or all)
├── order_edit.php
└── order_delete.php
```

### What changed from v2

#### Removed

- **The column name `updated_by`.** It is now `last_updated_by` on both tables (same meaning, new name). Anything of your own that reads `updated_by` must use the new name.
- **Fixed return pages after edit and delete.** In v2, editing or deleting always returned to the full customers list or to that customer's orders. In v3 it returns to the page you came from, including your search.
- **The `customer_id` link parameter on `order_delete.php`.** It is no longer needed because the page returns using the `back` parameter.
- Nothing else was removed. Registration, login, logout, the page guard and all CRUD work as in v2.

#### Newly added

- **Search** on customers and on orders (a GET form with a `LIKE` query over several columns, using a prepared statement)
- **All-orders view** (`orders.php` without a customer) and an **Orders** link in the header
- **`date_added`** column on `customers` and `orders`, shown in the "Added" column together with the user who added the record
- **Response messages** (`flash()` in `includes/auth.php`), shown once on the next page
- **Return-to-results** after update or delete (`back` parameter, accepted only for `index.php` and `orders.php` addresses)
- **`database/migration_v3.sql`**
- Search form, result count and no-match styling in `assets/style.css`

#### Changed

| File | Change from v2 |
|------|----------------|
| `database/schema.sql` | `customers` and `orders` get `date_added`; `updated_by` is renamed `last_updated_by` |
| `index.php` | Search form and query; "Added" column shows user and `date_added`; insert and edit use `last_updated_by`; shows a response after adding |
| `orders.php` | Works for one customer or for all customers; search form and query; Customer column in the all-orders view; the add-order form only shows when a customer is selected |
| `customer_edit.php`, `order_edit.php` | Save `last_updated_by`; show a response; return to the page you came from |
| `customer_delete.php`, `order_delete.php` | Show a response (and a friendly message if the record is already gone); return to the page you came from |
| `includes/auth.php` | New helpers: `flash()`, `flash_get()`, `like()`, `back_url()`, `back_param()` |
| `includes/header.php` | Orders link and the response message area |
| `assets/style.css` | Search styles; page width increased from 1040px to 1160px; action buttons wrap instead of overflowing |

## Author

David Hans Lancelotte M. Molina, UCOS 3-1
