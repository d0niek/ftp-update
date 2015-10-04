<?php

$execute = array( 'cache', 'logs', '.git', '.idea' );
$winPath = 'www/';
$linuxPath = '/srv/www/htdocs/ifgard.com/';

cp_r(dirname(__FILE__) . '/source', $linuxPath, $execute);

/** Copy files from source path to destination path
 *
 * @param string $source_path
 * @param string $destination_path
 * @param array $omit_patterns
 * @throws Exception
 */
function cp_r($source_path, $destination_path, $omit_patterns = array()) {
	$paths = array(
		array(
			$source_path,
			$destination_path
		)
	);

	for ($i = 0; $i < count($paths); $i++) {
		list($source_path, $destination_path) = $paths[$i];

		// Change backslash to slash (Windows)
		if (DIRECTORY_SEPARATOR == '\\') {
			$source_path = str_replace('\\', '/', $source_path);
		}

		// For all patterns check if source_path don't contain it
		foreach ($omit_patterns as $pattern) {
			if (preg_match("|$pattern|", $source_path)) { continue 2; }
		}

		// If file doesn't exists or was removed, throw exception
		if (!file_exists($source_path)) {
			throw new Exception("$source_path must be a valid file");
		}

		// Copy files only then if destination file doesn't exists or modification time is different from source file
		if (is_file($source_path)) {
			if (file_exists($destination_path) && filemtime($source_path) <= filemtime($destination_path)) {
				continue;
			}

			copy($source_path, $destination_path);
			echo "file: $source_path\n";
			continue;
		}

		// Make directory if destination path doesn't exists
		if (!file_exists($destination_path)) {
			echo "dir: $source_path\n";
			mkdir($destination_path);
		// Throw exception if destination path is a file
		} else if (!is_dir($destination_path)) {
			throw new Exception("$destination_path (which should be a directory) is already taken by a file");
		}

		$directory = opendir($source_path);

		// Add all files from source directory to array
		while (($item = readdir($directory)) !== false) {
			if ($item == '.' || $item == '..') { continue; }

			$paths[] = array($source_path . DIRECTORY_SEPARATOR . $item, $destination_path . DIRECTORY_SEPARATOR . $item);
		}

		closedir($directory);
	}
}
