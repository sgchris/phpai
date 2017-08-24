<?php

require_once __DIR__ . '/console_helper.php';

/**
 * execute list of tests
 *
 * @param array $tests
 * @return boolean
 */
function execute_tests($tests) {
	$result = true;

	foreach ($tests as $testName => $testData) {
		echo "\n------\n", _brown("[executing \"{$testName}\"]"), "\n";

		// tested function
		$functionName = $testData['function_name'];

		if (!function_exists($functionName)) {
			echo "function {$functionName} is not defined\n";
			$result = false;
			continue;
		}

		$functionActualResult = $functionName($testData['content']);
		if (!actualResultIsExpected($functionActualResult, $testData['expected'])) {
			echo "Expected: ", var_export($testData['expected'], true), "\n",
				"Actual: ", var_export($functionActualResult, true), "\n";
			echo _red("Failed"), "\n";
			$result = false;
			continue;	
		}

		echo _green("Passed!"), "\n";
	}


	echo "\n";
	echo ($result ? _green('SUCCESS') : _red('OVERALL FAIL')), "\n";

	return $result;
}

// compare results
function actualResultIsExpected($actual, $expected, $exactMatch = false) {
	// check booleans
	if (is_bool($actual) && is_bool($expected)) {
		return $actual == $expected;
	}

	// check scalars
	if ( 
		(is_string($actual) || is_numeric($actual)) && 
		(is_string($expected) || is_numeric($expected))
	) {
		return $exactMatch ? ($actual === $expected) : ($actual == $expected);
	}

	if (is_array($actual) && is_array($expected)) {
		if ($exactMatch) {
			$diff1 = array_diff($actual, $expected);
			$diff2 = array_diff($expected, $actual);
			return empty($diff2) && empty($diff2);
		} else {
			// check that all the expected keys, exists and equal in the actual result
			$diff = array_diff($expected, $actual);
			return empty($diff);
		}
	}

	// the types do not match
	return false;
}