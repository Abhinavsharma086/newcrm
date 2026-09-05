const Client = require('ssh2-sftp-client');
const sftp = new Client();
const path = require('path');

const config = {
  host: '46.202.183.28',
  port: 65002,
  username: 'u425316205',
  password: 'Hisab@20026'
};

const filesToUpload = [
    'app/Http/Controllers/Admin/ChatbotController.php',
    'app/Models/ChatbotLog.php',
    'database/migrations/2026_08_31_000001_create_chatbot_logs_table.php',
    'public/css/chatbot.css',
    'public/js/chatbot.js',
    'resources/views/components/chatbot-widget.blade.php',
    'resources/views/layouts/admin.blade.php',
    'resources/views/layouts/app.blade.php',
    'resources/views/layouts/employee.blade.php',
    'routes/web.php'
];

async function deploy() {
  try {
    await sftp.connect(config);
    const paths = [
        '/home/u425316205/domains/hisabmittra.in/public_html',
        '/home/u425316205/domains/hisabmittra.in/public_html/crm'
    ];
    
    for (const remoteBasePath of paths) {
        console.log('\\n--- Deploying to ' + remoteBasePath + ' ---');
        for (const file of filesToUpload) {
            const localFile = path.join(__dirname, '../', file);
            const remoteFile = remoteBasePath + '/' + file;
            
            const remoteDir = path.dirname(remoteFile);
            const exists = await sftp.exists(remoteDir);
            if (!exists) {
                console.log('Creating ' + remoteDir);
                await sftp.mkdir(remoteDir, true);
            }
            
            console.log('Uploading ' + file + ' ...');
            await sftp.put(localFile, remoteFile);
        }
        
        const migratorScript = `<?php
        require __DIR__.'/../vendor/autoload.php';
        $app = require_once __DIR__.'/../bootstrap/app.php';
        $kernel = $app->make(Illuminate\\Contracts\\Http\\Kernel::class);
        $response = $kernel->handle(
            $request = Illuminate\\Http\\Request::capture()
        );
        Illuminate\\Support\\Facades\\Artisan::call('migrate', ['--force' => true]);
        echo "Done! " . Illuminate\\Support\\Facades\\Artisan::output();
        `;
        
        const localMigrator = path.join(__dirname, 'migrator.php');
        require('fs').writeFileSync(localMigrator, migratorScript);
        
        await sftp.put(localMigrator, remoteBasePath + '/public/migrator.php');
    }
    
    console.log('Deployment completely finished for both folders!');
  } catch (err) {
    console.error('Deployment error: ', err.message);
  } finally {
    sftp.end();
  }
}

deploy();
