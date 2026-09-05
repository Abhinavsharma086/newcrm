import paramiko

host = '46.202.183.28'
port = 65002
user = 'u425316205'
password = 'Hisab@20026'

new_code = r"""<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        URL::forceScheme('https');
    }
}
"""

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, port, user, password, timeout=10)
    
    sftp = ssh.open_sftp()
    remote_file = '/home/u425316205/domains/hisabmittra.in/public_html/crm/app/Providers/AppServiceProvider.php'
    with sftp.open(remote_file, 'w') as f:
        f.write(new_code)
    sftp.close()
    
    stdin, stdout, stderr = ssh.exec_command('cd /home/u425316205/domains/hisabmittra.in/public_html/crm && php artisan config:clear && php artisan cache:clear && php artisan view:clear')
    print(stdout.read().decode())
    ssh.close()
    print("Fixed AppServiceProvider")
except Exception as e:
    print(f"Failed: {e}")
