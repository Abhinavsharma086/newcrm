import paramiko
import os
import sys

host = "46.202.183.28"
port = 65002
username = "u425316205"
password = "Hisab@20026"

local_base = r"c:\xampp\htdocs\NewCrm"
remote_base = "/home/u425316205/domains/hisabmittra.in/public_html/crm"

files_to_upload = [
    "resources/views/admin/vendor_pos/index.blade.php",
    "resources/views/admin/vendor_invoices/index.blade.php",
    "resources/views/admin/client_pos/index.blade.php",
    "resources/views/admin/customers/index.blade.php",
    "resources/views/admin/leads/index.blade.php",
    "resources/views/admin/appointments/index.blade.php"
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
        
        # ensure remote dir exists if we can? Actually the structure should already exist
        try:
            sftp.put(local_path, remote_path)
            print(f"Successfully uploaded {f}")
        except Exception as e:
            print(f"Error uploading {f}: {e}")
            
    sftp.close()
    
finally:
    ssh.close()
