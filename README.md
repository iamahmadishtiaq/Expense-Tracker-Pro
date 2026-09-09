# Expense Tracker Pro

A modern, full-stack personal finance management web application built with **Laravel 11**, **Tailwind CSS**, and **Alpine.js**. Expense Tracker Pro helps users manage multi-wallet balances, monitor category-wise budgets with smart overrun alerts, schedule recurring transactions, and attach receipts to their expenses.

---

## Key Features

- **Multi-Account / Wallet Management**
  - Track balances across Bank Accounts, Cash in Hand, and Digital Wallets (JazzCash, SadaPay, etc.).
  - Supports internal transfers between user accounts with automatic source/destination balance adjustments.

- **Financial Transactions & Receipts**
  - Create, filter, and track Income, Expense, and Transfer transactions.
  - Pessimistic locking (`lockForUpdate`) wrapped in database transactions to prevent race conditions during balance updates.
  - Receipt and invoice image attachment support (JPG, PNG, WEBP) with automatic file cleanup on deletion.
  - Interactive receipt lightbox modal preview built with Alpine.js.

- **Automated Recurring Transactions**
  - Automate repetitive income (Salary, Dividends) and recurring expenses (Utilities, Subscriptions).
  - Custom Artisan CLI command (`transactions:process-recurring`) scheduled to execute automatically based on frequency (Daily, Weekly, Monthly, Yearly).
  - Web interface to activate, pause, or remove recurring transaction schedules.

- **Smart Budget Monitoring & Overrun Alerts**
  - Set monthly category-based spending targets.
  - Dynamic threshold alerts on the dashboard when spending reaches **80%** or exceeds **100%** of the allocated budget.

- **Reporting & Data Export**
  - Real-time monthly spending breakdown by category with progress indicators.
  - Native, memory-safe streamed CSV export with active filter preservation.

- **Modern Dark-Mode UI & Seeders**
  - Fully responsive, accessible dark/light theme interface.
  - Pre-configured modular seeders for instant demo dataset testing.

---

## Tech Stack

- **Backend:** PHP 8.2+, Laravel 11
- **Database:** MySQL
- **Frontend:** Blade, Tailwind CSS, Alpine.js
- **Icons & Assets:** Heroicons, Vite

---

## Getting Started

### Prerequisites
- PHP >= 8.2
- Composer
- Node.js & NPM
- MySQL

### Installation

1. **Clone the repository:**
   ```bash
   git clone [https://github.com/your-username/expense-tracker-pro.git](https://github.com/your-username/expense-tracker-pro.git)
   cd expense-tracker-pro

1.Install PHP and Node dependencies:
composer install
npm install

2.Configure Environment:
cp .env.example .env
php artisan key:generate

3.Update .env database configuration:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=expense_tracker_db
DB_USERNAME=root
DB_PASSWORD=

4.Run Migrations & Seeders:
php artisan migrate --seed

5.Create Public Storage Symlink:
php artisan storage:link

6.Compile Frontend Assets:
npm run build
# or for development:
# npm run dev

7.Start Local Server:
php artisan serve

Demo Credentials
You can log in directly using the seeded demo account:

URL: http://localhost:8000/login

Email: test@example.com
Password: password

Recurring Transactions Scheduler
To test recurring transactions processing manually via CLI:
php artisan transactions:process-recurring

License
This project is open-sourced software licensed under the MIT license.


