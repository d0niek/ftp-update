<?php
/**
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

    /** @var string $host */
    protected static $host;

    /** @var string $login */
    protected static $login;

    /** @var string $password */
    protected static $password;

    /** @var int $port */
    protected static $port;

    public static function setUpBeforeClass()
    {
        $ftpConfig = require_once(__DIR__ . '/../../config/ftp.config.php');

        self::$host = $ftpConfig['host'];
        self::$login = $ftpConfig['login'];
        self::$password = $ftpConfig['password'];
        self::$port = $ftpConfig['port'];

        touch(__DIR__ . '/file_to_upload.txt');
    }

    public static function tearDownAfterClass()
    {
        if (self::$ftp instanceof Ftp) {
            // Cleanup ftp after tests
            unlink(__DIR__ . '/file_to_upload.txt');

            self::$ftp->removeFile('file.txt');
            self::$ftp->removeFile('dir/notExists/yet/file.txt');
            self::$ftp->removeDir('dir/notExists/yet');
            self::$ftp->removeDir('dir/notExists');
            self::$ftp->removeDir('dir');

            self::$ftp->close();
        }
    }

    public function testValidation()
    {
        $this->assertTrue(Ftp::valid(self::$host, self::$login, self::$password, self::$port));
    }

    /**
     * @expectedException \Ftp\FtpConnectionException
     */
    public function testValidConnectionHost()
    {
        Ftp::valid(self::$host . 'wrong_host', self::$login, self::$password, self::$port);
    }

    /**
     * @expectedException \Ftp\FtpConnectionException
     */
    public function testValidConnectionPort()
    {
        Ftp::valid(self::$host, self::$login, self::$password, self::$port + 21);
    }

    /**
     * @expectedException \Ftp\FtpLoginException
     */
    public function testValidLoginLogin()
    {
        Ftp::valid(self::$host, self::$login . 'wrong_login', self::$password, self::$port);
    }

    /**
     * @expectedException \Ftp\FtpLoginException
     */
    public function testValidLoginPassword()
    {
        Ftp::valid(self::$host, self::$login, self::$password . 'wrong_password', self::$port);
    }

    public function testPutFileOnFtp()
    {
        $ftp = $this->getFtp();

        $this->assertTrue($ftp->putFile('file.txt', __DIR__ . '/file_to_upload.txt'));
        $this->assertTrue($ftp->fileExists('file.txt'));
    }

    public function testPutFileIntoTheDirectoryThatNotExistsYet()
    {
        $ftp = $this->getFtp();

        $file = 'dir/notExists/yet/file.txt';

        $this->assertFalse($ftp->fileExists(dirname($file)));
        $this->assertTrue($ftp->putFile($file, __DIR__ . '/file_to_upload.txt'));
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
        rmdir(__DIR__ . '/dir/notExists/yet');
        rmdir(__DIR__ . '/dir/notExists');
        rmdir(__DIR__ . '/dir');
    }

    public function testGetNotExistingFileFromTheFtp()
    {
        $ftp = $this->getFtp();

        $this->assertFalse($ftp->fileExists('file_not_exists.txt'));
        $this->assertFalse($ftp->getFile('local_file.txt', 'file_not_exists.txt'));
    }

    #region Getters

    /**
     * @return \Ftp\Ftp
     */
    private function getFtp()
    {
        if (self::$ftp === null) {
            self::$ftp = new Ftp(self::$host, self::$login, self::$password, self::$port);

            self::$ftp->login();
        }

        return self::$ftp;
    }

    #endregion
}
