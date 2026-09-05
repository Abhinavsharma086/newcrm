# HisabMittra ERP+CRM System

Advanced, production-ready ERP+CRM hybrid web application built with Laravel 12, featuring comprehensive business management modules with HisabMittra brand theme.

## Features

### Core Modules
- **Employee Management** - Complete CRUD with role-based access control
- **Customer Management** - Customer database with GSTIN validation
- **Lead Management** - Lead tracking with stages and follow-ups
- **Product Management** - Product catalog with HSN codes
- **Inventory Management** - Multi-warehouse stock tracking with approval workflow
- **GST Module** - Auto CGST/SGST/IGST calculation based on state
- **Books of Accounting** - Chart of accounts, journal entries, ledgers
- **Billing System** - Quotation → Invoice → Payment workflow
- **Task Management** - Kanban board with drag-drop functionality
- **Ticket System** - Helpdesk with SLA tracking
- **Shipment Tracking** - Logistics management
- **WhatsApp Integration** - Auto-capture customer data from WhatsApp
- **Reports** - GST summary, sales, inventory reports (PDF/Excel export)

### Technical Stack
- **Framework**: Laravel 12
- **Database**: MySQL with Eloquent ORM
- **Authentication**: Laravel built-in auth with session
- **Authorization**: Spatie Laravel Permission package
- **Templating**: Blade (pure server-side rendering)
- **Frontend**: Bootstrap 5 (CDN)
- **Charts**: Chart.js (CDN)
- **Tables**: DataTables.js with export functionality (CDN)
- **Alerts**: SweetAlert2 (CDN)
- **PDF**: Laravel DomPDF
- **Excel**: Maatwebsite Excel
- **Activity Logging**: Spatie Activity Log

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- MySQL 5.7+ or MariaDB
- Apache/Nginx web server

### Setup Steps

1. **Clone Repository**
```bash
git clone <repository-url>
cd <project-directory>
```

2. **Install Dependencies**
```bash
composer install
```

3. **Environment Configuration**
```bash
cp .env.example .env
```

Edit `.env` file:
```env
APP_NAME="HisabMittra ERP+CRM"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hisabmittra_erp
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
```

4. **Generate Application Key**
```bash
php artisan key:generate
```

5. **Create Database**
Create a MySQL database named `hisabmittra_erp`:
```sql
CREATE DATABASE hisabmittra_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

6. **Run Migrations**
```bash
php artisan migrate
```

7. **Publish Spatie Packages**
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider"
```

8. **Seed Database**
```bash
php artisan db:seed
```

This creates:
- Admin user: `admin@hisabmittra.com` / `password`
- Employee user: `employee@hisabmittra.com` / `password`
- Sample products, customers, and chart of accounts

9. **Create Storage Link**
```bash
php artisan storage:link
```

10. **Start Development Server**
```bash
php artisan serve
```

Visit `http://localhost:8000`

## Default Credentials

**Admin Access:**
- Email: `admin@hisabmittra.com`
- Password: `password`

**Employee Access:**
- Email: `employee@hisabmittra.com`
- Password: `password`

## Project Structure

```
app/
├── Http/Controllers/
│   ├── Admin/           # Admin panel controllers
│   ├── Employee/        # Employee panel controllers
│   └── Auth/            # Authentication controllers
├── Models/              # Eloquent models with relationships
└── Services/            # Business logic services
    ├── GstCalculationService.php
    └── InvoiceService.php

resources/views/
├── layouts/
│   └── app.blade.php    # Main layout
├── components/          # Reusable Blade components
│   ├── sidebar.blade.php
│   ├── topbar.blade.php
│   ├── stat-widget.blade.php
│   └── card.blade.php
├── admin/               # Admin panel views
├── employee/            # Employee panel views
└── auth/                # Login views

public/css/
└── hisabmittra-theme.css    # Custom HisabMittra brand theme

database/
├── migrations/          # Database migrations (25+ tables)
└── seeders/            # Database seeders
```

## Key Routes

### Admin Panel (`/admin/*`)
- Dashboard: `/admin/dashboard`
- Employees: `/admin/employees`
- Customers: `/admin/customers`
- Products: `/admin/products`
- Inventory: `/admin/inventory`
- Invoices: `/admin/invoices`
- Tasks: `/admin/tasks`
- Tickets: `/admin/tickets`
- Reports: `/admin/reports`
- Settings: `/admin/settings`

### Employee Panel (`/employee/*`)
- Dashboard: `/employee/dashboard`
- My Tasks: `/employee/tasks`
- My Tickets: `/employee/tickets`
- My Leads: `/employee/leads`

## Features Guide

### GST Calculation
The system automatically calculates CGST/SGST for intrastate transactions and IGST for interstate transactions based on customer and company state.

### Invoice Generation
1. Create quotation
2. Convert to invoice
3. System auto-calculates GST
4. Add payments
5. Download PDF

### Task Management
- Kanban board view with 4 columns: To-Do, In Progress, Review, Done
- Drag-and-drop to change status
- Assign to employees, set priority and due dates
- Comments and file attachments

### Inventory Management
- Stock-in and stock-out transactions
- Approval workflow (employee creates, admin approves)
- Low stock alerts
- Multi-warehouse support

### Reports & Export
- All data tables support Excel/PDF export via DataTables
- Invoice PDF with company letterhead
- GST summary reports
- Sales and inventory reports

## Customization

### Brand Colors
Edit `public/css/hisabmittra-theme.css`:
```css
:root {
    --primary-color: #2563eb;
    --secondary-color: #1e40af;
    --accent-color: #3b82f6;
}
```

### Company Settings
Admin can update via `/admin/settings`:
- Company name, GSTIN, address, state
- Contact details
- WhatsApp API credentials

## Queue Setup (Optional)

For WhatsApp integration and notifications:

1. Configure queue driver in `.env`:
```env
QUEUE_CONNECTION=database
```

2. Run queue worker:
```bash
php artisan queue:work
```

## Production Deployment

1. Set environment to production:
```env
APP_ENV=production
APP_DEBUG=false
```

2. Optimize:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
composer install --optimize-autoloader --no-dev
```

3. Set proper file permissions:
```bash
chmod -R 775 storage bootstrap/cache
```

## Security

- CSRF protection enabled
- Password hashing with bcrypt
- SQL injection prevention via Eloquent ORM
- XSS protection via Blade escaping
- Role-based access control
- Activity logging for audit trails

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License

Proprietary - HisabMittra.

## Support

For issues or questions, contact the development team.
