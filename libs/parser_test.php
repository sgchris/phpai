<?php

// include the library
require_once str_replace('_test', '', __FILE__);

$classDeclarations = [
    'class foo {',
    'class foo{',
    "class foo\n",
    'abstract class foo {',
    'abstract class foo extends bar{',
    'abstract class foo implements bar, baz {',
    'class foo extends bar{',
];


$tests = [
    'check class name 0' => [
        'function_name' => 'is_line_start_of_class',
        'content' => $classDeclarations[0],
        'expected' => [
            'name' => 'foo',
        ]
    ],
    'check class name 1' => [
        'function_name' => 'is_line_start_of_class',
        'content' => $classDeclarations[1],
        'expected' => [
            'name' => 'foo',
        ]
    ],
    'check class name 2' => [
        'function_name' => 'is_line_start_of_class',
        'content' => $classDeclarations[2],
        'expected' => [
            'name' => 'foo',
        ]
    ],
    'check class name 3' => [
        'function_name' => 'is_line_start_of_class',
        'content' => $classDeclarations[3],
        'expected' => [
            'name' => 'foo',
        ]
    ],
    'check class name 4' => [
        'function_name' => 'is_line_start_of_class',
        'content' => $classDeclarations[4],
        'expected' => [
            'name' => 'foo',
        ]
    ],
    'check class name 5' => [
        'function_name' => 'is_line_start_of_class',
        'content' => $classDeclarations[5],
        'expected' => [
            'name' => 'foo',
        ]
    ],
    'check class name 6' => [
        'function_name' => 'is_line_start_of_class',
        'content' => $classDeclarations[6],
        'expected' => [
            'name' => 'foo',
        ]
    ],
];


$functionDeclarations = [
    'function foo() {',
    'function foo(){',
    'function foo($a, $b) {',
    'function foo()',
    'public function foo() {',
    'abstract protected function foo($a){',
    'private function foo($x) {',
];

$tests2 = [
    'check function name 0' => [
        'function_name' => 'is_line_start_of_function',
        'content' => $functionDeclarations[0],
        'expected' => [
            'name' => 'foo',
        ]
    ],
    'check function name 1' => [
        'function_name' => 'is_line_start_of_function',
        'content' => $functionDeclarations[1],
        'expected' => [
            'name' => 'foo',
        ]
    ],
    'check function name 2' => [
        'function_name' => 'is_line_start_of_function',
        'content' => $functionDeclarations[2],
        'expected' => [
            'name' => 'foo',
            'params' => '$a, $b'
        ]
    ],
    'check function name 3' => [
        'function_name' => 'is_line_start_of_function',
        'content' => $functionDeclarations[3],
        'expected' => [
            'name' => 'foo',
        ]
    ],
    'check function name 4' => [
        'function_name' => 'is_line_start_of_function',
        'content' => $functionDeclarations[4],
        'expected' => [
            'name' => 'foo',
        ]
    ],
    'check function name 5' => [
        'function_name' => 'is_line_start_of_function',
        'content' => $functionDeclarations[5],
        'expected' => [
            'name' => 'foo',
        ]
    ],
    'check function name 6' => [
        'function_name' => 'is_line_start_of_function',
        'content' => $functionDeclarations[6],
        'expected' => [
            'name' => 'foo',
        ]
    ],
];

// include the testing library
require_once __DIR__.'/execute_tests.php';
execute_tests($tests);
execute_tests($tests2);