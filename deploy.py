import paramiko
import sys
import os

host = '46.202.183.28'
port = 65002
user = 'u425316205'
password = 'Hisab@20026'

# Corrected path to point to the CRM directory instead of the main domain root
remote_base = '/home/u425316205/domains/hisabmittra.in/public_html/crm/'

files_to_deploy = [
    'resources/views/admin/quotations/create.blade.php',
    'resources/views/admin/quotations/edit.blade.php',
    'resources/views/admin/quotations/show.blade.php',
    'resources/views/admin/quotations/pdf.blade.php',
    'app/Http/Controllers/Admin/QuotationController.php',
    'resources/views/admin/customers/create.blade.php',
    'app/Http/Controllers/Admin/CustomerController.php',
    'app/Models/Customer.php',
    'routes/web.php',
    'app/Http/Controllers/Admin/ApiController.php',
    'app/Services/GstApiService.php',
    'app/Models/QuotationItem.php',
    'database/migrations/2026_08_19_081145_add_image_path_to_quotation_items_table.php',
    'app/Models/Product.php',
    'app/Http/Controllers/Admin/ProductController.php',
    'resources/views/admin/products/create.blade.php',
    'resources/views/admin/products/edit.blade.php',
    'resources/views/admin/products/index.blade.php',
    'database/migrations/2026_08_19_081729_add_image_path_to_products_table.php',
    'database/migrations/2026_08_19_083623_add_terms_conditions_to_quotations_table.php',
    'database/migrations/2026_08_19_193000_add_gst_details_to_quotations_table.php',
    'app/Models/Quotation.php',
    'resources/views/admin/quotations/index.blade.php',
    'resources/views/admin/invoices/create.blade.php',
    'resources/views/admin/invoices/index.blade.php',
    'resources/views/admin/invoices/show.blade.php',
    'resources/views/admin/invoices/pdf.blade.php',
    'app/Http/Controllers/Admin/InvoiceController.php',
    'database/migrations/2026_08_19_180000_add_bill_fields_to_vendor_pos_table.php',
    'app/Models/VendorPo.php',
    'app/Models/VendorPoItem.php',
    'app/Http/Controllers/Admin/VendorPoController.php',
    'resources/views/admin/vendor_pos/create.blade.php',
    'resources/views/admin/vendor_pos/show.blade.php',
    'resources/views/admin/vendor_pos/pdf.blade.php',
    'resources/views/admin/vendor_pos/index.blade.php',
    'database/migrations/2026_08_19_190000_add_checklist_to_tasks_table.php',
    'app/Models/Task.php',
    'app/Http/Controllers/Admin/TaskController.php',
    'resources/views/admin/tasks/index.blade.php',
    'resources/views/admin/tasks/create.blade.php',
    'resources/views/admin/tasks/edit.blade.php',
    'resources/views/admin/tasks/show.blade.php'
]

print(f"Connecting to {host}:{port} as {user}...")

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, port, user, password, timeout=10)
    
    print("Connected! Starting upload...")
    sftp = ssh.open_sftp()
    
    for f in files_to_deploy:
        local_path = f.replace('/', '\\')
        remote_path = remote_base + f
        print(f"Uploading {f}...")
        
        # Ensure remote directory exists
        remote_dir = os.path.dirname(remote_path)
        ssh.exec_command(f'mkdir -p {remote_dir}')
        
        sftp.put(local_path, remote_path)
        
    sftp.close()

    print("Running remote migration...")
    stdin, stdout, stderr = ssh.exec_command(f'cd {remote_base} && php artisan migrate --force')
    print(stdout.read().decode())
    print(stderr.read().decode())

    ssh.close()
    print("Deployment successful to CRM directory!")
    
except Exception as e:
    print(f"Deployment failed: {e}")
    sys.exit(1)
