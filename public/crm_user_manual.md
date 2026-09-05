# Metric Qube CRM — Complete Functional User Manual

This manual explains how to use the newly implemented custom features in your CRM: Client-Side Purchase Orders (WO), Vendor POs, 3-Way Matching, Direct Expense Billing, and Technician Verification Uploads.

---

## 🔵 Section 1: Client-Side Workflow (You Receive PO → Execute → Raise Sales Invoice)

This pathway is used when a client gives you a Purchase Order or Work Order, you track day-to-day work progress, and then bill them based on actual execution.

### Step 1: Record the Client PO
1. Open the sidebar and click **Client POs (WO)**.
2. Click **Receive Client PO** to record the contract.
3. Fill in the **PO Number**, **Client (Billed To)**, **Site/Locality Name**, **PO Date**, and **Retention %**.
4. In the items table, enter line items with their expected **Quantity**, **Unit (e.g., Mtr, Job)**, **Rate**, and **GST %**.
5. Save the PO. The system automatically computes the total contract value.

### Step 2: Date-Wise Progress Tracking (Measurement Sheet)
1. Go to the **Client POs** list and click **Track Progress** on the PO.
2. In the right panel (**Log Daily Execution**):
   * Select the specific PO Line Item.
   * Input the **Execution Date**.
   * Enter the **Qty Executed** on that date.
   * Add any site comments or remarks.
3. On the left side, you can filter logs using the **Start Date** and **End Date** filters. This helps you audit how much quantity was executed during a specific billing cycle.

### Step 3: Raise Sales Invoice (RA Bill)
1. In the Client PO details screen, click **Generate Invoice**.
2. The system automatically drafts a Sales Invoice, pre-filling client details and loading the executed quantities. The tax splits (CGST/SGST/IGST) are auto-calculated based on state rules.

---

## 🟡 Section 2: Vendor / Subcontractor Workflow (You Issue PO → Verify Bill → Pay)

This pathway is used when you hire a subcontractor or buy materials from a vendor.

### Step 1: Issue Vendor PO (Auto-Numbering)
1. Click **Vendor POs** in the sidebar.
2. Click **Issue Vendor PO**.
3. Select the **Vendor (Supplier)**, PO Date, project location, TDS %, and Retention %.
4. **PO Number Generation**: Leave the **PO Number** field blank to auto-generate. The system will use the logic: `MQ/[MonthYear]/[AutoNo]` (e.g., `MQ/Aug26/0001`).
5. Add items with their quantities, rates, and GST. Save and dispatch.

### Step 2: Record Bill & 3-Way Matching
1. Go to **Vendor Invoices** and click **Record Vendor Bill**.
2. **Select Billing Pathway**:
   * **Pathway A: Match Against PO**: Select the Vendor PO from the dropdown. The system automatically fetches the vendor details and pre-fills taxable values, GST, and TDS.
     * **3-Way Match Check**: When you submit the bill, the CRM compares the billed amounts against the PO values (including a 2% variance tolerance). If it exceeds the limits, it flags it as `3-Way Matching Variance Flagged` (Red Alert). If it matches, it displays `3-Way Match Passed` (Green Alert).
   * **Pathway B: Direct Payment (Without PO)**: Select `-- Direct Payment (Without PO) --` for recurring expenses like rent or electricity. 
     * **3-Way Check Bypass**: The system flags this as a direct expense and bypasses matching checks, allowing the bill to go straight to the payment stage.

### Step 3: Accounts Approval & Payment
1. Open the recorded Vendor Invoice. If it is flagged, the auditor reviews it.
2. Click **Approve Bill** to authorize the voucher.
3. In the right-hand panel (**Record Payment Voucher**), enter the amount paid and click **Record Bank Payment** to update the outstanding balance.

---

## 🟢 Section 3: Technician Verification Guidelines (Photos & Videos)

This section ensures that technicians submit consistent evidence from the field.

### 📷 1. Guided Photo Uploads (Customer Page)
When editing technical data on the customer edit forms (both in the Admin and Technician portal), the old general photo upload is replaced with **5 specific drop-boxes**:
1. **Inside Kitchen Photo**: Must show a front view of the burner and gas pipeline. A sample guide image is displayed on screen for reference.
2. **Meter Photo (3 Angles)**: Must show a closed front-facing shot of the digital natural gas meter showing the initial dial reading. A sample guide is shown on screen.
3. **Outside Kitchen Photo**: Must show the external balcony pipe segment and tapping point. A sample guide is shown on screen.
4. **RFC Report Image**: Photo of the completed RFC verification form.
5. **JMR Report Image**: Photo of the Joint Measurement Report sheet.

### 🎥 2. Video Verification (Appointments Page)
When scheduling/editing appointments in the **Confirmed Appointments** menu:
1. Select **Edit Appointment** on a scheduled customer case.
2. Select the **Burner Type** from the dropdown option: **Normal** or **Hob Stove**.
3. Upload the required videos:
   * **Kitchen & Burner Video**: A short walk-around video of the kitchen gas stove.
   * **External Riser Video**: A video of the outside riser pipeline tapping segment.
4. Managers can view and play these videos directly inside the appointment profile page by clicking the **Play Video** links.
