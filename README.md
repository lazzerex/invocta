<p align="center">
    <img width="578" height="329" alt="invocta-no-edit-removebg-preview" src="https://github.com/user-attachments/assets/2fddc4ef-d9a5-43db-b73a-9c477492ea10" />
</p>


<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel&logoColor=white"/>
  <img src="https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat&logo=vue.js&logoColor=white"/>
  <img src="https://img.shields.io/badge/Inertia.js-2-9553E9?style=flat&logo=inertia&logoColor=white"/>
  <img src="https://img.shields.io/badge/Tailwind_CSS-3.4-06B6D4?style=flat&logo=tailwindcss&logoColor=white"/>
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat&logo=mysql&logoColor=white"/>
  <img src="https://img.shields.io/badge/Stripe-Cashier-635BFF?style=flat&logo=stripe&logoColor=white"/>
</p>

# Invocta

A Multi-Tenant SaaS Invoicing Platform built with the VILT stack (Vue.js + Inertia.js + Laravel + Tailwind CSS).



## About

Invocta is a production-grade SaaS platform where businesses (tenants) can sign up, get their own isolated workspace, manage clients, create and send invoices, and handle subscriptions with auto-billing via Stripe. Each tenant is completely isolated from other tenants.

## Tech Stack

- **Backend:** Laravel 12
- **Frontend:** Vue 3 + Inertia.js
- **Styling:** Tailwind CSS
- **Multi-tenancy:** Spatie Laravel-Multitenancy
- **Roles & Permissions:** Spatie Laravel-Permission
- **Payments & Subscriptions:** Laravel Cashier (Stripe)
- **PDF Generation:** DomPDF
- **Database:** MySQL
- **Auth:** Laravel Breeze

## Features

### Implemented
- User registration with automatic tenant creation
- Multi-tenancy with data isolation
- Role-based access control (Admin, Manager, Staff)
- Team management with email invitations
- Permission-based authorization

### Planned
- Client management (CRUD)
- Invoice management with line items
- PDF generation and email delivery
- Public invoice view with Stripe payments
- Subscription billing via Stripe
- Dashboard analytics
- Tenant settings and branding

## Requirements

- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+

## Installation

```bash
# Clone the repository
git clone https://github.com/lazzerex/invocta
cd invocta

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database in .env
# DB_DATABASE=invocta
# DB_USERNAME=root
# DB_PASSWORD=

# Run migrations and seed
php artisan migrate --seed
```

## Development

Run both servers in separate terminals:

```bash
# Terminal 1: Laravel backend
php artisan serve

# Terminal 2: Vite frontend
npm run dev
```

Access the application at `http://localhost:8000`

## Test Accounts

After seeding, these accounts are available:

| Email | Password | Tenant | Role |
|-------|----------|--------|------|
| john@acme.com | password | Acme Corporation | Admin |
| tony@stark.com | password | Stark Industries | Admin |

## Roles & Permissions

| Role | Permissions |
|------|-------------|
| Admin | Full access to all features |
| Manager | Manage clients and invoices, view team |
| Staff | View clients and invoices only |

## License

This project is proprietary software.

