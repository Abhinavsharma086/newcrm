const Client = require('ssh2-sftp-client');
const sftp = new Client();
const config = {
  host: '46.202.183.28',
  port: 65002,
  username: 'u425316205',
  password: 'Hisab@20026'
};

async function clearCache() {
  try {
    await sftp.connect(config);
    const paths = [
        '/home/u425316205/domains/hisabmittra.in/public_html/storage/framework/views',
        '/home/u425316205/domains/hisabmittra.in/public_html/crm/storage/framework/views'
    ];
    
    for (const viewsDir of paths) {
        console.log('\\n--- Clearing cache in ' + viewsDir + ' ---');
        try {
            const list = await sftp.list(viewsDir);
            for (const file of list) {
                if (file.name.endsWith('.php')) {
                    const filePath = viewsDir + '/' + file.name;
                    await sftp.delete(filePath);
                    console.log('Deleted ' + file.name);
                }
            }
        } catch (e) {
            console.log('Error or missing dir: ' + e.message);
        }
    }
    
    console.log('View cache cleared successfully!');
  } catch (err) {
    console.error('Connection error: ', err.message);
  } finally {
    sftp.end();
  }
}

clearCache();
