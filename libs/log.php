<?php

class Log {

	// the name of the log file
	const LOG_FILE = 'phpai.log';

	// full path to the log file
	private $filePath = null;

	protected function __construct() {
		$this->filePath = $this->_getLogFile();
	}

	public function writeLog($level, $str) {
		if (file_exists($this->filePath) && is_writeable($this->filePath)) {
			$result = file_put_contents($this->filePath, '['.date('Y-m-d H:i:s').'] ['.strtoupper($level).']'."\t".$str.PHP_EOL, FILE_APPEND);
			return !!$result;
		}

		return false;
	}

	public function debug() {
		return $this->writeLog('debug', implode(' ', func_get_args()));
	}

	public function notice() {
		return $this->writeLog('notice', implode(' ', func_get_args()));
	}

	public function warning() {
		return $this->writeLog('warning', implode(' ', func_get_args()));
	}

	public function error() {
		return $this->writeLog('error', implode(' ', func_get_args()));
	}

	/**
	 * Create log file in the local folder, or in the system temp folder
	 * 
	 * @param string $folder (optional) - provide the name of the 
	 * 	folder where to get/create the log file
	 * @return string|false - the full path to the log file, or false on failure
	 */
	protected function _getLogFile($folder = false) {
		$goRecursive = true;
		if ($folder === false) {
			$folder = __DIR__;
		} elseif (!is_dir($folder)) {
			$goRecursive = false;
			return false;
		}
		
		$filePath = $folder.'/'.self::LOG_FILE;
		if (file_exists($filePath)) {
			if (is_writeable($filePath)) {
				return $filePath;
			} elseif (is_writeable($folder)) {
				// create new log file with temp name
				$newLogFileName = str_replace('.log', uniqid('_', $__moreEntropy = true).'.log', self::LOG_FILE);
				touch($folder.'/'.$newLogFileName);
				return $folder.'/'.$newLogFileName;
			}
		} elseif (is_writeable($folder)) {
			// create new log file with temp name
			touch($folder.'/'.self::LOG_FILE);
			return $folder.'/'.self::LOG_FILE;
		} else {
			if ($goRecursive) {
				return $this->_getLogFile(sys_get_temp_dir());
			}
		}

		return false;
	}

	private static $instance = null;
	
	/**
	 * get instance of the Log class (singleton)
	 */
	public static function getInstance() {
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}

		return self::$instance;
	}
}
