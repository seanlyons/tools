<?PHP
define ('NL', "\n\e[39m");
define ('SUCCESS', "\e[32m");
define ('FAILURE', "\e[31m");

class Tap {
	protected $counter = 0;
	protected $passed = 0;
	protected $failed = 0;

	function __destruct() {
		list($counter, $passed, $failed) = array($this->counter, $this->passed, $this->failed);
		echo "\n$counter tests run: $passed passed, $failed failed.\n\n";
	}

	function is($msg, $class_name, $function_name, $input, $expected_output) {
		if ($this->checkClassAndMethod($class_name, $function_name) === FALSE) {
			return;
		}
		
		$class = $this->retClass( $class_name );
		$returned = $class::$function_name();
		if ($returned == $expected_output) {
			$outcome = "matched on `$returned`.\n";
			$this->passed++;
		} else {
			$outcome = "Unmatched scalar: Expected `$expected_output`; received `$returned`.\n";
			$this->failed++;
		}
		echo '#' . ++$this->counter . " $msg: $outcome\n";
	}
	
	function is_instance_of($msg, $class_name, $function_name, $input, $expected_output) {
		if ($this->checkClassAndMethod($class_name, $function_name) === FALSE) {
			return;
		}
		$class = $this->retClass( $class_name );
		$returned = $class::$function_name();
		
		if ($returned instanceof $expected_output) {
			$outcome = SUCCESS . "instanceof matched on `$expected_output." . NL;
			$this->passed++;
		} else {
			$returned = get_class( $returned );
			$outcome = FAILURE . "Unmatched instance type: Expected `$expected_output`; received `$returned`." . NL;
			$this->failed++;
		}
		echo '#' . ++$this->counter . " $msg: $outcome";
	}	
	
	function is_resource_type($msg, $class_name, $function_name, $input, $expected_resource_type) {
		if ($this->checkClassAndMethod($class_name, $function_name) === FALSE) {
			return;
		}
		
		$class = $this->retClass( $class_name );
		$returned = $class::$function_name();
		$returned = get_resource_type( $returned );
		if ($returned === FALSE) {
			$outcome = "Error: returned value not a resource.\n";
			$this->failed++;
		} elseif ($returned == $expected_resource_type) {
			$outcome = "matched resource type on`$returned`.\n";
			$this->passed++;
		} else {
			$outcome = "Unmatched resource type: Expected `$expected_resource_type`; received `$returned`.\n";
			$this->failed++;
		}
		echo '#' . ++$this->counter . " $msg: $outcome\n";
		print_r($returned);		
	}
	
	function retClass( $class_name ) {
		return new $class_name;	
	}
	
	function checkClassAndMethod( $c, $f ) {
		if ( ! class_exists($c)) {
			echo "Class `$c` does not exist.\n";
			return FALSE;
		}
		if ( ! method_exists( $c, $f)) {
			echo "Method `$f` does not exist in class `$c`.\n";
			return FALSE;
		}
		return TRUE;
	}
}