# Database Setup Guide

## MySQL Database Configuration

The application has been configured to use MySQL instead of SQLite.

### Database Details
- **Database Name**: `new_erp`
- **Host**: `127.0.0.1` (localhost)
- **Port**: `3306`
- **Username**: `root`
- **Password**: (empty)

### Configuration File
Database settings are stored in `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=new_erp
DB_USERNAME=root
DB_PASSWORD=
```

### Setup Steps

1. **Create Database** (already done):
   ```bash
   mysql -u root -e "CREATE DATABASE IF NOT EXISTS new_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

2. **Clear Config Cache**:
   ```bash
   php artisan config:clear
   ```

3. **Run Migrations**:
   ```bash
   php artisan migrate:fresh
   ```

4. **Seed Database** (optional):
   ```bash
   php artisan db:seed
   ```

   Or combine migration and seeding:
   ```bash
   php artisan migrate:fresh --seed
   ```

### Default Users

After seeding, you can login with:

**Admin User:**
- Email: `admin@hisabmittra.com`
- Password: `password`

**Employee User:**
- Email: `employee@hisabmittra.com`
- Password: `password`

### Sample Data Included

The seeder creates:
- ✅ 2 Users (Admin & Employee)
- ✅ Roles & Permissions (Admin, Employee)
- ✅ 2 Sample Customers
- ✅ 3 Sample Products (Cables, MCB, Fan)
- ✅ 1 Warehouse
- ✅ 7 Chart of Accounts (Assets, Liabilities, Equity, Income, Expenses)
- ✅ Company Settings

### Troubleshooting

**If you get "Access denied" error:**
```bash
# Update .env file with correct MySQL credentials
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

**If database doesn't exist:**
```bash
# Create it manually
mysql -u root -p
CREATE DATABASE new_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

**Clear all caches:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Database Tables

Total Tables: 37

Core Tables:
- users, customers, products, invoices, quotations
- tasks, tickets, leads, shipments, warehouses
- accounts, journal_entries, payments
- stock_transactions, whatsapp_messages
- roles, permissions, activity_log

### MySQL Performance Tips

For better performance with XAMPP:
1. Ensure MySQL service is running
2. Check `my.ini` for proper configuration
3. Monitor memory usage in XAMPP Control Panel

### Backup Database

```bash
mysqldump -u root new_erp > backup.sql
```

### Restore Database

```bash
mysql -u root new_erp < backup.sql
```
