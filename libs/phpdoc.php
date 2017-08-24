<?php

// load general functions
require_once __DIR__.'/functions.php';

// remove leading whitespace in PHPDoc
function filter_stripLeadingCommentSpaces($line, &$initialSpace = false) {
	// if the initial was provided, just remove it
	if ($initialSpace !== false) {
		return str_replace_first($initialSpace, '', $line);
	}

	// check if there's comment start, comment body start or comment end characters
	if (preg_patch('#^(\s*)[(\*|/\*)+]#', $line, $matches)) {

		// set the space in the (byref) parameter
		$initialSpace = $matches[1];

		// remove the space and return
		return preg_replace('#^\s*?#', '', $line);
	}

	return $line;
}


// remove the leading comment opener (like "*", or "/*", or */)
function filter_stripLeadingCommentOpener($line) {
	return $line;
}