import paramiko

host = "46.202.183.28"
port = 65002
username = "u425316205"
password = "Hisab@20026"

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    ssh.connect(host, port, username, password)
    stdin, stdout, stderr = ssh.exec_command('tail -n 20 /home/u425316205/domains/hisabmittra.in/logs/error_log || tail -n 20 /home/u425316205/error_log || tail -n 20 /home/u425316205/domains/hisabmittra.in/public_html/error_log')
    print("Errors:\n", stdout.read().decode())
finally:
    ssh.close()
