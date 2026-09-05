const Client = require('ssh2-sftp-client');
const sftp = new Client();
const config = {
  host: '46.202.183.28',
  port: 65002,
  username: 'u425316205',
  password: 'Hisab@20026'
};

async function verify() {
  try {
    await sftp.connect(config);
    
    // Check hisabmittra.in/public_html/public
    const p1 = await sftp.list('/home/u425316205/domains/hisabmittra.in/public_html/public');
    console.log('\nhisabmittra.in/public_html/public:');
    console.log(p1.map(d => d.name));
    
  } catch (err) {
    console.error(err.message);
  } finally {
    sftp.end();
  }
}

verify();
