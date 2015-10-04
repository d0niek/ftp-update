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

    /** @var resource $ftp FTP stream */
    private $ftp;

    /** @var bool $passiveMode */
    private $passiveMode = false;

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
                $message = "Could not login to $host:$port\n with login:$login and password:$password";

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
}
