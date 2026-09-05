import paramiko
import os
import sys

host = "46.202.183.28"
port = 65002
username = "u425316205"
password = "Hisab@20026"

local_base = r"C:\xampp\htdocs\NewCrm"
remote_base = "/home/u425316205/domains/hisabmittra.in/public_html/crm"

files_to_upload = [
    "app/Http/Controllers/Admin/CustomerController.php",
    "app/Http/Controllers/Admin/InventoryController.php",
    "app/Http/Controllers/Admin/InvoiceController.php",
    "app/Http/Controllers/Admin/SupplierController.php",
    "app/Models/Customer.php",
    "app/Models/Supplier.php",
    "app/Services/InvoiceService.php",
    "resources/views/admin/appointments/index.blade.php",
    "resources/views/admin/client_pos/index.blade.php",
    "resources/views/admin/customers/edit.blade.php",
    "resources/views/admin/customers/index.blade.php",
    "resources/views/admin/customers/show.blade.php",
    "resources/views/admin/inventory/logs.blade.php",
    "resources/views/admin/invoices/create.blade.php",
    "resources/views/admin/invoices/index.blade.php",
    "resources/views/admin/invoices/pdf.blade.php",
    "resources/views/admin/leads/index.blade.php",
    "resources/views/admin/suppliers/create.blade.php",
    "resources/views/admin/suppliers/edit.blade.php",
    "resources/views/admin/suppliers/index.blade.php",
    "resources/views/admin/vendor_invoices/index.blade.php",
    "resources/views/admin/vendor_pos/index.blade.php",
    "resources/views/components/sidebar.blade.php",
    "resources/views/employee/customers/edit.blade.php",
    "resources/views/employee/customers/index.blade.php",
    "resources/views/employee/customers/show.blade.php",
    "database/migrations/2026_08_24_122435_add_invoice_type_to_invoices_table.php",
    "database/migrations/2026_08_24_150000_add_mlc_details_to_customers_table.php",
    "database/migrations/2026_08_25_052615_add_phone_2_to_suppliers_table.php"
]

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect(host, port, username, password)
    sftp = ssh.open_sftp()
    
    for f in files_to_upload:
        local_path = os.path.join(local_base, f.replace('/', '\\'))
        remote_path = f"{remote_base}/{f}"
        
        print(f"Uploading {local_path} to {remote_path}...")
        try:
            sftp.put(local_path, remote_path)
            print(f"Successfully uploaded {f}")
        except Exception as e:
            print(f"Error uploading {f}: {e}")
            
    sftp.close()
    
    print("Running post-upload commands...")
    commands = [
        "cd /home/u425316205/domains/hisabmittra.in/public_html/crm && php artisan migrate --force",
        "cd /home/u425316205/domains/hisabmittra.in/public_html/crm && php artisan view:clear",
        "cd /home/u425316205/domains/hisabmittra.in/public_html/crm && php artisan cache:clear",
        "cd /home/u425316205/domains/hisabmittra.in/public_html/crm && php artisan config:clear"
    ]
    
    for cmd in commands:
        stdin, stdout, stderr = ssh.exec_command(cmd)
        print(f"Command: {cmd}\nOutput: {stdout.read().decode()}\nErrors: {stderr.read().decode()}")
        
finally:
    ssh.close()
