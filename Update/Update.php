<?php
/**
 * User: d0niek
 * Date: 11/11/15
 * Time: 2:23 PM
 */

namespace Update;

use Exception;

class Update
{
    /**
     * Gets modified files array since last update
     * 
     * @param string $source
     * @param int $lastUpdate
     * @param array $ignoredFiles
     *
     * @return array
     * @throws \Exception
     */
    public function modifiedFiles($source, $lastUpdate = 0, $ignoredFiles = [])
    {
        $basePath = $source;
        $files = [];
        $paths = [
            [
                $source,
                $lastUpdate
            ]
        ];

        for ($i = 0; $i < count($paths); $i++) {
            list($sourcePath, $lastUpdate) = $paths[$i];

            // Change backslash to slash (Windows)
            if (DIRECTORY_SEPARATOR == '\\') {
                $sourcePath = str_replace('\\', '/', $sourcePath);
            }

            if ($this->isIgnoredFile($sourcePath, $basePath, $ignoredFiles)) {
                continue;
            }

            // If file doesn't exists or was removed, throw exception
            if (!file_exists($sourcePath)) {
                throw new Exception("$sourcePath must be a valid file");
            }

            // Add files if they were modified after last update
            if (is_file($sourcePath) && filemtime($sourcePath) > $lastUpdate) {
                $files[] =  $sourcePath;
            } else {
                $paths = array_merge($paths, $this->getFilesFromDirectory($sourcePath, $lastUpdate));
            }
        }

        return $files;
    }

    /**
     * Checks if file is on ignored files list
     *
     * @param string $sourcePath
     * @param string $basePath
     * @param array $ignoredFiles
     *
     * @return bool
     */
    private function isIgnoredFile($sourcePath, $basePath, $ignoredFiles)
    {
        $isIgnored = false;

        foreach ($ignoredFiles as $ignoredFile) {
            // Ignore comments
            if (strpos($ignoredFile, '#') === 0) {
                continue;
            }

            if ($basePath . DIRECTORY_SEPARATOR . $ignoredFile === $sourcePath) {
                $isIgnored = true;
                break;
            }
        }

        return $isIgnored;
    }

    /**
     * Gets files from source directory
     *
     * @param string $sourcePath
     * @param int $lastUpdate
     *
     * @return array
     */
    private function getFilesFromDirectory($sourcePath, $lastUpdate)
    {
        $files = [];
        $directory = opendir($sourcePath);

        while (($item = readdir($directory)) !== false) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $files[] = [
                $sourcePath . DIRECTORY_SEPARATOR . $item,
                $lastUpdate
            ];
        }

        closedir($directory);

        return $files;
    }

}
