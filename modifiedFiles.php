<?php

/** Make files list with modification time higher than m_time
 *
 * @param string $source
 * @param int $m_time
 * @param array $omit_patterns
 * @throws Exception
 * @return array
 */
function modifiedFiles($source, $m_time, $omit_patterns = array()) {
	$files = array();
	$paths = array(
		array(
			$source,
			$m_time
		)
	);

	for ($i = 0; $i < count($paths); $i++) {
		list($source_path, $m_time) = $paths[$i];

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

		// Add files when modification time is higher than m_time
		if (is_file($source_path)) {
			if (filemtime($source_path) > $m_time) {
				$files[] =  $source_path;
			}

			continue;
		}

		$directory = opendir($source_path);

		// Add all files from source directory to array
		while (($item = readdir($directory)) !== false) {
			if ($item == '.' || $item == '..') { continue; }

			$paths[] = array($source_path . DIRECTORY_SEPARATOR . $item, $m_time);
		}

		closedir($directory);
	}

	return $files;
}
