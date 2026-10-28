CREATE DATABASE IF NOT EXISTS bakery_db;
USE bakery_db;

-- USER ENTITY: the people who can log in to the system
CREATE TABLE users (
    user_id       INT AUTO_INCREMENT PRIMARY KEY,
    first_name    VARCHAR(50)  NOT NULL,
    last_name     VARCHAR(50)  NOT NULL,
    address       VARCHAR(255) NOT NULL,
    age           TINYINT UNSIGNED NOT NULL,
    email         VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,   -- never store the plain password
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- PARENT TABLE: one customer ...
CREATE TABLE customers (
    customer_id  INT AUTO_INCREMENT PRIMARY KEY,
    full_name    VARCHAR(100) NOT NULL,
    phone        VARCHAR(20)  NOT NULL,
    email        VARCHAR(100),
    added_by     INT NULL,                 -- user who created the record
    updated_by   INT NULL,                 -- user who last changed the record
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                 ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (added_by)   REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
);

-- CHILD TABLE: ... has many orders
CREATE TABLE orders (
    order_id     INT AUTO_INCREMENT PRIMARY KEY,
    customer_id  INT NOT NULL,
    item_name    VARCHAR(100) NOT NULL,
    quantity     INT NOT NULL DEFAULT 1,
    price        DECIMAL(10,2) NOT NULL,
    pickup_date  DATE NOT NULL,
    added_by     INT NULL,
    updated_by   INT NULL,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                 ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    FOREIGN KEY (added_by)   REFERENCES users(user_id) ON DELETE SET NULL,
    FOREIGN KEY (updated_by) REFERENCES users(user_id) ON DELETE SET NULL
);
