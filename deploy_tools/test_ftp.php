<?php
$ftp = @ftp_connect('46.202.183.28');
if ($ftp) {
    if (@ftp_login($ftp, 'u425316205', 'Hisab@20026')) {
        echo "FTP Login SUCCESS\n";
        echo "Dir: " . ftp_pwd($ftp) . "\n";
        ftp_close($ftp);
    } else {
        echo "FTP Login FAILED\n";
    }
} else {
    echo "FTP Connection FAILED\n";
}
