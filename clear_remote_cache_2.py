import paramiko

host = "46.202.183.28"
port = 65002
username = "u425316205"
password = "Hisab@20026"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect(host, port, username, password)
    stdin, stdout, stderr = ssh.exec_command('cd /home/u425316205/domains/hisabmittra.in/public_html/crm && php artisan cache:clear && php artisan config:clear')
    print("Output:\n", stdout.read().decode())
    print("Errors:\n", stderr.read().decode())
finally:
    ssh.close()
