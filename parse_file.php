<?php

require_once __DIR__.'/libs/functions.php';
require_once __DIR__.'/libs/parser.php';

execute_this_file_if_requested(__FILE__);

/**
 * Parse class contents
 *
 * @param string $content
 * @return string JSON
 */
function parse_file($content) {
    if (empty($content)) {
        return [];
    }
    
    $parsedData = [
        'content' => $content,
        'phpdoc' => _get_file_phpdoc($content),
        'classes' => _get_file_classes($content),
        'functions' => _get_file_functions($content),
    ];
    
    return $parsedData;
}