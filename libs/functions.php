<?php

/**
 * Check if executed script is the one provided - if yes, execute it and 
 * @param mixed $filePath 
 * @return  
 */
function execute_this_file_if_requested($filePath) {
    
    // get the name of the function from the file name
    $functionName = preg_replace('#\.php$#i', '', basename($filePath));
    
    // check if this is the file that's being executed
    if (isset($_SERVER['argv']) && $_SERVER['argv'][0] && $_SERVER['argv'][0] == basename($filePath)) {
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
            echo json_encode($functionName($content), JSON_PRETTY_PRINT);
        } else {
            echo "Empty content\n";
        }
        
        die();
    }
}


/**
 * replace only the first occurrence of a string
 * 
 * @param string $from
 * @param string $to
 * @param string $subject
 * @return string
 */
function str_replace_first($from, $to, $subject) {
    $from = '/'.preg_quote($from, '/').'/';
    return preg_replace($from, $to, $subject, 1);
}

/**
 * Split the text into lines
 * 
 * @param mixed $content 
 * @return array
 */
function split_into_lines($content) {
    return preg_split('#[\r\n]+#', $content);
}

/**
 * array_diff, but recursive 
 * 
 * @param  mixed $aArray1 
 * @param  mixed $aArray2 
 * @return array
 */
function array_diff_recursive($aArray1, $aArray2) 
{
    $aReturn = array();

    foreach ($aArray1 as $mKey => $mValue) {
        if (array_key_exists($mKey, $aArray2)) {
            if (is_array($mValue)) {
                $aRecursiveDiff = arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; 
                }
            } else {
                if ($mValue != $aArray2[$mKey]) {
                    $aReturn[$mKey] = $mValue;
                }
            }
        } else {
            $aReturn[$mKey] = $mValue;
        }
    }
    return $aReturn;
} 