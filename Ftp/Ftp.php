<?php
/**
 * Created by PhpStorm.
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
    public function valid($host, $login, $password, $port = 21)
    {
        $ftpStream = @ftp_connect($host, $port);

        if ($this->ftp !== false) {
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
     * Logs in to an FTP connection
     *
     * @throws \Ftp\FtpConnectionException
     * @throws \Ftp\FtpLoginException
     */
    public function login()
    {
        if ($this->valid($this->host, $this->login, $this->password, $this->port)) {
            $this->ftp = @ftp_connect($this->host, $this->port);

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
     * Gets file from ftp and save it in the local file
     *
     * @param string $localFile
     * @param string $remoteFile
     * @param int $mode
     * @param int $position
     *
     * @return bool
     */
    public function getFile($localFile, $remoteFile, $mode, $position = 0)
    {
        if ($this->fileExists($remoteFile)) {
            $ftpGet = @ftp_get($this->ftp, $localFile, $remoteFile, $mode, $position);

            // That means the local file is in directory that not exists yet
            if (!$ftpGet) {
                $dir = substr($localFile, 0, strrpos($localFile, '/'));

                mkdir($dir, 0777, true);

                return ftp_get($this->ftp, $localFile, $remoteFile, $mode, $position);
            }

            return true;
        }

        return false;
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
    public function putFile($remoteFile, $localFile, $mode, $position = 0)
    {
        if (file_exists($localFile)) {
            $ftpPut = @ftp_put($this->ftp, $remoteFile, $localFile, $mode, $position);

            // That means the ftp file should be in directory that not exists yet
            if (!$ftpPut) {
                $dir = substr($remoteFile, 0, strrpos($remoteFile, '/'));

                $this->makeDir($dir);

                return ftp_put($this->ftp, $remoteFile, $localFile, $mode);
            }

            return true;
        }

        return false;
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
        $ftpDir = @ftp_mkdir($this->ftp, $dir);

        if (!$ftpDir) {
            $parentDir = substr($dir, 0, strrpos($dir, '/'));

            $ftpDir = $this->makeDir($parentDir);

            if ($ftpDir) {
                $ftpDir = ftp_mkdir($this->ftp, $dir);
            }
        }

        return $ftpDir;
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
