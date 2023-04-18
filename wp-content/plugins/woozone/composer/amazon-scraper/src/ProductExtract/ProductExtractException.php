<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract;

if (class_exists(ProductExtractException::class) !== true) { class ProductExtractException extends \Exception {

	// Required __construct() function
	// Redefine the exception so message isn't optional
	public function __construct($message, $code = 0, Exception $previous = null) {

		parent::__construct($message, $code, $previous);
	}

	// custom string representation of object
	public function __toString() {

		return parent::__toString();
	}

	// render an exception based on debug mode (debug >= 0)
	public function render( $debug=0 ) {

		$ret = array();
		$ret[] = "Exception '{$this->message}'";

		// development mode
		if ( $debug ) {

			$ret[] = "in class " . get_class($this);
			$ret[] = "from file {$this->file}({$this->line})";
			$ret[] = 'Stack trace:';
			$ret[] = $this->getTraceAsString();
			//$ret[] = $this->jTraceEx( $this );
			//ob_start(); var_dump('<pre>', debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), '</pre>'); $ret[] = ob_get_clean();
			$ret[] = '';
		}

		$ret = implode( PHP_EOL, $ret );
		return $ret;
	}

	/**
	 * jTraceEx() - provide a Java style exception trace
	 * @param $exception
	 * @param $seen      - array passed to recursive calls to accumulate trace lines already seen
	 *                     leave as NULL when calling this function
	 * @return array of strings, one entry per trace line
	 */
	public function jTraceEx( $e, $seen=null ) {

		$starter = $seen ? 'Caused by: ' : '';
		$result = array();
		if ( !$seen ) $seen = array();
		$trace = $e->getTrace();
		$prev = $e->getPrevious();
		$result[] = sprintf('%s%s: %s', $starter, get_class($e), $e->getMessage());
		$file = $e->getFile();
		$line = $e->getLine();

		while (true) {
			$current = "$file:$line";
			if (is_array($seen) && in_array($current, $seen)) {
				$result[] = sprintf(' ... %d more', count($trace)+1);
				break;
			}
			$result[] = sprintf(
				' at %s%s%s(%s%s%s)',
				count($trace) && array_key_exists('class', $trace[0]) ? str_replace('\\', '.', $trace[0]['class']) : '',
				count($trace) && array_key_exists('class', $trace[0]) && array_key_exists('function', $trace[0]) ? '.' : '',
				count($trace) && array_key_exists('function', $trace[0]) ? str_replace('\\', '.', $trace[0]['function']) : '(main)',
				$line === null ? $file : basename($file),
				$line === null ? '' : ':',
				$line === null ? '' : $line
			);
			if (is_array($seen)) {
				$seen[] = "$file:$line";
			}
			if (!count($trace)) {
				break;
			}

			$file = array_key_exists('file', $trace[0]) ? $trace[0]['file'] : 'Unknown Source';

			$line = array_key_exists('file', $trace[0])
				&& array_key_exists('line', $trace[0]) && $trace[0]['line'] ? $trace[0]['line'] : null;

			array_shift($trace);
		}

		$result = join("\n", $result);
		if ($prev) {
			$result  .= "\n" . jTraceEx($prev, $seen);
		}

		return $result;
	}

} } // end class