<?php

$pass = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : '';
$server = '';
$user = '';

// Init ftp connection
echo "FTP connecting ...\n";
$ftp = ftp_init($server, $user, $pass);
if (!$ftp) {
    echo "Error ftp init\n";
    exit();
}

$source_path = 'source/trunk';
$backupDir = 'backup/' . date('dmY_Hi');

@ file_put_contents("$backupDir/backup.log", "Backuo log:\n", FILE_APPEND);

require_once(dirname(__FILE__) . '/ftp.php');
require_once(dirname(__FILE__) . '/modifiedFiles.php');

// Get last update time
echo "Get last update time ...\n";
ftp_get($ftp, 'update.log', '/update.log', FTP_ASCII);
$lastUpdate = strtotime(file_get_contents('update.log'));

// Get newer files since the last update
echo "Get newer files list since the last update ...\n";
$execute = array();
$files = modifiedFiles($source_path, $lastUpdate, $execute);

echo "Backup old files and update newer ...\n";
foreach ($files as $file) {
    $ftpFiles = substr($file, strlen($source_path));

    if (ftp_getFile($ftp, "$backupDir$ftpFiles", $ftpFiles, FTP_ASCII)) {
        echo "    Backup << $ftpFiles\n";
        file_put_contents("$backupDir/backup.log", "Backup: $ftpFiles\n", FILE_APPEND);
    }

    if (ftp_putFile($ftp, $ftpFiles, "$source_path$ftpFiles", FTP_ASCII)) {
        echo "    Update >> $source_path$ftpFiles\n";
        file_put_contents("$backupDir/backup.log", "Update: $source_path$ftpFiles\n", FILE_APPEND);
    }
}

// Update time
echo "Save new update time ...\n";
file_put_contents('update.log', date('Y-m-d H:i', time()));
ftp_putFile($ftp, '/update.log', 'update.log', FTP_ASCII);

ftp_quit($ftp);
