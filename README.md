# Smart Expense Manager

A personal finance web app built with **HTML, CSS, JavaScript, PHP and MySQL**.

## Features
- Register / login (hashed passwords, CSRF protection, prepared statements)
- **Add daily expenses** (amount, category, sub category, date, payment mode, note) and income
- **Dashboard**: balance, this month's spending vs last month, savings goal, recent expenses
- **Monthly expense report**: income, expenses, net savings, savings rate, category breakdown, daily spending chart, biggest expenses, CSV download, print / save as PDF
- **Income vs expenses chart** (last 6 months) and monthly expenses chart (Chart.js)
- Filter and search all expenses; delete entries; one-click demo data

## Folder structure
```
smart-expense-manager/
├── index.php, login.php, register.php, logout.php
├── dashboard.php, expenses.php, income.php, report.php, export.php
├── actions.php            (all form submissions: add / delete / goal / demo data)
├── includes/              (config, db connection, helpers, layout header/footer)
├── database/schema.sql    (tables + default categories, auto-run on first visit)
├── assets/css, assets/js  (styles, modals, Chart.js setup)
├── Dockerfile, docker-start.sh   (for Render)
└── README.md
```

## Run on your computer (XAMPP, easiest)
1. Install XAMPP (PHP 7.4 or newer): https://www.apachefriends.org
2. Copy the `smart-expense-manager` folder into `C:\xampp\htdocs\` (macOS: `/Applications/XAMPP/htdocs/`).
3. Open the XAMPP Control Panel and **Start** Apache and MySQL.
4. Open http://localhost/phpmyadmin → **New** → database name `smart_expense_manager` → collation `utf8mb4_general_ci` → **Create**.
   (Leave it empty. The tables are created automatically on your first visit.
   Or click the database → **Import** → choose `database/schema.sql`.)
5. Open **http://localhost/smart-expense-manager/** → Create account → on the dashboard click **Load demo data**.

If your MySQL root user has a password, copy `includes/config.local.example.php` to
`includes/config.local.php` and put the password there (this file is git-ignored).

### Without XAMPP (PHP built-in server)
Needs PHP with the `pdo_mysql` extension and a running MySQL:
```
cd smart-expense-manager
php -S localhost:8000
```
Open http://localhost:8000

## Push to GitHub
```
cd smart-expense-manager
git init
git add .
git commit -m "Smart Expense Manager: minor project"
git branch -M main
git remote add origin https://github.com/<your-username>/smart-expense-manager.git
git push -u origin main
```
(Create the empty repository `smart-expense-manager` on github.com first, without a README.)

## Deploy on Render
Render has no native PHP runtime and no managed MySQL, so: PHP runs from the included
**Dockerfile**, and the database lives on an external MySQL host (TiDB Cloud Serverless has a free tier).

1. **Database**: create a TiDB Cloud Serverless cluster, create a database named `smart_expense_manager`
   (SQL editor: `CREATE DATABASE smart_expense_manager;`), then copy host, port, user and password from the *Connect* dialog.
2. **Render**: New → **Web Service** → connect your GitHub repo → Language/Runtime **Docker** → Free instance.
3. Add environment variables:

| Key | Value |
|---|---|
| DB_HOST | host from your MySQL provider |
| DB_PORT | 4000 for TiDB Cloud (3306 for most others) |
| DB_NAME | smart_expense_manager |
| DB_USER | your DB username |
| DB_PASS | your DB password |
| DB_SSL | true |

4. Deploy. On first visit the tables are created automatically.

Note: Render's free web services sleep after a period without traffic, so the first
request afterwards is slow. Sessions are stored on the container, so users may need to log in again after a redeploy.

## Troubleshooting
- **"Database connection failed"**: MySQL is not started, the database `smart_expense_manager` does not exist, or the credentials in `includes/config.php` / environment variables are wrong.
- **Blank page / 500 error**: check the PHP error log (`xampp/apache/logs/error.log`).
- **Charts missing**: you need an internet connection, because Chart.js loads from a CDN.
- **Page not found**: the folder name must match the URL (`htdocs/smart-expense-manager` → `/smart-expense-manager/`).
