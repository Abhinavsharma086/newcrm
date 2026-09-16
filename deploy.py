import paramiko
import os

host = "46.202.183.28"
port = 65002
username = "u425316205"
password = "Hisab@20026"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh.connect(host, port, username, password)

sftp = ssh.open_sftp()
base_remote = "domains/hisabmittra.in/public_html/crm"

files = [
    'app/Http/Controllers/Admin/TaskController.php',
    'app/Http/Controllers/Admin/EmployeeController.php',
    'app/Http/Controllers/Employee/TaskController.php',
    'app/Http/Controllers/Employee/CustomerController.php',
    'resources/views/admin/tasks/index.blade.php',
    'resources/views/admin/employees/index.blade.php',
    'resources/views/employee/tasks/index.blade.php',
    'resources/views/employee/tasks/show.blade.php',
    'app/Models/Customer.php',
    'resources/views/admin/customers/show.blade.php',
    'resources/views/admin/reports/index.blade.php',
    'resources/views/employee/customers/show.blade.php',
    'resources/views/components/topbar.blade.php'
]

for f in files:
    local_path = f.replace('/', '\\')
    remote_path = base_remote + '/' + f
    
    # ensure remote dir exists
    remote_dir = os.path.dirname(remote_path)
    stdin, stdout, stderr = ssh.exec_command(f"mkdir -p {remote_dir}")
    stdout.read()
        
    print(f"Uploading {f}...")
    sftp.put(local_path, remote_path)

sftp.close()

print("Running migrations and clearing cache on server...")
commands = [
    "cd domains/hisabmittra.in/public_html/crm",
    "php artisan migrate --force",
    "php artisan route:clear",
    "php artisan view:clear"
]
stdin, stdout, stderr = ssh.exec_command(" && ".join(commands))
print(stdout.read().decode())
print(stderr.read().decode())
ssh.close()
print("Deployment completed successfully!")
