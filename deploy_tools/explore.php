<?php
require 'vendor/autoload.php';

use phpseclib4\Net\SSH2;
use phpseclib4\Net\SCP;

$ssh = new SSH2('46.202.183.28', 65002);
if (!$ssh->login('u425316205', 'Hisab@20026')) {
    exit('Login Failed');
}

echo "Current Directory via SSH2: " . $ssh->exec('pwd') . "\n";
echo "Listing:\n";
echo $ssh->exec('ls -la');
