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
git clone <repository-url> invocta
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

