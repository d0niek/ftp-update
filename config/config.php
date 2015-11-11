<?php
/**
 * User: d0niek
 * Date: 11/11/15
 * Time: 6:27 PM
 */

/**
 * Local file to store last update time
 */
define('LOCAL_UPDATE', dirname(__DIR__) . '/update');

/**
 * Temporary file to store last update time from ftp file
 */
define('FTP_UPDATE', dirname(__DIR__) . '/ftpUpdate');

/**
 * Ignore files list
 */
define('IGNORE_FILES', dirname(__DIR__) . '/ignore');

/**
 * Source of files to update
 */
define('SOURCE_PATH', dirname(dirname(__DIR__)));

/**
 * Define constants with ftp setting
 */
$ftpConfig = require_once __DIR__ . '/ftp.config.php';
define('HOST', $ftpConfig['host']);
define('LOGIN', $ftpConfig['login']);
define('PASSWORD', $ftpConfig['password']);
define('PORT', $ftpConfig['port']);
define('PROJECT_PATH', $ftpConfig['projectPath']);
