import paramiko
import sys

host = '46.202.183.28'
port = 65002
user = 'u425316205'
password = 'Hisab@20026'

remote_file = '/home/u425316205/domains/hisabmittra.in/public_html/crm/storage/logs/laravel.log'
local_file = 'laravel_remote.log'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, port, user, password, timeout=10)
    
    sftp = ssh.open_sftp()
    sftp.get(remote_file, local_file)
    sftp.close()
    ssh.close()
    print("Log downloaded successfully")
except Exception as e:
    print(f"Failed: {e}")
