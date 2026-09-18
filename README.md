# Multivendor E-commerce Platform

A Laravel 12 and Livewire 4 marketplace for customers, vendors, and administrators.

## Features

- Customer registration, email verification, Google sign-in, profile settings, product browsing, search, category filtering, cart, checkout, orders, reviews, wishlists, coupons, chat, and public product collections.
- Vendor registration and dashboard, catalog and variant management, categories, orders, reviews, chat, coupons, earnings, payout requests, and invoices.
- Admin dashboard, vendor/product/category management, orders, coupons, payouts, messages, and invoices.
- Product ranking, TF-IDF search, recommendations, product clustering, wallet points, real-time chat, and sales analytics.

## Requirements

- PHP 8.2 or later with the extensions Laravel requires
- Composer
- Node.js 20 or later and npm
- MySQL 8 or compatible database

## Local setup

1. Copy `.env.example` to `.env` and configure the `DB_*` values, `APP_URL`, mail settings, Google OAuth credentials, and Reverb credentials if those integrations are required.
2. Install backend and frontend dependencies:

   ```bash
   composer install
   npm install
   ```

3. Generate the application key, prepare the database, and create the storage link:

   ```bash
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   ```

4. Start the application during development:

   ```bash
   composer run dev
   ```

   Or run `php artisan serve` and `npm run dev` separately. Start Reverb and a queue worker as needed for chat and queued work.

## Validation

Run the backend test suite with:

```bash
php artisan test
```

Build production frontend assets with:

```bash
npm run build
```

## Before deployment

- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Provide real production values for database, mail, OAuth, Reverb, queue, and cache configuration.
- Run migrations, compile assets, and ensure `storage` is writable and linked to `public/storage`.
- Verify the customer checkout, vendor order, admin approval, coupon, payout, and chat flows with real user accounts.
