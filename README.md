# ftp-update
Update only modified files via ftp

Installations
-------------
Download and put files from repository into ftp-update directory in root directory of your project.

Go to /path/to/project/ftp-update/config copy ftp.config.php.dist file and past in the same location 
but without dist extension.

Setup yours ftp data such as host, login, password, port and path to where script will upload new files.

Tests
-----
When you setup ftp data now you can run tests to check if everything work correctly. Run
```bash
$ php phpunit.phar
```

Ignore file
-----------
Copy ignore.dist file and past without extension. Add files which you don't want to upload to the ftp.
Each file put in separate line
```bash
fileName
directoryName
directory/fileName
directory/subdirectory/fileName

vendor
```
Files paths are
- /path/to/project/fileName
- /path/to/project/directoryName
- /path/to/project/directory/fileName
- /path/to/project/directory/subdirectory/fileName

If fileName is directory then whole directory won't be upload to ftp.
If you want to ignore single file write path to it (start from projekt root).

Lines start with # is a comment

Run update
----------
Open command line and go to project root
```bash
$ cd /path/to/project
```
and run command
```bash
$ php ftp-update/update.php
```
Script will connect to ftp and download file with last update time (if there was any) and then will list
all local files modified since this time.

After will take local update time and list all local files modified since this time.

Then you have to choice by which time you want to make update
```bash
Which time you want to use to do updates (local, ftp): ftp
```
write local or ftp and press Enter.

According to chosen option script first will make backup from ftp (if files exists) and after that will
upload new modified files.

Your update history is inside /path/to/project/ftp-update/update-history
