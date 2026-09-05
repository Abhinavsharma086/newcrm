import paramiko
import sys

host = "46.202.183.28"
port = 65002
username = "u425316205"
password = "Hisab@20026"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect(host, port, username, password)
    stdin, stdout, stderr = ssh.exec_command('find ~ -name "NewCrm" -type d -maxdepth 3')
    print("Find NewCrm:", stdout.read().decode())
    
    stdin, stdout, stderr = ssh.exec_command('find ~ -name "customers" -type d | grep "admin/customers"')
    print("Find admin/customers:", stdout.read().decode())
    
    stdin, stdout, stderr = ssh.exec_command('pwd; ls -la')
    print("Pwd and ls:", stdout.read().decode())

finally:
    ssh.close()
