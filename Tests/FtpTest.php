<?php
/**
 * Created by PhpStorm.
 * User: d0niek
 * Date: 10/8/15
 * Time: 10:54 PM
 */

namespace Tests;

use Ftp\Ftp;
use PHPUnit_Framework_TestCase;

class FtpTest extends PHPUnit_Framework_TestCase
{
    /** @var \Ftp\Ftp|null $ftp */
    private static $ftp = null;

    /** @var array $ftpConfig */
    protected static $ftpConfig;

    public static function setUpBeforeClass()
    {
        self::$ftpConfig = require_once(__DIR__ . '/ftp.config.php');
    }

    public static function tearDownAfterClass()
    {
        if (self::$ftp instanceof Ftp) {
            self::$ftp->close();
        }
    }

    public function testValidation()
    {
        extract(self::$ftpConfig);

        $this->assertTrue(Ftp::valid($host, $login, $password, $port));
    }

    /**
     * @expectedException \Ftp\FtpConnectionException
     */
    public function testValidConnectionHost()
    {
        extract(self::$ftpConfig);

        Ftp::valid($host . 'wrong_host', $login, $password, $port);
    }

    /**
     * @expectedException \Ftp\FtpConnectionException
     */
    public function testValidConnectionPort()
    {
        extract(self::$ftpConfig);

        Ftp::valid($host, $login, $password, $port + 21);
    }

    /**
     * @expectedException \Ftp\FtpLoginException
     */
    public function testValidLoginLogin()
    {
        extract(self::$ftpConfig);

        Ftp::valid($host, $login . 'wrong_login', $password, $port);
    }

    /**
     * @expectedException \Ftp\FtpLoginException
     */
    public function testValidLoginPassword()
    {
        extract(self::$ftpConfig);

        Ftp::valid($host, $login, $password . 'wrong_password', $port);
    }

    public function testPutFileOnFtp()
    {
        $ftp = $this->getFtp();

        $this->assertTrue($ftp->putFile('file.txt', __DIR__ . '/ftp.config.php.dist'));
        $this->assertTrue($ftp->fileExists('file.txt'));
    }

    public function testPutFileIntoTheDirectoryThatNotExistsYet()
    {
        $ftp = $this->getFtp();

        $file = 'dir/notExists/yet/file.txt';

        $this->assertFalse($ftp->fileExists(dirname($file)));
        $this->assertTrue($ftp->putFile($file, __DIR__ . '/ftp.config.php.dist'));
        $this->assertTrue($ftp->fileExists($file));
    }

    public function testPutNotExistingLocalFileToTheFtp()
    {
        $ftp = $this->getFtp();

        $this->assertFalse($ftp->putFile('local_file_not_exists.txt', __DIR__ . '/no_file.txt'));
        $this->assertFalse($ftp->fileExists('local_file_not_exists.txt'));
    }

    public function testGetFileFromFtp()
    {
        $ftp = $this->getFtp();

        $localFile = __DIR__ . '/file.txt';

        $this->assertFalse(file_exists($localFile));
        $this->assertTrue($ftp->getFile($localFile, 'file.txt'));
        $this->assertTrue(file_exists($localFile));

        unlink($localFile);
    }

    public function testGetFileFromTheFtpToTheDirectoryThatNotExistsYet()
    {
        $ftp = $this->getFtp();

        $localFile = __DIR__ . '/dir/notExists/yet/file.txt';

        $this->assertFalse(file_exists($localFile));
        $this->assertTrue($ftp->getFile($localFile, 'file.txt'));
        $this->assertTrue(file_exists($localFile));

        unlink($localFile);
        rmdir(dirname($localFile));
    }

    public function testGetNotExistingFileFromTheFtp()
    {
        $ftp = $this->getFtp();

        $this->assertFalse($ftp->fileExists('file_not_exists.txt'));
        $this->assertFalse($ftp->getFile('local_file.txt', 'file_not_exists.txt'));
    }

    /**
     * @return \Ftp\Ftp
     */
    private function getFtp()
    {
        if (self::$ftp === null) {
            extract(self::$ftpConfig);

            self::$ftp = new Ftp($host, $login, $password, $port);
            self::$ftp->login();
        }

        return self::$ftp;
    }
}
