<?php

// include the library
require_once str_replace('_test', '', __FILE__);

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
require_once __DIR__.'/libs/execute_tests.php';
execute_tests($tests);