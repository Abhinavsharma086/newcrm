const Client = require('ssh2-sftp-client');
const sftp = new Client();
const config = {
  host: '46.202.183.28',
  port: 65002,
  username: 'u425316205',
  password: 'Hisab@20026'
};

async function listFiles() {
  try {
    await sftp.connect(config);
    const basePath = '/home/u425316205/domains/hisabmittra.in/public_html/crm/public';
    const list = await sftp.list(basePath);
    console.log('Contents of public/:');
    console.log(list.map(i => i.name));
    
    // also check asset folder or something
    const jsList = await sftp.list(basePath + '/js');
    console.log('Contents of public/js/:');
    console.log(jsList.map(i => i.name));
  } catch (err) {
    console.error(err.message);
  } finally {
    sftp.end();
  }
}

listFiles();
