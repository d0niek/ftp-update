<?php
/**
 * User: d0niek
 * Date: 10/4/15
 * Time: 10:15 PM
 */

namespace Ftp;

class Ftp
{
    /** @var string $host */
    private $host;

    /** @var int $port */
    private $port = 21;

    /** @var string $login */
    private $login;

    /** @var string $password */
    private $password;

    /** @var bool $passiveMode */
    private $passiveMode = true;

    /** @var resource $ftp FTP stream */
    private $ftp;

    public function __construct($host, $login, $password, $port = 21)
    {
        $this->host = $host;
        $this->port = $port;
        $this->login = $login;
        $this->password = $password;
    }

    /**
     * Valid if ftp parameters are correct
     *
     * @param string $host
     * @param string $login
     * @param string $password
     * @param int $port
     *
     * @return bool
     * @throws \Ftp\FtpConnectionException
     * @throws \Ftp\FtpLoginException
     */
    public static function valid($host, $login, $password, $port = 21)
    {
        $ftpStream = @ftp_connect($host, $port);

        if ($ftpStream !== false) {
            $loginResult = @ftp_login($ftpStream, $login, $password);

            ftp_close($ftpStream);

            if ($loginResult === false) {
                $message = "Could not login to with $login:$password\n";

                throw new FtpLoginException($message, FtpLoginException::FTP_LOGIN_CODE);
            }
        } else {
            $message = "Could not connect to the $host:$port\n";

            throw new FtpConnectionException($message, FtpConnectionException::FTP_CONNECTION_CODE);
        }

        return true;
    }

    /**
     * Logs in to an FTP and turns passive mode on
     *
     * @throws \Ftp\FtpConnectionException
     * @throws \Ftp\FtpLoginException
     */
    public function login()
    {
        if ($this->valid($this->host, $this->login, $this->password, $this->port)) {
            $this->ftp = ftp_connect($this->host, $this->port);

            ftp_login($this->ftp, $this->login, $this->password);

            ftp_pasv($this->ftp, $this->passiveMode);
        }
    }

    /**
     * Closes an FTP connection
     */
    public function close()
    {
        ftp_close($this->ftp);
    }

    /**
     * Toggles passive mode
     */
    public function togglePassiveMode()
    {
        $this->passiveMode = !$this->passiveMode;

        ftp_pasv($this->ftp, $this->passiveMode);
    }

    /**
     * Gets the current directory name
     *
     * @return string
     */
    public function pwd()
    {
        return ftp_pwd($this->ftp);
    }

    /**
     * Lists specific directory on the ftp
     *
     * @param string $dir
     */
    public function listDir($dir)
    {
        $files = ftp_nlist($this->ftp, $dir);

        foreach ($files as $file) {
            echo "$file\n";
        }
    }

    /**
     * Gets file from ftp and save it into the local file
     *
     * @param string $localFile
     * @param string $remoteFile
     * @param int $mode
     * @param int $position
     *
     * @return bool
     */
    public function getFile($localFile, $remoteFile, $mode = FTP_ASCII, $position = 0)
    {
        $result = false;

        if ($this->fileExists($remoteFile)) {
            $result = @ftp_get($this->ftp, $localFile, $remoteFile, $mode, $position);

            // That means the local file's directory not exists yet
            if (!$result) {
                $dir = dirname($localFile);

                mkdir($dir, 0777, true);

                $result = ftp_get($this->ftp, $localFile, $remoteFile, $mode, $position);
            }
        }

        return $result;
    }

    /**
     * Puts local file to the ftp
     *
     * @param string $remoteFile
     * @param string $localFile
     * @param int $mode
     * @param int $position
     *
     * @return bool
     */
    public function putFile($remoteFile, $localFile, $mode = FTP_ASCII, $position = 0)
    {
        $result = false;

        if (file_exists($localFile)) {
            $result = @ftp_put($this->ftp, $remoteFile, $localFile, $mode, $position);

            // That means the ftp file's directory not exists yet
            if (!$result) {
                $dir = dirname($remoteFile);

                $this->makeDir($dir);

                $result = ftp_put($this->ftp, $remoteFile, $localFile, $mode);
            }
        }

        return $result;
    }

    /**
     * Removes file from the FTP server
     *
     * @param string $remoteFile
     *
     * @return bool
     */
    public function removeFile($remoteFile)
    {
        return ftp_delete($this->ftp, $remoteFile);
    }

    /**
     * Makes directory on the ftp
     *
     * @param string $dir
     *
     * @return string
     */
    public function makeDir($dir)
    {
        $result = @ftp_mkdir($this->ftp, $dir);

        // That means the parent directory not exists yet
        if (!$result) {
            $parentDir = dirname($dir);

            $result = $this->makeDir($parentDir);

            if ($result) {
                $result = ftp_mkdir($this->ftp, $dir);
            }
        }

        return $result;
    }

    /**
     * Removes directory from the ftp
     *
     * @param string $dir
     *
     * @return bool
     */
    public function removeDir($dir)
    {
        return ftp_rmdir($this->ftp, $dir);
    }

    /**
     * Checks if file exists on the ftp
     *
     * @param string $file
     *
     * @return bool
     */
    public function fileExists($file)
    {
        return @ftp_rename($this->ftp, $file, $file);
    }

    #region Getters & Setters

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getLogin()
    {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return boolean
     */
    public function isPassiveMode()
    {
        return $this->passiveMode;
    }

    #endregion Getters & Setters
}
