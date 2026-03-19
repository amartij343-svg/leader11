# PHP Supplement Store

Simple supplement e-commerce starter built with:
- PHP
- SQLite
- HTML
- CSS
- JavaScript

## What it does
- Product catalog
- Product page
- Cart
- Checkout creates orders
- Admin login
- Product price editing
- Product discounts
- Category discounts
- Inventory tracking
- Stock movement history
- SQLite database with product seed data from the uploaded Excel file

## Files
- `index.php` - storefront
- `product.php` - product details
- `cart.php` - cart and checkout
- `admin_login.php` - admin login
- `admin.php` - admin panel
- `setup.php` - creates database automatically if needed
- `data/products_seed.json` - imported product seed list
- `data/store.sqlite` - database file after setup

## Local run
Use XAMPP, Laragon, MAMP or PHP built-in server.

### Option 1: built-in PHP server
```bash
php -S localhost:8000
```
Then open:
- http://localhost:8000/setup.php
- http://localhost:8000/index.php

### Option 2: XAMPP
1. Put folder inside `htdocs`
2. Open `http://localhost/PROJECT_FOLDER/setup.php`
3. Then open `index.php`

## Admin login
Default password:
`admin123`

Change it in:
`config.php`

## GitHub
You can upload this entire folder to GitHub as a normal repository.

## Important
GitHub itself does not run PHP websites.
GitHub is good for storing the code.
To make this website actually run online, upload it to PHP hosting such as:
- cPanel hosting
- Hostinger
- Namecheap shared hosting
- InfinityFree
- local XAMPP/Laragon

## Next improvements
- Real payment gateway
- Better image upload
- Customer login/register
- Email sending
- Better security
