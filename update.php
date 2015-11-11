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
    echo 'Directory ' . PROJECT_PATH . "doesn't exists on the ftp.\n";
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
foreach ($ftpModifiedFiles as $modifiedFile) {
    echo '    ' . substr($modifiedFile, strlen(SOURCE_PATH) + 1) . "\n";
}

echo "Gets time of last update from local file\n";
$localLastUpdate = file_exists(LOCAL_UPDATE) ? (int) file_get_contents(LOCAL_UPDATE) : 0;
$localModifiedFiles = $update->modifiedFiles(SOURCE_PATH, $localLastUpdate, $ignoredFiles);

echo 'Modified files from last local update (' . date('d-m-Y', $localLastUpdate) . "):\n";
foreach ($localModifiedFiles as $modifiedFile) {
    echo '    ' . substr($modifiedFile, strlen(SOURCE_PATH) + 1) . "\n";
}

unlink(FTP_UPDATE);
$ftp->close();
