<?php

require_once __DIR__.'/libs/parser.php';

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
        'phpdoc' => getFunctionPhpDoc($content),
        'name' => getFunctionName($content),
        'arguments' => getFunctionArguments($content),
        'arguments' => getFunctionArguments($content),
    ];
}

////////////// private //////////////

/**
 * Get PHPDoc from function's content
 * 
 * @param mixed $content - the content of the function
 * @return array
 */
function getFunctionPhpDoc($content) {
    if (empty($content)) {
        return [];
    }
    
    $tokens = token_get_all($content);
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
    
    $commentLines = preg_split('#\r*\n*#', $commentCode);

    // get only the comment content
    foreach ($commentLines as $i => $commentLine) {
        $commentLine = trim($commentLine);
        $commentLine = str_replace(['/**', '/*', '//', '*'], '', $commentLine);
        $commentLines[$i] = $commentLine;
    }
    
    return parsePhpDocLines($commentLines);
}


/**
 * get php doc lines and transform it into an associative array 
 * 
 * @param array $phpDocLines 
 * @return array
 */
function parsePhpDocLines(array $phpDocLines) {
    // the final result
    $result = array();
    // the currently tracked key/value
    $currentKey = 'brief';
    $currentVal = '';
    foreach ($phpDocLines as $line) {
        // remove the leading comment operators (preserve the white-space after the comment opener/closer)
        $line = preg_replace('%^\s*/?\**/?\s?%i', '', $line);
        // check if there's a new key in that line
        if (preg_match('%^@(.*?)(\s|$)%', $line, $match)) {
            // save the previous key/val
            // if the key already exists - create new key with plural "s" and 
            // insert the new value there. 
            if (isset($result[$currentKey.'s'])) {
                $result[$currentKey.'s'][] = $currentVal;
            } elseif (isset($result[$currentKey])) {
                $result[$currentKey.'s'] = array($result[$currentKey], $currentVal);
                unset($result[$currentKey]);
            } else {
                $result[$currentKey] = trim($currentVal);
            }
            
            // reset the current key and val
            $currentKey = $match[1];
            $currentVal = trim(str_replace('@'.$currentKey, '', $line));
        } else {
            // add this line to the previous key
            $currentVal = $currentVal . (!empty($currentVal) ? "\n" : '') . $line;
        }
    }
    // write the last key/val
    $result[$currentKey] = trim($currentVal);
    // check if the doc is empty (empty brief and no other keys)
    if (count($result) == 1 && $currentKey == 'brief' && empty($result[$currentKey])) {
        return array();
    }
    return $result;
}