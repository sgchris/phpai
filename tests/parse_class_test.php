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
                // class one line comment
                class myclass { 
                    public function foo() {
                    }
                }
            ',
        'expected' => function($actual) {
            return isset($actual['phpdoc']) &&
                isset($actual['phpdoc']['brief']) &&
                $actual['phpdoc']['brief'] == 'class one line comment';
        },
    ],

    'check multi line comment' => [
        'function_name' => $funcName,
        'content' => '
                /** 
                 * class multiple 
                 * line comment
                 */
                class myclass { 
                    public function foo() {
                    }
                }
            ',
        'expected' => function($actual) {
            return isset($actual['phpdoc']) &&
                isset($actual['phpdoc']['brief']) &&
                $actual['phpdoc']['brief'] == "class multiple\nline comment";
        }
    ],

    'check class name' => [
        'function_name' => $funcName,
        'content' => '
                /** 
                 * class multiple 
                 * line comment
                 */
                class myclass { 
                    public function foo() {
                    }
                }
            ',
        'expected' => array(
            'name' => 'myclass',
        ),
    ],

    'check abstract class name' => [
        'function_name' => $funcName,
        'content' => '
                /** 
                 * abstract class multiple 
                 * line comment
                 */
                abstract class myclass { 
                    public function foo() {
                    }
                }
            ',
        'expected' => array(
            'name' => 'myclass',
        ),
    ],

    'check class content' => [
        'function_name' => $funcName,
        'content' => '
                /** 
                 * abstract class multiple 
                 * line comment
                 */
                abstract class myclass { 
                    public function foo() {
                    }
                }
            ',
        'expected' => array(
            'content' => '
                /** 
                 * abstract class multiple 
                 * line comment
                 */
                abstract class myclass { 
                    public function foo() {
                    }
                }
            ',
        ),
    ],

    'check function extends' => [
        'function_name' => $funcName,
        'content' => '
                /** 
                 * abstract class multiple 
                 * line comment
                 */
                abstract class myclass extends mybaseclass { 
                    public function foo() {
                    }
                }
            ',
        'expected' => array(
            'extends' => 'mybaseclass',
        ),
    ],
    
    'check function extends #2' => [
        'function_name' => $funcName,
        'content' => '
                /** 
                 * abstract class multiple 
                 * line comment
                 */
                abstract class myclass extends      mybaseclass { 
                    public function foo() {
                    }
                }
            ',
        'expected' => array(
            'extends' => 'mybaseclass',
        ),
    ],
    
    
    'check parsed functions number' => [
        'function_name' => $funcName,
        'content' => '
                /** 
                 * abstract class multiple 
                 * line comment
                 */
                abstract class myclass extends      mybaseclass { 
                    public function foo() {
                    }
                    
                    abstract protected function bar() {
                    }
                }
            ',
        'expected' => function($result) {
            return isset($result['methods']) && is_array($result['methods']) && count($result['methods']) == 2;
        },
    ],
];



// include the testing library
execute_tests($tests);

