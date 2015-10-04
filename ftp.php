<?php

/** Connect to ftp server
 *
 * @param string $server
 * @param string $user
 * @param string $pass
 * @return resource FTP stream on success or false on error
 */
function ftp_init($server, $user, $pass) {
    $ftp = ftp_connect($server);
    if (!$ftp) { return false; }

    @ $ftpLogin = ftp_login($ftp, $user, $pass);
    if (!$ftpLogin) {
        ftp_quit($ftp);
        return false;
    }

    ftp_pasv($ftp, true);

    return $ftp;
}

/** Show directory file list
 *
 * @param resource $ftp FTP stream
 * @param string $dir
 */
function ftp_showDir($ftp, $dir) {
    $files = ftp_nlist($ftp, $dir);
    foreach ($files as $file) {
        if ($file == '/.' || $file == '/..') { continue; }

        echo "$file\n";
    }
}

/** Put local file to ftp server
 *
 * @param resource $ftp FTP stream
 * @param string $serverFile Path to FTP file
 * @param string $localFile Path to local file
 * @param int $mode
 * @return bool true on success or false if local file doesn't exists or ftp faile
 */
function ftp_putFile($ftp, $serverFile, $localFile, $mode) {
    if (!file_exists($localFile)) { return false; }

    @ $ftpPut = ftp_put($ftp, $serverFile, $localFile, $mode);
    if (!$ftpPut) {
        ftp_makeDir($ftp, substr($serverFile, 0, strrpos($serverFile, '/')));

        return ftp_put($ftp, $serverFile, $localFile, $mode);
    }

    return true;
}

/** Get ftp file to local
 *
 * @param resource $ftp FTP stream
 * @param string $localFile Path to local file
 * @param string $serverFile Path to FTP file
 * @param int $mode
 * @return bool true on success or false if local file doesn't exists or ftp faile
 */
function ftp_getFile($ftp, $localFile, $serverFile, $mode) {
    if (!ftp_fileExists($ftp, $serverFile)) { return false; }

    @ $ftpGet = ftp_get($ftp, $localFile, $serverFile, $mode);
    if (!$ftpGet) {
        file_makeDir(substr($localFile, 0, strrpos($localFile, '/')));
        return ftp_get($ftp, $localFile, $serverFile, $mode);
    }

    return true;
}

/** Make folder on FTP server
 *
 * @param resource $ftp FTP stream
 * @param string $dir
 * @return string Newly created directory name on success or false on faile
 */
function ftp_makeDir($ftp, $dir) {
    @ $ftpDir = ftp_mkdir($ftp, $dir);
    if (!$ftpDir) {
        $parentDir = substr($dir, 0, strrpos($dir, '/'));
        $ftpDir = ftp_makeDir($ftp, $parentDir);

        if ($ftpDir) { $ftpDir = ftp_mkdir($ftp, $dir); }
    }

    return $ftpDir;
}

/** Check if file on FTP server exists
 *
 * @param resource $ftp FTP stream
 * @param string $file
 * @return bool true if file exists or false if not
 */
function ftp_fileExists($ftp, $file) {
    @ $rename = ftp_rename($ftp, $file, $file);

    if (!$rename) { return false; }
    else { return true; }
}

/** Make local folder
 *
 * @param string $dir
 * @return bool true on succes or false on fail
 */
function file_makeDir($dir) {
    @ $make = mkdir($dir);
    if (!$make) {
        $parentDir = substr($dir, 0, strrpos($dir, '/'));
        $make = file_makeDir($parentDir);

        if ($make) { mkdir($dir); }
    }

    return $make;
}
