<?php
/**
 * Files parser related methods
 */

/**
 * Remove comment starting - all the available variants ("/**", "//", "*", ...)
 * @param mixed $commentLine 
 * @return string
 */
function remove_comment_start($commentLine) {
    return preg_replace('#^\s*[(/|\*)]+#', '', $commentLine);
}



/**
 * check if the line is a class start 
 * (if the line contains class definition like: 
 * "[abstract] class <name> [extends <base class>] [implements <if1, if2, .. ifN>])
 * 
 * @param string $inputLine 
 * @return array|bool - array(name)
 */
function is_line_start_of_class($inputLine) {
    // remove comment block
    $inputLine = preg_replace('%/\*(.*?)\*/%i', '', $inputLine);
    
    // check if the line is commented out
    if (preg_match('%^\s*//%', $inputLine)) {
        return false;
    }
    
    // get the function name from a line
    $isClassLine = false;
    $lineData = array();
    if (preg_match("/^\s*[(!?abstract)]*\s*class\s*(.*?)[\s\{]+/i", $inputLine, $match)) {
        $isClassLine = true;
        $lineData['name'] = $match[1];
    }
    
    return empty($lineData) ? false : $lineData;
}


/**
 * Check if the line is a function start (if the line contains function definition)
 * 
 * @param mixed $line 
 * @return  
 */
function is_line_start_of_function($line) {
    // get the function name from a line
    if (preg_match("/^\s*[(!?abstract|!?public|!?protected|!?private|!?final|\s)]*\s*function\s*(.*?)\s*\((.*?)\)/i", $line, $match)) {
        $lineData = array(
            'name' => $match[1], 
            'params' => $match[2]
        );
        
        return $lineData;
    }
    
    return false;
}

/**
 * Check if the line is a comment line, i.e. starts with "//" or "/*" or just "*"
 * 
 * @param mixed $line 
 * @return boolean
 */
function is_comment_line($line) {
    return preg_match('#^\s*[(/|\*)]+#', $line);
}

/**
 * get php doc lines and transform it into an associative array 
 * 
 * @param array $phpDocLines 
 * @return array
 */
function parse_php_doc_lines(array $phpDocLines) {
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
 * Get the lines of a statement. Statement is defined between {...} brackets
 * 
 * @param array $contentLines 
 * @param mixed $startFromLineNumber 
 * @return array
 */
function get_statement_contents(array $contentLines, $startFromLineNumber = 0) {
    $statementContentLines = array();
    
    // gather all the statement lines
    $bracketsBalance = false;
    for (;$bracketsBalance !== 0 && $startFromLineNumber < count($contentLines); $startFromLineNumber++) {
        $line = $contentLines[$startFromLineNumber];
        
        // get the brackets statistics
        $totalOpens = substr_count($line, '{');
        $totalCloses = substr_count($line, '}');

        // check the first open tag of a function
        if ($totalOpens > 0 && $bracketsBalance === false) {
            $bracketsBalance = 0;
        } elseif ($bracketsBalance === false) {
            // statement's first bracket still didn't appear
            continue;
        }

        $bracketsBalance+= $totalOpens;
        $bracketsBalance-= $totalCloses;
        $statementContentLines[] = $line;
    }
    
    return $statementContentLines;
}


/**
 * get PHP tokens from the source
 * @param mixed $content 
 * @return  
 */
function get_tokens($content) {
    $tokens = stripos($content, '<?') === false ? token_get_all('<?php ' . $content) : token_get_all($content);
    
    // convert tokens to strings
    foreach ($tokens as $i => $tokenData) {
        if (is_array($tokenData) && isset($tokenData[0])) {
            $tokens[$i][0] = token_name($tokenData[0]);
        }
    }
    
    return $tokens;
}