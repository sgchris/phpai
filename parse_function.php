<?php

require_once __DIR__.'/libs/functions.php';
require_once __DIR__.'/libs/parser.php';

//////////////////////////////////////////////////////////////////////////////////////////////////

// check if this is the file that's being executed
if (isset($_SERVER['argv']) && $_SERVER['argv'][0] && $_SERVER['argv'][0] == basename(__FILE__)) {
    // receive parameters
    $params = getopt('', [
        'content:',
        'file:'
    ]);
    
    // get the content
    $content = '';
    if (isset($params['content'])) {
        $content = $params['content'];
    } elseif (isset($params['file'])) {
        if (file_exists($params['file']) && is_readable($params['file'])) {
            $content = file_get_contents($params['file']);
        }
    }
    
    // output the result
    if (!empty($content)) {
        echo json_encode(parse_function($content), JSON_PRETTY_PRINT);
    } else {
        echo "Empty content\n";
    }
    
    die();
}

//////////////////////////////////////////////////////////////////////////////////////////////////

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
        $commentLine = str_replace(['/**', '/*', '//', '*'], '', $commentLine);
        $commentLines[$i] = $commentLine;
    }
    
    return _parse_php_doc_lines($commentLines);
}


/**
 * get php doc lines and transform it into an associative array 
 * 
 * @param array $phpDocLines 
 * @return array
 */
function _parse_php_doc_lines(array $phpDocLines) {
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


