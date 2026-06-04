# Stalvi

Personal daily budget tracker inspired by [Presupuesto Diario](https://apps.apple.com/app/presupuesto-diario-original/id651896614). Built with Laravel and Filament.

## How it works

You set your monthly income. The app subtracts your fixed expenses and divides the rest by the days in the month, giving you a single daily allowance. Spend less today, and tomorrow you have more. Overspend, and it shrinks.

## Stack

- **Backend**: Laravel 13, PHP 8.4
- **Admin panel**: Filament 5
- **Database**: SQLite
- **API**: REST with static Bearer token auth
- **Mobile app**: Expo / React Native (separate repo: `stalvi-mobile`)

## Features

### Overview dashboard
- Net worth trend (24 months)
- Expenses by category with period filter
- Income breakdown (fixed / variable / saved / remaining)
- Top 5 spending categories
- Spending by category (12-month stacked chart)
- Income vs expenses (12 months)
- Daily variable spending chart

### Daily Budget dashboard
- Daily allowance with accumulated savings
- Monthly budget usage
- Daily spending vs target
- Savings rate (12 months)
- Month-end forecast
- This month's variable expenses

### Management
- Transaction management (income/expense)
- Multiple accounts with balance tracking
- Hierarchical categories (fixed vs variable)
- Allocations (transfers between accounts)
- Color palette switcher

### API
- `GET /api/daily-budget` — today's allowance, spent, accumulated
- `GET/POST /api/transactions` — list and create
- `GET/PUT/DELETE /api/transactions/{id}` — show, update, delete
- `GET /api/categories` — all categories with children
- `GET /api/accounts` — all accounts with balance
- `CRUD /api/allocations` — manage transfers
- `PUT /api/budget` — update monthly income

All endpoints require `Authorization: Bearer {API_TOKEN}`.

## Setup

```bash
git clone <repo-url> stalvi
cd stalvi
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Add your API token to `.env`:

```
API_TOKEN=your-token-here
```

```bash
php artisan migrate --seed
npm run build
```

The app is served by Laravel Herd at `http://stalvi.test`. Default login: `admin@example.com` / `password`.

To seed with 2 years of realistic demo data instead:

```bash
php artisan migrate:fresh --seeder=DemoSeeder
```

## Development

```bash
# Run tests
php artisan test

# Format code
vendor/bin/pint

# Fresh database with seed data
php artisan migrate:fresh --seed

# Serve API on network (for mobile app)
php artisan serve --host=0.0.0.0 --port=8000
```

## Daily Budget Calculation

```
daily_allowance = (monthly_income - fixed_expenses) / days_in_month
accumulated = (allowance x days_elapsed) - variable_spent_before_today
left_today = daily_allowance + accumulated - spent_today
```

Categories marked as `is_fixed` (rent, subscriptions, etc.) are subtracted upfront. Only variable expenses affect your daily number.
