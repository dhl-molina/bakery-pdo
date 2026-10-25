# Bakery Shop: One to Many Relationship in PHP PDO

A small PHP + MySQL web application that demonstrates a **one to many relationship** using **PDO**.

## Relationship

**ONE CUSTOMER can have MANY ORDERS.**

| Table         | Role      | Description                                           |
|---------------|-----------|-------------------------------------------------------|
| `customers`   | Parent    | Registered bakery customers                           |
| `orders`      | Child     | Orders placed by a customer (linked by `customer_id`) |

Each order belongs to exactly one customer, while a customer can place any number of orders. The `orders.customer_id` column is a foreign key to `customers.customer_id` with `ON DELETE CASCADE`, so deleting a customer also deletes their orders.

## Features

### Customers (parent)

- Insert a new customer
- Update a customer's details
- Delete a customer (and all of their orders)

### Orders (child)

- Insert an order for a specific customer
- Update an order
- Delete an order

## Requirements

- PHP 8.0 or newer
- MySQL / MariaDB
- XAMPP, WAMP, or Laragon (or any Apache + PHP + MySQL setup)

## Setup

1. Copy this folder into your web server directory (for XAMPP: `C:\xampp\htdocs\bakery-pdo`).
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open phpMyAdmin (`http://localhost/phpmyadmin`), go to the **SQL** tab, and run the contents of `database/schema.sql`. This creates the `bakery_db` database and the `customers` and `orders` tables.
4. If your MySQL user or password is not the default (`root` with an empty password), edit `includes/db.php`.
5. Open `http://localhost/bakery-pdo/` in your browser.

## Folder Structure

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

## Database Schema

```sql
CREATE TABLE customers (
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name   VARCHAR(100) NOT NULL,
    phone       VARCHAR(20)  NOT NULL,
    email       VARCHAR(100)
);

CREATE TABLE orders (
    order_id    INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    item_name   VARCHAR(100) NOT NULL,
    quantity    INT NOT NULL DEFAULT 1,
    price       DECIMAL(10,2) NOT NULL,
    pickup_date DATE NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
);
```

## Author

David Hans Lancelotte M. Molina, UCOS 3-1
