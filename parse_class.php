<?php

require_once __DIR__.'/libs/functions.php';
require_once __DIR__.'/libs/parser.php';

// include the functions parsing to parse class' methods
require_once __DIR__.'/parse_function.php';

execute_this_file_if_requested(__FILE__);

/**
 * Parse class contents
 *
 * @param string $content
 * @return string JSON
 */
function parse_class($content) {
    if (empty($content)) {
        return [];
    }
    
    $parsedData = [
        'content' => $content,
        'phpdoc' => _get_class_phpdoc($content),
        'name' => _get_class_name($content),
        'extends' => _get_class_extends($content),
        'methods' => _get_class_methods($content),
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
function _get_class_phpdoc($content) {
    if (empty($content)) {
        return [];
    }
    
    $tokens = stripos($content, '<?') === false ? token_get_all('<?php ' . $content) : token_get_all($content);
    $commentCode = '';
    foreach ($tokens as $tokenData) {
        if (is_array($tokenData) && count($tokenData) > 1) {
            // check doc block
            if ($tokenData[0] == T_DOC_COMMENT || $tokenData[0] == T_COMMENT) {
                $commentCode.= $tokenData[1];
            }
            
            // check if the class was already declared
            if ($tokenData[0] == T_CLASS || $tokenData[0] == T_ABSTRACT) {
                break;
            }
        }
    }
    
    $commentLines = split_into_lines($commentCode);

    // get only the comment content
    foreach ($commentLines as $i => $commentLine) {
        $commentLine = trim($commentLine);
        $commentLine = remove_comment_start($commentLine);
        $commentLines[$i] = $commentLine;
    }
    
    return parse_php_doc_lines($commentLines);
}


/**
 * Get the name of the class/interface
 * 
 * @param mixed $content 
 * @return string
 */
function _get_class_name($content) {
    $lines = split_into_lines($content);
    
    foreach ($lines as $line) {
        if (($classData = is_line_start_of_class($line)) !== false) {
            return $classData['name'] ?? false;
        }
    }
    
    return false;
}


/**
 * Get the name of the class/interface
 * 
 * @param mixed $content 
 * @return string
 */
function _get_class_extends($content) {
    if (empty($content)) {
        return false;
    }
    
    // get PHP tokens
    $tokens = get_tokens($content);
    
    // find "extends" token and return the string that comes right after it
    foreach ($tokens as $i => $token) {
        if (is_array($token) && isset($token[0]) && $token[0] == 'T_EXTENDS') {
            // return the string that comes after the 
            return $tokens[$i + 2][1] ?? false;
        }
    }
    
    return false;
}


/**
 * Get list of methods of the class. Parse every method, and return the full array
 * 
 * @param mixed $content 
 * @return array|false
 */
function _get_class_methods($content) {
    if (empty($content)) {
        return false;
    }
    
    $lines = split_into_lines($content);
    
    $functions = [];
    
    for ($i = 0; $i < count($lines); $i++) {
        $line = $lines[$i];
        
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

