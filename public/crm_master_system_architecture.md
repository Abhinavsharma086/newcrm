# Metric Qube CRM — Master System Manual & Architecture Guide

This master guide describes every module, sidebar section, employee registration role, and portal transition present in your CRM.

---

## 📂 Part 1: Admin Sidebar Sections & Features

When logged in as an Admin, your sidebar is divided into four primary operational sections:

### 1. Dashboard
* **Route**: `/admin/dashboard`
* **Purpose**: Overview of active customers, confirmed appointments, outstanding invoice amounts, and monthly sales summaries.

### 2. CRM Section
* **Customers**: `/admin/customers`
  - Manage consumer records. Contains tools to **Export** and **Import** customer records via Excel sheets.
  - Controls technician assignment settings.
* **Societies**: `/admin/societies`
  - Master society database. Admin assigns default subcontractors/contractors to specific societies so that new customers in those locations are auto-assigned to the correct technician.
* **Confirmed Appointments**: `/admin/appointments`
  - Shows scheduled technician visits. Includes the **AI Smart Scheduler** which groups pending visits by society to minimize executive travel times.
* **Burner Types & Meter Types**: `/admin/burner-types` & `/admin/meter-types`
  - Master lists of authorized gas burners and meters.
* **Contractors**: `/admin/contractors`
  - Subcontractors authorized to carry out installations.

### 3. Billing & Purchase Orders Section
* **Client POs (WO)**: `/admin/client-pos`
  - Receive work orders from clients, log item quantities, and trace day-to-day work execution with date range filters.
* **Vendor POs**: `/admin/vendor-pos`
  - Issue POs to suppliers with automatic sequential numbering format (`MQ/MonthYear/0001`).
* **Vendor Invoices**: `/admin/vendor-invoices`
  - Record subcontractor bills, run **3-Way checks** to match PO limits, verify GST Input Tax Credits (ITC), and approve payment vouchers.
* **Quotations**: `/admin/quotations`
  - Draft proposals and convert them to Sales Invoices in one click.
* **Sales Invoices**: `/admin/invoices`
  - Raise invoices directly to **Clients** (independent of the general Customer CRM database) and process payments.
* **Credit/Debit Notes**: `/admin/notes`
  - Post sales adjustments.

### 4. Inventory Section
* **Products & Services**: `/admin/products`
  - Inventory items master list.
* **Inventory Logs / Stock Transactions**: `/admin/inventory`
  - Record stock additions, stock assignments to field technicians, and view reconciliation reports.

---

## 👥 Part 2: Employee Registration & Roles Mapping

When you create a new employee inside the **Employee Management** screen (`/admin/employees`), you must map them to one of the specific roles:

### 🔑 The 5 Available User Roles:
1. **Admin**:
   - Complete system access. Oversees finances, deletes records, approves invoices, and manages settings.
2. **Business User (Manager)**:
   - Evaluates performance metrics, reviews quotations, and manages customer assignments without access to system configurations/backups.
3. **Employee (Field Coordinator / Backoffice)**:
   - Coordinates appointments, tracks tasks, and processes documents received from the field.
4. **Field Technician**:
   - Accesses the CRM on mobile devices. Responsible for logging LMC pipe lengths, burner types, and capturing the **5 Guided Installation Photos** and videos.
5. **Field Marketing**:
   - Identifies leads, records customer interests, and schedules initial appointments.

---

## 📱 Part 3: Employee / Technician Portal Operations

When an Employee or Technician logs in, they access a simplified mobile-friendly dashboard:

### 1. Dashboard
- View assigned tasks, customer lists, and scheduled appointments.

### 2. My Customers
- The technician can update installation parameters for assigned customers:
  - Input **Meter No.**, **Meter Type**, and **MLC Pipe Length**.
  - Capture **Guided Evidences** with sample overlays:
    1. **Inside Kitchen Photo** (burner flame and piping view)
    2. **Meter Photo** (front dial numbers close-up)
    3. **Outside Kitchen Photo** (balcony riser line)
    4. **RFC & JMR forms photos**

### 3. My Tasks & Tickets
- Access work tickets issued by the admin, add comments, and update status logs.
