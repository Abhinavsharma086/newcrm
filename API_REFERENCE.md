# API Reference - Havells ERP+CRM

## Overview

This document provides a comprehensive reference for all routes, controllers, and methods in the Havells ERP+CRM system.

## Authentication

All routes except login are protected by the `auth` middleware. Additionally, admin routes require the `role:admin` middleware, and employee routes require the `role:employee` middleware.

### Auth Endpoints

#### Login
```
GET  /login
POST /login

Request Body:
{
    "email": "admin@havells.com",
    "password": "password",
    "remember": true (optional)
}

Response:
- Success: Redirect to dashboard
- Error: Validation errors
```

#### Logout
```
POST /logout

Response: Redirect to login page
```

---

## Admin Panel Routes

Base URL: `/admin`

All admin routes require authentication + admin role.

### Dashboard

#### Get Dashboard Data
```
GET /admin/dashboard

Response: Dashboard view with:
- Total employees, customers, revenue
- Pending invoices, open tickets
- Active tasks, low stock alerts
- GST summary charts
- Monthly revenue chart
- Recent invoices and tickets
```

---

### Employee Management

#### List Employees
```
GET /admin/employees

Response: Index view with DataTables
- All employees with roles and status
```

#### Create Employee Form
```
GET /admin/employees/create

Response: Create form view
- Role selection
- Department input
```

#### Store Employee
```
POST /admin/employees

Request Body:
{
    "name": "John Doe",
    "email": "john@havells.com",
    "password": "password",
    "password_confirmation": "password",
    "phone": "9876543210",
    "department": "Sales",
    "status": "active",
    "role": "employee"
}

Validation:
- name: required, string, max:255
- email: required, email, unique
- password: required, min:8, confirmed
- phone: nullable, string, max:20
- department: nullable, string, max:100
- status: required, in:active,inactive
- role: required, exists:roles,name

Response: Redirect to employee list with success message
```

#### View Employee
```
GET /admin/employees/{id}

Response: Show view with:
- Employee details
- Assigned tasks
- Assigned tickets
- Activity logs
```

#### Edit Employee Form
```
GET /admin/employees/{id}/edit

Response: Edit form view
```

#### Update Employee
```
PUT /admin/employees/{id}

Request Body: Same as store (password optional)

Response: Redirect to employee list with success message
```

#### Delete Employee
```
DELETE /admin/employees/{id}

Response: Soft delete, redirect with success message
```

---

### Customer Management

#### List Customers
```
GET /admin/customers

Response: Index view with DataTables
```

#### Create Customer
```
GET  /admin/customers/create
POST /admin/customers

Request Body:
{
    "name": "ABC Electricals",
    "email": "abc@electricals.com",
    "phone": "9876543212",
    "gstin": "07AAABC1234A1Z5",
    "address": "123 Main Street",
    "city": "Delhi",
    "state": "Delhi",
    "pin": "110001",
    "source": "manual"
}

Validation:
- name: required, string, max:255
- email: nullable, email
- phone: required, string, max:20
- gstin: nullable, string, size:15
- state: nullable, string, max:100
- source: required, in:manual,whatsapp,web

GSTIN Pattern: /^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/
```

#### View Customer
```
GET /admin/customers/{id}

Response: Show view with:
- Customer details
- Invoice history
- Lead history
- Ticket history
```

#### Update/Delete Customer
```
GET    /admin/customers/{id}/edit
PUT    /admin/customers/{id}
DELETE /admin/customers/{id}
```

---

### Product Management

#### List Products
```
GET /admin/products

Response: DataTables with:
- SKU, name, category, price
- Tax rate, current stock
- Low stock indicator
```

#### Create Product
```
POST /admin/products

Request Body:
{
    "sku": "HAV-WIRE-001",
    "name": "Havells Lifeline Cable 1.5 sq mm",
    "description": "Single core PVC insulated cable",
    "category": "Cables & Wires",
    "unit": "meter",
    "hsn_code": "85444290",
    "price": 25.00,
    "tax_rate": 18.00,
    "reorder_level": 500,
    "current_stock": 1000
}

Validation:
- sku: required, string, unique
- name: required, string, max:255
- unit: required, string, max:20
- price: required, numeric, min:0
- tax_rate: required, numeric, min:0, max:100
- reorder_level: required, integer, min:0
- current_stock: required, integer, min:0
```

---

### Inventory Management

#### View Inventory
```
GET /admin/inventory

Response: Inventory dashboard with:
- Total stock value
- Low stock products
- Warehouse-wise stock
```

#### Stock Transactions
```
GET /admin/inventory/stock-transactions

Response: All stock transactions with filters
```

#### Stock In
```
POST /admin/inventory/stock-in

Request Body:
{
    "product_id": 1,
    "warehouse_id": 1,
    "quantity": 100,
    "reference_no": "PO-12345",
    "notes": "New stock arrival"
}

Action: Creates pending stock transaction
```

#### Stock Out
```
POST /admin/inventory/stock-out

Request Body:
{
    "product_id": 1,
    "warehouse_id": 1,
    "quantity": 50,
    "reference_no": "SO-12345",
    "notes": "Sales order"
}

Action: Creates pending stock transaction
```

#### Approve Transaction
```
POST /admin/inventory/approve-transaction/{transaction_id}

Action:
- Updates product stock
- Sets approved_by and approved_at
- Sends notification
```

---

### Invoice Management

#### Create Invoice
```
POST /admin/invoices

Request Body:
{
    "customer_id": 1,
    "invoice_date": "2026-07-15",
    "due_date": "2026-08-15",
    "notes": "Payment terms: 30 days",
    "items": [
        {
            "product_id": 1,
            "quantity": 10,
            "unit_price": 25.00
        }
    ]
}

Business Logic:
1. Auto-generate invoice number: INV-{YEAR}{MONTH}-{SEQ}
2. Calculate GST per item based on customer state
3. If same state: CGST + SGST
4. If different state: IGST
5. Aggregate totals
6. Create invoice and items

Response: Redirect to invoice view
```

#### Download Invoice PDF
```
GET /admin/invoices/{id}/pdf

Response: PDF download with:
- Company letterhead
- Customer details
- Itemized bill with HSN codes
- GST breakdown
- Total amount
```

#### Add Payment
```
POST /admin/invoices/{id}/payment

Request Body:
{
    "payment_date": "2026-07-20",
    "amount": 1180.00,
    "method": "bank",
    "reference": "TXN123456",
    "notes": "NEFT payment"
}

Business Logic:
- Add to payments table
- Update invoice paid_amount
- Update payment_status (unpaid/partial/paid)
```

---

### Task Management

#### List Tasks (Kanban)
```
GET /admin/tasks

Response: Kanban board view with 4 columns:
- To-Do
- In Progress
- Review
- Done

JavaScript: SortableJS for drag-drop
```

#### Create Task
```
POST /admin/tasks

Request Body:
{
    "title": "Update customer database",
    "description": "Migrate old customer records",
    "assigned_to": 2,
    "status": "todo",
    "priority": "high",
    "due_date": "2026-07-20"
}
```

#### Update Task Status (AJAX)
```
POST /admin/tasks/{id}/status

Request Body:
{
    "status": "progress"
}

Response: JSON
{
    "success": true,
    "message": "Task status updated"
}
```

#### Add Comment
```
POST /admin/tasks/{id}/comment

Request Body:
{
    "comment": "Started working on this task"
}

Response: Redirect back with success message
```

---

### Ticket Management

#### Create Ticket
```
POST /admin/tickets

Request Body:
{
    "customer_id": 1,
    "title": "Product not working",
    "description": "Customer facing issue with MCB",
    "priority": "urgent",
    "status": "open",
    "assigned_to": 2,
    "category": "Technical Support"
}

Business Logic:
- Auto-generate ticket number: TKT-{YYYYMMDD}-{SEQ}
- Calculate SLA based on priority:
  * Urgent: 4 hours
  * High: 24 hours
  * Medium: 48 hours
  * Low: 72 hours
- Set sla_due_at timestamp
```

#### Update Ticket Status
```
PUT /admin/tickets/{id}

Business Logic:
- If status changed to 'resolved': Set resolved_at
- If status changed to 'closed': Set closed_at
```

---

### Accounting Module

#### Chart of Accounts
```
GET /admin/accounts

Response: Tree view of accounts grouped by type:
- Assets
- Liabilities
- Equity
- Income
- Expenses
```

#### Create Journal Entry
```
POST /admin/accounts/journal

Request Body:
{
    "entry_no": "JE-001",
    "date": "2026-07-15",
    "narration": "Opening balance entry",
    "lines": [
        {
            "account_id": 1,
            "debit": 50000,
            "credit": 0
        },
        {
            "account_id": 5,
            "debit": 0,
            "credit": 50000
        }
    ]
}

Validation:
- Sum of debits must equal sum of credits
```

#### Balance Sheet
```
GET /admin/accounts/balance-sheet

Response: Financial statement showing:
- Assets total
- Liabilities total
- Equity total
- Balanced equation: Assets = Liabilities + Equity
```

#### Profit & Loss
```
GET /admin/accounts/profit-loss

Response: Financial statement showing:
- Total Income
- Total Expenses
- Net Profit/Loss
```

---

### Reports

#### GST Summary Report
```
GET /admin/reports/gst-summary

Query Parameters:
- from_date: 2026-07-01
- to_date: 2026-07-31

Response: Report with:
- Total CGST collected
- Total SGST collected
- Total IGST collected
- Invoice-wise breakdown

Export: Excel, PDF
```

#### Sales Report
```
GET /admin/reports/sales-report

Query Parameters:
- from_date
- to_date
- customer_id (optional)

Response: Report with:
- Total sales amount
- Customer-wise breakdown
- Product-wise breakdown
- Payment status summary
```

#### Inventory Report
```
GET /admin/reports/inventory-report

Response: Report with:
- Current stock levels
- Low stock items
- Stock value
- Warehouse-wise distribution
```

---

### Settings

#### View Settings
```
GET /admin/settings

Response: Settings form with:
- Company name, GSTIN, address
- Contact details
- WhatsApp API credentials
```

#### Update Settings
```
POST /admin/settings/update

Request Body:
{
    "company_name": "Havells India Ltd",
    "company_gstin": "07AAACH7409R1ZZ",
    "company_address": "QRG Towers, Noida",
    "company_state": "Delhi",
    "company_phone": "1800-103-7555",
    "company_email": "info@havells.com",
    "whatsapp_api_url": "https://...",
    "whatsapp_api_token": "token",
    "whatsapp_phone_number_id": "id"
}

Action: Updates company_settings table
```

---

## Employee Panel Routes

Base URL: `/employee`

All employee routes require authentication + employee role.

### Employee Dashboard
```
GET /employee/dashboard

Response: Employee dashboard with:
- My assigned tasks
- My assigned tickets
- My assigned leads
- Personal performance metrics
```

### My Tasks
```
GET  /employee/tasks
GET  /employee/tasks/{id}
POST /employee/tasks/{id}/status
POST /employee/tasks/{id}/comment
```

### My Tickets
```
GET  /employee/tickets
GET  /employee/tickets/{id}
POST /employee/tickets/{id}/comment
```

### My Leads
```
GET /employee/leads
GET /employee/leads/{id}
PUT /employee/leads/{id}

Update allowed fields:
- stage
- notes
- follow_up_date
```

---

## Service Classes

### GstCalculationService

#### calculateGst()
```php
calculateGst($amount, $taxRate, $customerState, $companyState)

Returns:
[
    'cgst' => 90,
    'sgst' => 90,
    'igst' => 0,
    'total_tax' => 180
]

Logic:
- If states match: CGST = SGST = tax/2, IGST = 0
- If states differ: CGST = SGST = 0, IGST = tax
```

#### validateGstin()
```php
validateGstin($gstin)

Returns: boolean

Pattern: ^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$
```

### InvoiceService

#### generateInvoiceNumber()
```php
generateInvoiceNumber()

Returns: String (e.g., "INV-202607-0001")

Logic:
- Prefix: INV-{YEAR}{MONTH}-
- Sequence: Last invoice + 1, padded to 4 digits
```

#### createInvoice()
```php
createInvoice($invoiceData, $items)

Returns: Invoice model

Actions:
1. Create invoice record
2. For each item:
   - Calculate line total
   - Calculate GST
   - Create invoice item
3. Recalculate invoice totals
4. Return fresh invoice
```

---

## Database Models

All models with timestamps, soft deletes where applicable.

### User
```
Relationships:
- hasMany: assignedLeads, assignedTasks, createdTasks, assignedTickets
- hasMany: managedWarehouses
- belongsToMany: roles, permissions (Spatie)

Traits: HasRoles, LogsActivity
```

### Customer
```
Relationships:
- hasMany: leads, invoices, tickets, whatsappMessages

Traits: LogsActivity, SoftDeletes
```

### Product
```
Relationships:
- hasMany: stockTransactions

Scopes:
- lowStock(): whereColumn('current_stock', '<=', 'reorder_level')

Traits: LogsActivity, SoftDeletes
```

### Invoice
```
Relationships:
- belongsTo: customer, quotation, creator
- hasMany: items, payments
- hasOne: shipment

Traits: LogsActivity, SoftDeletes
```

### Task
```
Relationships:
- belongsTo: assignee, creator
- hasMany: comments, attachments

Traits: LogsActivity, SoftDeletes
```

### Ticket
```
Relationships:
- belongsTo: customer, assignee, creator
- hasMany: comments

Traits: LogsActivity, SoftDeletes
```

---

## Events & Notifications

### Planned Notifications

#### LowStockAlert
```
Triggered: When product.current_stock <= product.reorder_level
Recipients: Admin users
Channels: database, mail (optional)
```

#### InvoiceDue
```
Triggered: Daily cron, for invoices where due_date is tomorrow
Recipients: Admin users
Channels: database, mail
```

#### TicketSLABreached
```
Triggered: When current_time > ticket.sla_due_at
Recipients: Assigned user + Admin
Channels: database, mail
```

---

## Error Handling

All controllers implement try-catch blocks and return appropriate error messages.

### Standard Error Responses

#### Validation Error
```
HTTP 302 Redirect (back)
Session: errors array
Flash: old input
```

#### Authorization Error
```
HTTP 403
Response: "Unauthorized action."
```

#### Not Found Error
```
HTTP 404
Response: "Resource not found."
```

#### Server Error
```
HTTP 500
Response: "An error occurred. Please try again."
Log: storage/logs/laravel.log
```

---

## Testing Endpoints

Use these for testing:

```bash
# Test login
curl -X POST http://localhost:8000/login \
  -d "email=admin@havells.com&password=password"

# Test dashboard (requires auth)
curl -X GET http://localhost:8000/admin/dashboard \
  -H "Cookie: laravel_session=..."

# Test create customer (requires auth + CSRF)
curl -X POST http://localhost:8000/admin/customers \
  -H "Cookie: laravel_session=..." \
  -H "X-CSRF-TOKEN: ..." \
  -d '{"name":"Test Customer","phone":"1234567890","source":"manual"}'
```

---

## Extending the API

To add new endpoints:

1. Create migration
2. Create model with relationships
3. Create controller with CRUD methods
4. Add routes to web.php
5. Create Blade views
6. Add permissions if needed
7. Update documentation

---

**Version**: 1.0.0
**Last Updated**: July 15, 2026
