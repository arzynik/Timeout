<?php

/**
 * Timeout
 *
 * Timeout is a PHP implimentation of Javascript's setTimeout. It runs an asynchronous process in the
 * background while the rest of your code can continue to run. Like forking a process, or spawning a 
 * thread. Timeout is extremely useful for sending off mail, or 3rd party API callbacks like twilio or phaxio.
 *
 * @package Timeout
 * @author  Devin Smith
 * @license MIT
 * @repo	https://github.com/arzynik/Timeout
 *
 */

class Timeout {
	public function __construct($func, $ms = null, $options = array()) {

		// a closure object that will be converted to a SuperClosure
		$this->func = $func;

		// ms to wait
		if ($ms) {
			$this->ms = $ms;
		}
		
		// disable async to debug your code
		if ($options['async']) {
			$this->async = $options['async'];
		}
		
		// optional require function that will be evaled. this is NOT a SuperClosure
		if ($options['require']) {
			$this->require = $options['require'];
		}

		// the path of the executable
		$this->path = $options['path'] ? $options['path'] : './timeout-cli.php';
		
	}

	// static run
	public static function run($func, $ms = null, $options) {
		$timeout = new Timeout($func, $ms, $options);
		return $timeout->exec();
	}

	public function exec() {
		$closure = new SuperClosure($this->func);
		$encoded = base64_encode(serialize($closure));

		if ($this->ms) {
			$sleep = ' -s='.$this->ms;
		}
		
		$cmd = $this->path.$sleep.' -c='.str_replace("'",'"',escapeshellarg($encoded));
		
		if ($this->require) {
			$closure = new SuperClosure($this->require);
			$require = base64_encode(serialize($closure));
			$cmd .= ' -r='.str_replace("'",'"',escapeshellarg($require));
		}

		if ($this->async) {

			exec('nohup '.$cmd.' > /dev/null 2>&1 &');

		} else {

			// disable async to test
			exec($cmd, $o);
			return $o;
		}
	}
}


/**
 * SuperClosure
 *
 * The SuperClosure class encapsulates a PHP Closure and adds new capabilities like serialization and code retrieval.
 * It uses the FunctionParser library to acquire information about the Closure to aid in serialization. Because the
 * class works with Closures, it requires PHP version 5.3+. DISCLAIMERS: This class is not designed to perform well due
 * to the nature of the techniques it uses. Also, you should note that it uses the `extract()` and `eval()` functions to
 * make serialization/unserialization possible.
 *
 * @package SuperClosure
 * @author  Jeremy Lindblom
 * @license MIT
 * @repo	https://github.com/jeremeamia/super_closure
 *
 */

class SuperClosure {

	protected $closure = NULL;
	protected $reflection = NULL;
	protected $code = NULL;
	protected $used_variables = array();

	public function __construct($function) {
		if (!$function instanceOf Closure) {
			throw new InvalidArgumentException();
		}

		$this->closure = $function;
		$this->reflection = new ReflectionFunction($function);
		$this->code = $this->_fetchCode();
		$this->used_variables = $this->_fetchUsedVariables();
	}

	public function __invoke() {
		$args = func_get_args();
		return $this->reflection->invokeArgs($args);
	}

	public function getClosure() {
		return $this->closure;
	}

	public function _fetchCode() {
		// Open file and seek to the first line of the closure
		$file = new SplFileObject($this->reflection->getFileName());
		$file->seek($this->reflection->getStartLine()-1);

		// Retrieve all of the lines that contain code for the closure
		$code = '';
		while ($file->key() < $this->reflection->getEndLine()) {
			$code .= $file->current();
			$file->next();
		}

		// Only keep the code defining that closure
		$begin = strpos($code, 'function');
		$end = strrpos($code, '}');
		$code = substr($code, $begin, $end - $begin + 1);

		return $code;
	}

	public function getCode() {
		return $this->code;
	}

	public function getParameters() {
		return $this->reflection->getParameters();
	}

	protected function _fetchUsedVariables() {
		// Make sure the use construct is actually used
		$use_index = stripos($this->code, 'use');
		if (!$use_index) {
			return array();
		}

		// Get the names of the variables inside the use statement
		$begin = strpos($this->code, '(', $use_index) + 1;
		$end = strpos($this->code, ')', $begin);
		$vars = explode(',', substr($this->code, $begin, $end - $begin));

		// Get the static variables of the function via reflection
		$static_vars = $this->reflection->getStaticVariables();
	
		// Only keep the variables that appeared in both sets
		$used_vars = array();
		foreach ($vars as $var) {
			$var = trim($var, ' $&amp;');
			$used_vars[$var] = $static_vars[$var];
		}

		return $used_vars;
	}

	public function getUsedVariables() {
		return $this->used_variables;
	}

	public function __sleep() {
		return array('code', 'used_variables');
	}

	public function __wakeup() {
		extract($this->used_variables);

		eval('$_function = '.$this->code.';');
		if (isset($_function) && $_function instanceOf Closure) {
			$this->closure = $_function;
			$this->reflection = new ReflectionFunction($_function);
		} else {
			throw new Exception();
		}
	}
}