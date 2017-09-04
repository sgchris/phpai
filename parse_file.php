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


////////////// private //////////////

/**
 * Get PHPDoc from class' content
 * 
 * @param mixed $content - the content of the function
 * @return array
 */
function _get_file_phpdoc($content) {
    if (empty($content)) {
        return [];
    }
    
    $foundMatch = preg_match_all("/<\?[(?!php)]*\s*\/\*\*(.*?)\s*\*\//smi", $content, $matches);
    
    return $foundMatch ? parse_php_doc_lines($matches[1]) : [];
}


/**
 * Get the classes in this file
 * 
 * @param mixed $content 
 * @return array
 */
function _get_file_classes($content) {
    if (empty($content)) {
        return false;
    }
    
    $lines = split_into_lines($content);
    
    $classes = [];
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        
        if (is_line_start_of_class($line)) {
            $classContentLines = [];
            
            // collect the phpdoc (or just comments above the class)
            $j = $i - 1;
            if (is_comment_line($lines[$j])) {
                while (is_comment_line($lines[$j]) && $j>0) { $j--; }
                $classContentLines = array_slice($lines, $j + 1, $i - $j);
            }
            
            // collect the method contents
            $classContentLines = array_merge($classContentLines, get_statement_contents($lines, $i));
            
            // parse the class content
            $parsedClassData = parse_class(implode(PHP_EOL, $classContentLines));
            if ($parsedClassData !== false) {
                $classes[$parsedClassData['name']] = $parsedClassData;
            }
        }
    }
    
    return $classes;
}
