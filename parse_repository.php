<?php

require_once __DIR__.'/libs/functions.php';
require_once __DIR__.'/libs/parser.php';

// include the functions parsing to parse class' methods
require_once __DIR__.'/parse_file.php';

execute_this_file_if_requested(__FILE__);

/**
 * Parse repository, all of its classes, and functions
 * if the $localFolder parameter is 
 * provided, the repo will be only "pull"ed 
 *
 * @param string $repoUrl
 * @param string $localFolder
 * @return array
 */
function parse_repository($repoUrl, $localFolder = false) {
    if (empty($repoUrl) && !preg_match('/^https?:\/\//i', $repoUrl)) {
        return [];
    }

	$repoLocalFolder = _cloneRepo($repoUrl, $localFolder);
	if ($repoLocalFolder === false) {
		return [];
	}

	$parsedData = _parseRepo($repoLocalFolder);
	if ($parsedData === false) {
		return [];
	}
    
    $parsedData = [
        'readme' => _get_repo_readme($repoLocalFolder),
        'classes' => $parsedData['classes'],
        'functions' => $parsedData['functions'], 
    ];
    
    return $parsedData;
}


////////////// private //////////////

// clone the repo to a local folder
function _cloneRepo($repoUrl, $localFolder) {
	$gitExecutable = '/usr/bin/git';

	if ($localFolder && is_dir($localFolder) && is_writeable($localFolder) && is_dir($localFolder.'/.git')) {
		// update the repo
		shell_exec($gitExecutable.' -C '.escapeshellarg($localFolder).' pull');
		return $localFolder;
	} else {
		// clone the repo
		$tempFolder = sys_get_temp_dir();
		if (!$tempFolder || !is_writeable($tempFolder)) {
			return false;
		}

		$tempFolder.= '/'.uniqid('repo_parser_', $__more_entropy = true);
		shell_exec($gitExecutable.' clone '.escapeshellarg($repoUrl).' '.escapeshellarg($tempFolder));
		if (is_dir($tempFolder) && is_readable($tempFolder)) {
			return $tempFolder;
		}

		return false;
	}

}


/**
 * Get repository readme file content
 *
 * @param string $repoLocalFolder
 * @return string|false - readme contents or FALSE
 */
function _get_repo_readme($repoLocalFolder) {
	foreach (scandir($repoLocalFolder) as $fileName) {
		if (preg_match('/^readme/i', $fileName)) {
			if (is_readable($repoLocalFolder.'/'.$fileName)) {
				return file_get_contents($repoLocalFolder.'/'.$fileName);
			}
		}
	}

	return false;
}


/**
 * Parse the repository to get all the classes and the functions
 *
 * @param string $repoLocalFolder
 * @return array|false - parsed data like ['classes' => [...], 'functions' => [...]] or false
 */
function _parseRepo($repoLocalFolder) {
	$classes = [];
	$functions = [];

	foreach (scandir($repoLocalFolder) as $fileName) {
		// skip the dots
		if ($fileName == '.' || $fileName == '..' || $fileName == '.git') {
			continue;
		}

		$parsedData = false;
		if (is_dir($repoLocalFolder.'/'.$fileName) && is_readable($repoLocalFolder.'/'.$fileName)) {
			// parse the folder recursively
			$parsedData = _parseRepo($repoLocalFolder.'/'.$fileName);
		} elseif (preg_match('/\.(php|inc)$/i', $fileName) && is_readable($fileName)) {
			// parse the file
			$fileContents = file_get_contents($repoLocalFolder.'/'.$fileName);
			if (!empty($fileContents)) {
				$parsedData = parse_file($fileContents);
			}
		}

		if ($parsedData !== false) {
			if (!empty($parsedData['classes'])) {
				$classes = array_merge($classes, $parsedData['classes']);
			}
			if (!empty($parsedData['functions'])) {
				$functions = array_merge($functions, $parsedData['functions']);
			}
		}
	}

	return ['classes' => $classes, 'functions' => $functions];
}

