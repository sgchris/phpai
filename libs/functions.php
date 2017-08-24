<?php

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
