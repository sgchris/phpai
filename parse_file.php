<?php

require_once __DIR__.'/libs/functions.php';
require_once __DIR__.'/libs/parser.php';

// include the functions parsing to parse class' methods
require_once __DIR__.'/parse_class.php';

execute_this_file_if_requested(__FILE__);

/**
 * Parse class contents
 *
 * @param string $content
 * @return array
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
        //'namespace' => _get_file_namespace($content),
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
    
    // get the first PHPDoc in the file
    $foundMatch = preg_match("/[(?!<\?php)]\s*\/\*\*(.*?)\s*\*\//smi", $content, $matches);

    $commentLines = [];
    if ($foundMatch) {
        $commentCode = $matches[1];
        $commentLines = split_into_lines($commentCode);

        // get only the comment content
        foreach ($commentLines as $i => $commentLine) {
            $commentLine = trim($commentLine);
            $commentLine = remove_comment_start($commentLine);
            $commentLines[$i] = $commentLine;
        }

    }

    return parse_php_doc_lines($commentLines);
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

function _get_file_functions($content) {
    if (empty($content)) {
        return false;
    }
    
    $lines = split_into_lines($content);
    
    $functions = [];
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        
		// check if it's start of a class, skip to the end
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

			// promote the counter to the end of the class
			$i+= count($classContentLines) - 1;
			continue;
        }

		// check if the line is a start of a function
        if (is_line_start_of_function($line)) {
            $functionContentLines = [];
            
            // collect the phpdoc (or just comments above the method)
            $j = $i - 1;
            if (is_comment_line($lines[$j])) {
                while (is_comment_line($lines[$j]) && $j>0) { $j--; }
                $functionContentLines = array_slice($lines, $j + 1, $i - $j);
            }
            
            // collect the method contents
            $functionContentLines = array_merge($functionContentLines, get_statement_contents($lines, $i));
            
            // parse the function content
            $parsedFunctionData = parse_function(implode(PHP_EOL, $functionContentLines));
            if ($parsedFunctionData !== false) {
                $functions[$parsedFunctionData['name']] = $parsedFunctionData;
            }
        }
    }
    
    return $functions;
}
