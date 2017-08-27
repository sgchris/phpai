<?php

require_once __DIR__.'/libs/functions.php';
require_once __DIR__.'/libs/parser.php';

execute_this_file_if_requested(__FILE__);

/**
 * Parse function contents
 *
 * @param string $content
 * @return string JSON
 */
function parse_function($content) {
    if (empty($content)) {
        return [];
    }

    $parsedData = [
        'content' => $content,
        'phpdoc' => _get_function_phpdoc($content),
        'name' => _get_function_name($content),
        'arguments' => _get_function_arguments($content),
    ];
    
    return $parsedData;
}

////////////// private //////////////


/**
 * Get PHPDoc from function's content
 * 
 * @param mixed $content - the content of the function
 * @return array
 */
function _get_function_phpdoc($content) {
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
            
            // check if the function was already declared
            if ($tokenData[0] == T_FUNCTION) {
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
 * Get the name of the function
 * 
 * @param mixed $content 
 * @return string
 */
function _get_function_name($content) {
    $lines = split_into_lines($content);
    
    foreach ($lines as $line) {
        if (($functionData = is_line_start_of_function($line)) !== false) {
            return $functionData['name'];
        }
    }
    
    return false;
}


/**
 * Get the arguments of the function
 * 
 * @param mixed $content 
 * @return string
 */
function _get_function_arguments($content) {
    $lines = split_into_lines($content);
    
    foreach ($lines as $line) {
        if (($functionData = is_line_start_of_function($line)) !== false) {
            return $functionData['params'];
        }
    }
    
    return false;
}


