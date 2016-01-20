<?php
/**
 * User: d0niek
 * Date: 11/11/15
 * Time: 2:32 PM
 */

require_once 'vendor/autoload.php';
require_once 'config/config.php';

use Update\Update;
use Ftp\Ftp;

$ftp = new Ftp(HOST, LOGIN, PASSWORD, PORT);

echo "Login to the ftp\n";
$ftp->login();

if (!$ftp->chdir(PROJECT_PATH)) {
    echo 'Directory ' . PROJECT_PATH . " doesn't exists on the ftp.\n";
    echo "Creating directory on the ftp ...\n";

    $ftp->makeDir(PROJECT_PATH);
    $ftp->chdir(PROJECT_PATH);
}

$ignoredFiles = file_exists(IGNORE_FILES) ?
    file(IGNORE_FILES, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) :
    [];

// Add ftp-update script to ignore list
$ignoredFiles[] = 'ftp-update';

$update = new Update();

echo "Gets time of last update from ftp file\n";
$ftpLastUpdate = $ftp->getFile(FTP_UPDATE, 'update') ? (int) file_get_contents(FTP_UPDATE) : 0;
$ftpModifiedFiles = $update->modifiedFiles(SOURCE_PATH, $ftpLastUpdate, $ignoredFiles);

echo 'Modified files from last ftp update (' . date('d-m-Y', $ftpLastUpdate) . "):\n";
listFiles($ftpModifiedFiles);

echo "Gets time of last update from local file\n";
$localLastUpdate = file_exists(LOCAL_UPDATE) ? (int) file_get_contents(LOCAL_UPDATE) : 0;
$localModifiedFiles = $update->modifiedFiles(SOURCE_PATH, $localLastUpdate, $ignoredFiles);

echo 'Modified files from last local update (' . date('d-m-Y', $localLastUpdate) . "):\n";
listFiles($localModifiedFiles);

if (empty($ftpModifiedFiles) && empty($localModifiedFiles)) {
    echo "\nGreat! Nothing to update!\n";

    exit;
}

do {
    echo 'Which time you want to use to do updates (local, ftp, exit): ';

    $option = rtrim(fgets(STDIN), "\n");

    if ($option === 'exit') {
        $ftp->close();

        exit;
    }
} while ($option !== 'local' && $option !== 'ftp');

$updateTime = time();
$backupDirectory = __DIR__ . '/update-history/' . date('d-m-Y H.i', $updateTime);
mkdir($backupDirectory, 0777, true);

if ($option === 'local') {
    $files = $localModifiedFiles;
    $messageLog = 'Last update before this ' . date('d-m-Y H:i', $localLastUpdate) . ' (local time)';
} else {
    $files = $ftpModifiedFiles;
    $messageLog = 'Last update before this ' . date('d-m-Y H:i', $ftpLastUpdate) . ' (ftp time)';
}

file_put_contents("$backupDirectory/backup.log", "$messageLog\n\n");

echo "Backups files before update...\n";
foreach ($files as $file) {
    $file = substr($file, strlen(SOURCE_PATH));
    $ftpFile = $ftp->pwd() . $file;

    if ($ftp->getFile("$backupDirectory$file", $ftpFile)) {
        echo "    $ftpFile\n";
        file_put_contents("$backupDirectory/backup.log", "Backup: $ftpFile\n", FILE_APPEND);
    }
}

echo "Update files on the ftp...\n";
foreach ($files as $file) {
    $ftpFile = $ftp->pwd() . substr($file, strlen(SOURCE_PATH));

    if ($ftp->putFile($ftpFile, $file)) {
        echo "    $file\n";
        file_put_contents("$backupDirectory/backup.log", "Update: $file\n", FILE_APPEND);
    }
}

echo "Save new update time...\n";
file_put_contents(LOCAL_UPDATE, $updateTime);
$ftp->putFile('update', LOCAL_UPDATE);

if (file_exists(FTP_UPDATE)) {
    unlink(FTP_UPDATE);
}

$ftp->close();

/**
 * Lists files from array
 *
 * @param array $files
 */
function listFiles($files)
{
    foreach ($files as $modifiedFile) {
        echo '    ' . substr($modifiedFile, strlen(SOURCE_PATH) + 1) . "\n";
    }
}
