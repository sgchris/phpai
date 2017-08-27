<?php
/**
 * Files parser related methods
 */



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
 * Check if the line is a start of a comment block (
 * 
 * @param mixed $line 
 * @return  
 */
function isLineCommentBlockStart($line) {
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
