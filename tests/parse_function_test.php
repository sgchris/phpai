<?php

define ('BASEDIR', dirname(__DIR__));

// include the library
require_once BASEDIR.'/'.str_replace('_test', '', basename(__FILE__));
require_once BASEDIR.'/libs/execute_tests.php';

// get the name of the main library function
$funcName = str_ireplace('_test.php', '', basename(__FILE__));

////////////////////////////////////////////////////////////////////////////////////////////////////////////

$tests = [
    'check one line comment' => [
        'function_name' => $funcName,
        'content' => '
                // function one line comment
                function foo() { 
                    $x = 10;
                }
            ',
        'expected' => function($actual) {
            return isset($actual['phpdoc']) &&
                isset($actual['phpdoc']['brief']) &&
                $actual['phpdoc']['brief'] == 'function one line comment';
        },
    ],

    'check multi line comment' => [
        'function_name' => $funcName,
        'content' => '
                /** 
                 * function multiple 
                 * line comment
                 */
                function foo() { 
                    $x = 10;
                }
            ',
        'expected' => function($actual) {
            return isset($actual['phpdoc']) &&
                isset($actual['phpdoc']['brief']) &&
                $actual['phpdoc']['brief'] == "function multiple\nline comment";
        }
    ],

    'check function name' => [
        'function_name' => $funcName,
        'content' => '
                /** 
                 * function multiple 
                 * line comment
                 */
                function foo() { 
                    $x = 10;
                }
            ',
        'expected' => array(
            'name' => 'foo',
        ),
    ],

    'check function content' => [
        'function_name' => $funcName,
        'content' => '
                /** 
                 * function multiple 
                 * line comment
                 */
                function foo() { 
                    $x = 10;
                }
            ',
        'expected' => array(
            'content' => '
                /** 
                 * function multiple 
                 * line comment
                 */
                function foo() { 
                    $x = 10;
                }
            ',
        ),
    ],

    'check function arguments' => [
        'function_name' => $funcName,
        'content' => '
                /** 
                 * function multiple 
                 * line comment
                 */
                function foo($x, $y) { 
                    $x = 10;
                }
            ',
        'expected' => array(
            'arguments' => '$x, $y',
        ),
    ],
];

$untested = [
];


// include the testing library
execute_tests($tests);
