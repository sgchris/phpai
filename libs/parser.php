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
    if (preg_match("/^\s*(abstract\s+)*\bclass\s+(.*?)[\s\{]?$/i", $inputLine, $match)) {
        $lineData = array(
            'name' => $match[2],
        );
        
        // @TODO add "Extends" check and add to $lineData
        
        // @TODO add "Implements" check and add to $lineData
        
        return $lineData;
    }
    
    return false;
}


/**
 * Check if the line is a function start (if the line contains function definition)
 * 
 * @param mixed $line 
 * @return  
 */
function is_line_start_of_function($line) {
    // get the function name from a line
    if (preg_match("/^\s*(public|protected|private|final|static|\s)*function\s*(.*?)\s*\((.*?)\)/smi", $line, $match)) {
        $lineData = array(
            'name' => $match[2], 
            'params' => $match[3]
        );
        
        return $lineData;
    }
    
    return false;
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
