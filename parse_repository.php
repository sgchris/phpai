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
    
    $parsedData = [
        'readme' => _get_repo_readme($repoLocalFolder),
        'classes' => _get_repo_classes($repoLocalFolder),
        'functions' => _get_repo_functions($repoLocalFolder),
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
		return $tempFolder;
	}

}

function _get_repo_readme($repoLocalFolder) {
	if (!is_dir($repoLocalFolder)) {
		return false;
	}
}


function _get_repo_classes($repoLocalFolder) {
	if (!is_dir($repoLocalFolder)) {
		return false;
	}
}


function _get_repo_functions($repoLocalFolder) {
	if (!is_dir($repoLocalFolder)) {
		return false;
	}
}

