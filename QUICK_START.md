# 🚀 HisabMittra ERP+CRM - Quick Start Guide

## Current Status: ✅ Ready to Configure Database

Your Laravel 12 application is **installed and configured**!

### What's Already Done ✓

- ✅ Composer packages installed
- ✅ .env file created
- ✅ Application key generated
- ✅ Laravel 12.64.0 running

---

## Next Steps (Choose One Option)

### 🎯 Option 1: Quick Setup with SQLite (Fastest - Recommended for Testing)

```bash
# 1. The database file already exists
# Just run migrations
php artisan migrate

# 2. Seed with sample data
php artisan db:seed

# 3. Start server
php artisan serve
```

**Login at:** http://localhost:8000
- Admin: admin@hisabmittra.com / password
- Employee: employee@hisabmittra.com / password

---

### 🎯 Option 2: MySQL/MariaDB Setup (Recommended for Production)

#### Step 1: Create MySQL Database
Open MySQL/phpMyAdmin and run:
```sql
CREATE DATABASE hisabmittra_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

#### Step 2: Update .env file
Edit `.env` and change these lines:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hisabmittra_erp
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

#### Step 3: Run Setup Commands
```bash
# Publish Spatie packages
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Create storage link
php artisan storage:link

# Start server
php artisan serve
```

#### Step 4: Access Application
Open browser: http://localhost:8000

**Default Credentials:**
- **Admin**: admin@hisabmittra.com / password
- **Employee**: employee@hisabmittra.com / password

---

## 📱 What You'll Get

### Admin Panel Features
- 📊 **Dashboard** - Charts, stats, recent activities
- 👥 **Employee Management** - CRUD with roles
- 🏢 **Customer Management** - GSTIN validation
- 📦 **Product Catalog** - With HSN codes
- 🏭 **Inventory** - Multi-warehouse with approval
- 💰 **GST Billing** - Auto CGST/SGST/IGST calculation
- 📄 **Invoice Generation** - PDF export
- 📚 **Accounting** - Chart of accounts, journal entries
- ✅ **Task Management** - Kanban board
- 🎫 **Ticket System** - Helpdesk with SLA
- 🚚 **Shipment Tracking** - Logistics management
- 📊 **Reports** - GST, Sales, Inventory (Excel/PDF)
- ⚙️ **Settings** - Company profile, GSTIN

### Employee Panel Features
- 📊 **Personal Dashboard**
- ✅ **My Tasks** - View and update assigned tasks
- 🎫 **My Tickets** - Handle assigned support tickets
- 🎯 **My Leads** - Manage sales leads

---

## 🎨 Sample Data Included

After seeding, you'll have:
- ✓ 2 Users (Admin + Employee)
- ✓ 2 Roles with Permissions
- ✓ 3 Sample Products (Cables, MCB, Fan)
- ✓ 2 Sample Customers with GSTIN
- ✓ 1 Warehouse
- ✓ 7 Chart of Accounts
- ✓ Company Settings (HisabMittra)

---

## 🧪 Testing the Application

### 1. Login Test
```
URL: http://localhost:8000/login
Admin: admin@hisabmittra.com / password
```

### 2. Create Your First Invoice
1. Go to Customers → Create new customer
2. Go to Products → Verify products exist
3. Go to Invoices → Create Invoice
4. Select customer and add products
5. System auto-calculates GST
6. Download PDF

### 3. Try Task Management
1. Go to Tasks
2. See Kanban board (4 columns)
3. Create new task
4. Assign to employee
5. Drag-drop to change status

### 4. Check Reports
1. Go to Reports → GST Summary
2. See CGST/SGST/IGST breakdown
3. Export to Excel or PDF

---

## 🔧 Troubleshooting

### Issue: Database connection error
**Solution**: 
- Verify MySQL is running (XAMPP/WAMP control panel)
- Check database credentials in `.env`
- Ensure database `hisabmittra_erp` exists

### Issue: Permission denied on storage
**Solution**:
```bash
# Windows (Run as Administrator)
icacls storage /grant Users:F /T
icacls bootstrap\cache /grant Users:F /T

# Linux/Mac
chmod -R 775 storage bootstrap/cache
```

### Issue: Class not found
**Solution**:
```bash
composer dump-autoload
```

### Issue: Routes not working
**Solution**:
```bash
php artisan route:clear
php artisan config:clear
```

---

## 📂 Important URLs

After starting server (`php artisan serve`):

- **Login**: http://localhost:8000/login
- **Admin Dashboard**: http://localhost:8000/admin/dashboard
- **Employee Dashboard**: http://localhost:8000/employee/dashboard

---

## 📖 Documentation Files

Explore detailed documentation:

1. **README.md** - Complete feature overview
2. **INSTALLATION.md** - Detailed setup steps
3. **SYSTEM_DOCUMENTATION.md** - Technical deep-dive
4. **API_REFERENCE.md** - All routes and endpoints
5. **DEPLOYMENT_CHECKLIST.md** - Production deployment
6. **PROJECT_SUMMARY.md** - Full project details

---

## 🎯 Development Roadmap

The system is **production-ready** with core functionality complete:

✅ **Phase 1: Core System** (COMPLETE)
- Database schema (25 tables)
- Models with relationships (20+)
- Controllers (15+)
- Authentication & Authorization
- GST calculation engine
- Invoice generation
- PDF/Excel export
- Activity logging

🚧 **Phase 2: Additional Views** (Optional)
- Additional CRUD views following established patterns
- Advanced filtering and search
- More report types

🚧 **Phase 3: Enhancements** (Optional)
- Email notifications via SMTP
- WhatsApp two-way messaging
- Mobile responsive improvements
- API for mobile apps

---

## 💡 Tips

### For Testing
- Use SQLite (already configured)
- Sample data is perfect for demos
- All features work out of the box

### For Production
- Use MySQL/MariaDB
- Configure email settings
- Enable HTTPS
- Set up backups
- Review DEPLOYMENT_CHECKLIST.md

### For Development
- Study the code patterns
- Controllers follow resource pattern
- Services for business logic
- Blade components for reusability

---

## 🆘 Need Help?

1. **Setup Issues**: Check INSTALLATION.md
2. **Technical Details**: Check SYSTEM_DOCUMENTATION.md
3. **API/Routes**: Check API_REFERENCE.md
4. **Deployment**: Check DEPLOYMENT_CHECKLIST.md

---

## 🎉 You're Ready!

Choose your setup option above and start exploring your advanced ERP+CRM system!

**Built with Laravel 12 • Bootstrap 5 • Zero npm dependencies**

---

**Quick Command Reference:**

```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Fresh start (reset everything)
php artisan migrate:fresh --seed

# Clear all caches
php artisan optimize:clear

# View routes
php artisan route:list

# Check Laravel info
php artisan about
```

---

**Default Password for All Users:** `password`

**Remember to change passwords in production!**

🚀 **Happy Building!**
