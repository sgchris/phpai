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
function parse_class($content) {
    if (empty($content)) {
        return [];
    }
    
    $parsedData = [
        'content' => $content,
        'phpdoc' => _get_class_phpdoc($content),
        'name' => _get_function_name($content),
        'arguments' => _get_function_arguments($content),
    ];
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