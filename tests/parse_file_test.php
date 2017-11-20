<?php

define ('BASEDIR', dirname(__DIR__));

// include the library
require_once BASEDIR.'/'.str_replace('_test', '', basename(__FILE__));
require_once BASEDIR.'/libs/execute_tests.php';

// get the name of the main library function
$funcName = str_ireplace('_test.php', '', basename(__FILE__));

////////////////////////////////////////////////////////////////////////////////////////////////////////////

$tests = [
    'check phpdoc' => [
        'function_name' => $funcName,
        'content' => '
        		<?php
                /**
                 * file phpdoc
                 * second line of file phpdoc
                 */

                // some class
                class myclass { 
                    public function foo() {
                    }
                }
            ',
        'expected' => function($result) {
			// check that file PHPdoc was parsed successfully
            return isset($result['phpdoc']) && 
            	isset($result['phpdoc']['brief']) && 
            	$result['phpdoc']['brief'] == "file phpdoc\nsecond line of file phpdoc";
        },

    ],

    'check file classes 1' => [
        'function_name' => $funcName,
        'content' => '
        		<?php

                /** 
                 * abstract class multiple 
                 * line comment
                 */
                abstract class myclass { 
                    public function foo() {
                    }
                }
            ',
        'expected' => function($result) {
            return isset($result['classes']) && is_array($result['classes']) && count($result['classes']) == 1;
       },
    ],

    'check file files 1' => [
        'function_name' => $funcName,
        'content' => '
        		<?php

				function foo($a) { }

				function bar($b) { }

				function baz($c) { }

				function bat($d) { }
            ',
        'expected' => function($result) {
            return isset($result['functions']) && is_array($result['functions']) && count($result['functions']) == 4;
       },
    ],

    'check mixed content' => [
        'function_name' => $funcName,
        'content' => '
        		<?php

				class c_foo {
					public function c_foo_method_1() {
					}

					public function c_foo_method_2() {
					}
				}

				function foo($a) { }

				function bar($b) { }

				class c_bar extends c_foo {

					public function c_bar_method_1() {
					}
				}

				function baz($c) { }

				function bat($d) { }
            ',
        'expected' => function($result) {
            return isset($result['functions']) && 
				is_array($result['functions']) && 
				count($result['functions']) == 4 &&
				isset($result['classes']) &&
				count($result['classes']) == 2;
       },
    ],
];



// include the testing library
execute_tests($tests);

