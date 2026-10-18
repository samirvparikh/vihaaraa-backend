# Vihaaraa Backend (Laravel 13 + MySQL)

API for the React storefront (`../vihaaraa-frontend`) and the admin panel that manages it.

## Setup

```bash
composer install
cp .env.example .env        # then set DB_* (database: vihaaraa) and APP_URL
php artisan key:generate
php artisan migrate --seed  # creates tables + demo catalogue, home content, coupons, users
php artisan storage:link    # serves uploaded images from /storage
php artisan serve           # http://localhost:8000
```

MySQL 5.7 note: tables are created as InnoDB (`DB_ENGINE` in `.env` to override).

## Logins (seeded — change these passwords)

| Who | Where | Email | Password |
|---|---|---|---|
| Admin | http://localhost:8000/admin | admin@vihaaraa.com | Admin@123 |
| Customer | storefront `/login` | customer@vihaaraa.com | Customer@123 |

Admins can only sign in to the admin panel and customers only to the storefront.

## Admin panel (`/admin`)

- **Top Offer Bar**: the rotating messages above the header
- **Image Slider**: home banners (upload desktop and mobile images, or show a product illustration)
- **Home Sections**: Category Circles (New Launches, Best Sellers, Demi-Fine, Ethnic, Western, 925 Silver), Handpicked For You, Our Best Sellers (heading and count), Shop The Look
- **Products**: price/MRP, stock, sizes, stones, main image and gallery, New / Best Seller flags, collections
- **Categories** and **Collections** (manual, or automatic: all / new / best sellers)
- **Orders**: filter, view, change status/payment, tracking number, CSV export. Cancelling restocks the items.
- **Coupon Codes**: percent or fixed amount, minimum order, cap, total and per-customer limits, start/end dates
- **Customers & Admins**, **Newsletter** (CSV export), **Pages** (policy pages), **Shop Settings** (free-shipping threshold, shipping fee, COD limit, max quantity, Buy 1 Get 1 on/off, contact details)

## Storefront API (`/api`)

Public: `GET site`, `home`, `products?q=&ids=&category=`, `products/{slug}`, `collections/{handle}`, `pages/{slug}`;
`POST newsletter`, `coupons/apply`, `register`, `login`.

Customer (Bearer token from login/register): `GET/PUT me`, `PUT me/password`, `POST logout`,
`GET/POST/DELETE cart`, `POST cart/merge`, `PATCH/DELETE cart/{item}`,
`GET wishlist`, `POST wishlist/{productId}`, `POST wishlist/merge`,
`GET/POST orders`, `GET orders/{number}`, `POST orders/{number}/cancel`.

Order totals (Buy 1 Get 1, coupon, shipping) are always recalculated on the server in `app/Services/Pricing.php`.

## Payments

Online payment is **simulated**: UPI, card and net-banking orders are marked paid right away. Connect Razorpay/PayU in
`app/Http/Controllers/Api/OrderController.php@store` (create the gateway order, then mark it paid in a verified callback).
