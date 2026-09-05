# HisabMittra ERP+CRM System Documentation

## Table of Contents
1. [System Overview](#system-overview)
2. [Architecture](#architecture)
3. [Database Schema](#database-schema)
4. [Module Documentation](#module-documentation)
5. [API Endpoints](#api-endpoints)
6. [Business Logic](#business-logic)

## System Overview

HisabMittra ERP+CRM is a comprehensive business management system built with Laravel 12, designed specifically for B2B operations with integrated GST compliance, inventory management, CRM, and accounting modules.

### Key Highlights
- **Zero Build Tools**: Pure Laravel Blade, no npm/vite required
- **CDN-Based Assets**: All JS/CSS loaded via CDN
- **Production Ready**: Complete with security, logging, and backups
- **Brand Themed**: HisabMittra brand colors and design

## Architecture

### Technology Stack
```
Frontend Layer:
├── Bootstrap 5 (CSS Framework)
├── Chart.js (Analytics)
├── DataTables.js (Data Tables)
├── SweetAlert2 (Notifications)
└── SortableJS (Drag & Drop)

Backend Layer:
├── Laravel 12 (Framework)
├── MySQL (Database)
├── Spatie Permission (Authorization)
├── Spatie Activity Log (Audit Trail)
├── DomPDF (PDF Generation)
└── Maatwebsite Excel (Export)
```

### Application Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/          # Admin panel controllers
│   │   ├── Employee/       # Employee panel controllers
│   │   └── Auth/           # Authentication
│   └── Middleware/         # Custom middleware
├── Models/                 # Eloquent models (20+ models)
├── Services/               # Business logic services
│   ├── GstCalculationService
│   ├── InvoiceService
│   └── ReportService
└── Notifications/          # Laravel notifications
```

## Database Schema

### Core Tables (25 tables)

#### 1. users
Primary user table with soft deletes
```sql
- id, name, email, password
- phone, department, status
- last_login_at
- timestamps, soft_deletes
```

#### 2. roles & permissions (Spatie)
```sql
roles: id, name, guard_name
permissions: id, name, guard_name
model_has_roles: user_id, role_id
role_has_permissions: role_id, permission_id
```

#### 3. customers
```sql
- id, name, email, phone, gstin
- address, city, state, pin
- source (manual/whatsapp/web)
- timestamps, soft_deletes
```

#### 4. leads
```sql
- id, customer_id, assigned_to
- title, stage, source, value
- notes, follow_up_date
- timestamps
```

#### 5. products
```sql
- id, sku, name, description
- category, unit, hsn_code
- price, tax_rate
- reorder_level, current_stock
- timestamps, soft_deletes
```

#### 6. warehouses
```sql
- id, name, location, manager_id
- timestamps
```

#### 7. stock_transactions
```sql
- id, product_id, warehouse_id
- type (in/out), quantity
- reference_no, notes
- created_by, approved_by, approved_at
- timestamps
```

#### 8. quotations
```sql
- id, quotation_no, customer_id
- date, valid_till
- subtotal, tax_amount, total
- status, notes, created_by
- timestamps, soft_deletes
```

#### 9. quotation_items
```sql
- id, quotation_id, product_id
- description, quantity
- unit_price, tax_rate, tax_amount, total
- timestamps
```

#### 10. invoices
```sql
- id, invoice_no, customer_id, quotation_id
- invoice_date, due_date
- subtotal, cgst, sgst, igst, total
- payment_status, paid_amount
- notes, created_by
- timestamps, soft_deletes
```

#### 11. invoice_items
```sql
- id, invoice_id, product_id
- description, hsn_code, quantity
- unit_price, tax_rate
- cgst, sgst, igst, total
- timestamps
```

#### 12. payments
```sql
- id, invoice_id
- payment_date, amount
- method, reference, notes
- created_by, timestamps
```

#### 13. accounts (Chart of Accounts)
```sql
- id, account_code, account_name
- account_type (asset/liability/equity/income/expense)
- parent_id, opening_balance, current_balance
- timestamps
```

#### 14. journal_entries
```sql
- id, entry_no, date
- narration, created_by
- timestamps
```

#### 15. journal_entry_lines
```sql
- id, journal_entry_id, account_id
- debit, credit
- timestamps
```

#### 16. tasks
```sql
- id, title, description
- assigned_to, created_by
- status (todo/progress/review/done)
- priority (low/medium/high)
- due_date, completed_at
- timestamps, soft_deletes
```

#### 17. task_comments
```sql
- id, task_id, user_id, comment
- timestamps
```

#### 18. task_attachments
```sql
- id, task_id, file_name, file_path
- uploaded_by, timestamps
```

#### 19. tickets
```sql
- id, ticket_no, customer_id
- title, description
- priority (low/medium/high/urgent)
- status (open/progress/resolved/closed)
- assigned_to, category
- sla_due_at, resolved_at, closed_at
- created_by, timestamps, soft_deletes
```

#### 20. ticket_comments
```sql
- id, ticket_id, user_id
- comment, is_internal
- timestamps
```

#### 21. shipments
```sql
- id, shipment_no, invoice_id
- courier_name, tracking_no, vehicle_no
- dispatch_date, expected_delivery
- status (dispatched/transit/delivered)
- delivered_at, notes, created_by
- timestamps
```

#### 22. whatsapp_messages
```sql
- id, customer_id, phone
- message, direction (in/out)
- whatsapp_id, processed
- timestamps
```

#### 23. company_settings
```sql
- id, key, value
- timestamps
```

#### 24. notifications (Laravel)
```sql
- id (uuid), type
- notifiable_type, notifiable_id
- data (json), read_at
- timestamps
```

#### 25. activity_log (Spatie)
```sql
- id, log_name, description
- subject_type, subject_id
- causer_type, causer_id
- properties (json)
- timestamps
```

## Module Documentation

### 1. Employee Management Module

**Location**: `app/Http/Controllers/Admin/EmployeeController.php`

**Features**:
- Create/Edit/Deactivate employees
- Assign roles (Admin/Employee)
- Set department and contact details
- Track last login time
- View activity logs

**Routes**:
```php
GET  /admin/employees         # List all
GET  /admin/employees/create  # Create form
POST /admin/employees         # Store
GET  /admin/employees/{id}    # View details
GET  /admin/employees/{id}/edit  # Edit form
PUT  /admin/employees/{id}    # Update
DELETE /admin/employees/{id}  # Soft delete
```

**Permissions Required**: `manage-employees`

### 2. Customer Management Module

**Location**: `app/Http/Controllers/Admin/CustomerController.php`

**Features**:
- CRUD operations for customers
- GSTIN validation (regex pattern)
- Track customer source (manual/whatsapp)
- View customer history (invoices, leads, tickets)

**GSTIN Validation**:
```php
Pattern: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/
Example: 07AAACH7409R1ZZ
```

### 3. GST Module

**Location**: `app/Services/GstCalculationService.php`

**Business Logic**:

**Same State (Intrastate)**:
```
Tax Amount = (Amount × Tax Rate) / 100
CGST = Tax Amount / 2
SGST = Tax Amount / 2
IGST = 0
```

**Different State (Interstate)**:
```
Tax Amount = (Amount × Tax Rate) / 100
CGST = 0
SGST = 0
IGST = Tax Amount
```

**Example**:
```php
Product Price: ₹1,000
Tax Rate: 18%
Customer State: Delhi
Company State: Delhi (Same)

Result:
- Subtotal: ₹1,000
- CGST (9%): ₹90
- SGST (9%): ₹90
- Total: ₹1,180
```

### 4. Invoice Module

**Location**: `app/Http/Controllers/Admin/InvoiceController.php`

**Workflow**:
1. Create invoice with line items
2. System auto-calculates GST per item
3. Aggregates total CGST/SGST/IGST
4. Track payment status (unpaid/partial/paid)
5. Record payments
6. Generate PDF

**Invoice Number Format**:
```
INV-{YEAR}{MONTH}-{SEQUENCE}
Example: INV-202607-0001
```

### 5. Inventory Module

**Features**:
- Multi-warehouse stock tracking
- Stock-in/out transactions
- Approval workflow
- Low stock alerts
- Product-wise and warehouse-wise reports

**Approval Flow**:
```
Employee → Creates transaction (pending)
Admin → Reviews transaction
Admin → Approves (updates stock)
```

### 6. Task Management Module

**Features**:
- Kanban board (4 columns)
- Drag-drop status change
- Task assignment
- Priority levels
- Comments and attachments

**Kanban Columns**:
1. To-Do (todo)
2. In Progress (progress)
3. Review (review)
4. Done (done)

### 7. Ticket Management Module

**Features**:
- Helpdesk ticket system
- SLA tracking
- Priority-based auto-SLA
- Internal/external comments
- Status tracking

**SLA Hours by Priority**:
```
Urgent: 4 hours
High: 24 hours
Medium: 48 hours
Low: 72 hours
```

### 8. Accounting Module

**Features**:
- Chart of Accounts
- Journal Entries (Double-entry bookkeeping)
- Balance Sheet
- Profit & Loss Statement
- Account-wise Ledger

**Account Types**:
- Asset
- Liability
- Equity
- Income
- Expense

### 9. Reports Module

**Available Reports**:

1. **GST Summary**: CGST/SGST/IGST totals
2. **Sales Report**: Revenue analysis
3. **Inventory Report**: Stock levels
4. **Customer Report**: Customer-wise transactions
5. **Task Performance**: Employee productivity
6. **Ticket Analytics**: Support metrics

**Export Formats**: Excel, PDF, Print

### 10. WhatsApp Integration

**Webhook Endpoint**: `/admin/whatsapp/webhook`

**Flow**:
1. Incoming message hits webhook
2. Queue job processes message
3. Auto-create/update customer
4. Store message in database
5. Optional: Auto-create lead

**Configuration**:
```env
WHATSAPP_API_URL=https://graph.facebook.com/v17.0
WHATSAPP_API_TOKEN=your_token
WHATSAPP_PHONE_NUMBER_ID=your_phone_id
```

## Business Logic

### Invoice Calculation Logic

```php
// Per Item
$lineTotal = $quantity × $unitPrice;
$taxAmount = ($lineTotal × $taxRate) / 100;

if ($customerState === $companyState) {
    $cgst = $taxAmount / 2;
    $sgst = $taxAmount / 2;
    $igst = 0;
} else {
    $cgst = 0;
    $sgst = 0;
    $igst = $taxAmount;
}

$itemTotal = $lineTotal + $taxAmount;

// Invoice Totals
$subtotal = Σ(lineTotal);
$totalCGST = Σ(cgst);
$totalSGST = Σ(sgst);
$totalIGST = Σ(igst);
$grandTotal = $subtotal + $totalCGST + $totalSGST + $totalIGST;
```

### Stock Transaction Logic

```php
// Stock In
$product->current_stock += $quantity;

// Stock Out
if ($product->current_stock >= $quantity) {
    $product->current_stock -= $quantity;
} else {
    throw Exception("Insufficient stock");
}

// Low Stock Check
if ($product->current_stock <= $product->reorder_level) {
    Notification::send($admin, new LowStockAlert($product));
}
```

### Payment Status Logic

```php
if ($invoice->paid_amount == 0) {
    $status = 'unpaid';
} elseif ($invoice->paid_amount >= $invoice->total) {
    $status = 'paid';
} else {
    $status = 'partial';
}
```

## Security Features

1. **Authentication**: Laravel session-based
2. **Authorization**: Role-based (Spatie Permission)
3. **CSRF Protection**: Enabled on all forms
4. **SQL Injection**: Prevented via Eloquent ORM
5. **XSS Protection**: Blade auto-escaping
6. **Password Hashing**: Bcrypt
7. **Activity Logging**: All CRUD operations logged
8. **Soft Deletes**: Data never permanently deleted

## Performance Optimization

1. **Eager Loading**: Prevents N+1 queries
2. **Database Indexing**: On frequently queried columns
3. **Query Caching**: For reports and dashboards
4. **CDN Assets**: Faster load times
5. **Opcache**: PHP bytecode caching (production)

## Maintenance

### Daily Tasks
- Monitor error logs: `storage/logs/laravel.log`
- Check disk space
- Verify backup completion

### Weekly Tasks
- Review activity logs
- Update stock levels
- Generate weekly reports

### Monthly Tasks
- Database optimization
- Security updates
- User access review

## Backup Strategy

```bash
# Database Backup
mysqldump -u root -p hisabmittra_erp > backup_$(date +%Y%m%d).sql

# File Backup
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/

# Automated Backup (cron)
0 2 * * * /path/to/backup-script.sh
```

## Support & Troubleshooting

### Common Issues

**Issue**: Database connection error
**Solution**: Check `.env` database credentials

**Issue**: Permission denied on storage
**Solution**: `chmod -R 775 storage bootstrap/cache`

**Issue**: Class not found
**Solution**: `composer dump-autoload`

**Issue**: Route not found
**Solution**: `php artisan route:clear`

## Version History

- v1.0.0 (2026-07-15): Initial release
  - All core modules implemented
  - GST compliance
  - Multi-warehouse inventory
  - Full accounting system
  - Task & ticket management
  - WhatsApp integration ready

## License

Proprietary - HisabMittra.
All rights reserved.
