<?php
/**
 * User: d0niek
 * Date: 11/11/15
 * Time: 2:32 PM
 */

require_once 'vendor/autoload.php';

/**
 * Local file to store last update time
 */
define('LOCAL_UPDATE', __DIR__ . '/update');

/**
 * Ignore files list. Will no be update
 */
define('IGNORE_FILES', __DIR__ . '/ignore');

/**
 * Source of files to update
 */
define('SOURCE_PATH', dirname(__DIR__));

echo "Gets time of last update from local file\n";
$localLastUpdate = file_exists(LOCAL_UPDATE) ? file_get_contents(LOCAL_UPDATE) : 0;

echo "Gets list of ignored files\n";
$ignoredFiles = file_exists(IGNORE_FILES) ?
    file(IGNORE_FILES, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) :
    [];

// Add ftp-update script to ignore list
$ignoredFiles[] = 'ftp-update';

$update = new \Update\Update();
$modifiedFiles = $update->modifiedFiles(SOURCE_PATH, $localLastUpdate, $ignoredFiles);

echo 'Modified files from last local update (' . date('d-m-Y', $localLastUpdate) . "):\n";
foreach ($modifiedFiles as $modifiedFile) {
    echo '    ' . substr($modifiedFile, strlen(SOURCE_PATH) + 1) . "\n";
}
