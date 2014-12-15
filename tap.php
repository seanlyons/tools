<?PHP
define ('NL', "\n\e[39m");
define ('SUCCESS', "\e[32m");
define ('FAILURE', "\e[31m");

class Tap {
	protected $counter = 0;
	protected $passed = 0;
	protected $failed = 0;
    protected $debug = false;
    
	function __destruct() {
		list($counter, $passed, $failed) = array($this->counter, $this->passed, $this->failed);
		echo "\n$counter tests run: $passed passed, $failed failed.\n\n";
	}
    /**
    * Check to see if the given method returns the expected output, provided optional input.
    * @param	$msg				string  A description of the test; echoed as it is run.
    * @param	$class_name			string  The name of the class containing the method to be tested.
    * @param 	$function_name		string	The name of the method to be tested.
    * @param 	$expected_output	mixed   The value of the expected output
    * @param 	$input				mixed   //TODO: Currently only takes one parameter; make it take indefinite params.
	* @return	none
    **/
	function is($msg, $class_name, $function_name, $expected_output = NULL, $input = NULL) {
        if ($this->debug === TRUE) { print_r(array($msg, $class_name, $function_name, $expected_output, $input)); }
		if ($this->checkClassAndMethod($class_name, $function_name) === FALSE) {
			return;
		}
		$class = $this->retClass( $class_name );
		$instance = new $class;
		$returned = $instance->$function_name( $input );

        if ( ! is_scalar($expected_output)) {
            $expected_output = json_encode($expected_output);
        }
        if ( ! is_scalar($returned)) {
            $returned = json_encode($returned);
        }
		if ($returned == $expected_output) {
			$outcome = SUCCESS . "matched on `$returned`." . NL ;
			$this->passed++;
		} else {
			$outcome = FAILURE . "Unmatched comparison: Expected `$expected_output`; received `$returned`." . NL;
			$this->failed++;
		}
		echo '#' . ++$this->counter . " $msg: $outcome";
	}

    /**
    * Check to see if the given method returns an object of the expected type.
    * @param	$msg				string  A description of the test; echoed as it is run.
    * @param	$class_name			string  The name of the class containing the method to be tested.
    * @param 	$function_name		string	The name of the method to be tested.
    * @param 	$expected_output	mixed   The value of the expected output
    * @param 	$input				mixed   //TODO: Currently only takes one parameter; make it take indefinite params.
	* @return	none
    **/	
	function is_instance_of($msg, $class_name, $function_name, $expected_output = NULL, $input = NULL) {
		if ($this->checkClassAndMethod($class_name, $function_name) === FALSE) {
			return;
		}
        
		$class = $this->retClass( $class_name );
		$instance = new $class;
		$returned = $instance->$function_name();        
		
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
	
    /**
    * Check to see if the given method returns a resource of the expected type.
    * @param	$msg				string  A description of the test; echoed as it is run.
    * @param	$class_name			string  The name of the class containing the method to be tested.
    * @param 	$function_name		string	The name of the method to be tested.
    * @param 	$expected_output	mixed   The value of the expected output
    * @param 	$input				mixed   //TODO: Currently only takes one parameter; make it take indefinite params.
	* @return	none
    **/	
	function is_resource_type($msg, $class_name, $function_name, $expected_resource_type = NULL, $input = NULL) {
		if ($this->checkClassAndMethod($class_name, $function_name) === FALSE) {
			return;
		}
		$class = $this->retClass( $class_name );
		$instance = new $class;
		$returned = $instance->$function_name();
        
		$returned = get_resource_type( $returned );
		if ($returned === FALSE) {
			$outcome = FAILURE . "Error: returned value not a resource." . NL;
			$this->failed++;
		} elseif ($returned == $expected_resource_type) {
			$outcome = SUCCESS . "matched resource type on`$returned`." . NL;
			$this->passed++;
		} else {
			$outcome = FAILURE . "Unmatched resource type: Expected `$expected_resource_type`; received `$returned`." . NL;
			$this->failed++;
		}
		echo '#' . ++$this->counter . " $msg: $outcome\n";
		print_r($returned);		
	}
	
    /**
    * Prints output from the specified function: good for debugging.
    * @param	$msg				string  A description of the test; echoed as it is run.
    * @param	$class_name			string  The name of the class containing the method to be tested.
    * @param 	$function_name		string	The name of the method to be tested.
    * @param 	$expected_output	mixed   The value of the expected output
    * @param 	$input				mixed   //TODO: Currently only takes one parameter; make it take indefinite params.
	* @return	none
    **/	
	function check($msg, $class_name, $function_name, $expected_output = NULL, $input = NULL) {
		if ($this->checkClassAndMethod($class_name, $function_name) === FALSE) {
			return;
		}
		$class = $this->retClass( $class_name );
		$instance = new $class;
		$returned = $instance->$function_name();
		
		if (is_scalar($returned)) {
			//It's fine to output with a simple echo.
		} elseif (is_array($returned)) {
			$returned = json_encode($returned);
		} elseif (is_object($returned)) {
			$returned = get_class($returned);
		} elseif (is_resource($returned)) {
			$returned = get_resource_type( $returned );
		}		
		
		echo "#CHECKING: $msg: \n\t> $returned\n";
	}

########################################################################
	
	
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
	
############################ WEB TESTS #################################

	function web_ok( $url, $params, $expected_output ) {
		if ($this->debug === TRUE) {
			print_r(array($msg, $class_name, $function_name, $expected_output, $input));
		}
		
		$params = http_build_query($params);
		
		$data = curl( $url . '?' . $params);
		
		print_r($data);
		
		return;
		
        if ( ! is_scalar($expected_output)) {
            $expected_output = json_encode($expected_output);
        }
        if ( ! is_scalar($returned)) {
            $returned = json_encode($returned);
        }
		if ($returned == $expected_output) {
			$outcome = SUCCESS . "matched on `$returned`." . NL ;
			$this->passed++;
		} else {
			$outcome = FAILURE . "Unmatched comparison: Expected `$expected_output`; received `$returned`." . NL;
			$this->failed++;
		}
		echo '#' . ++$this->counter . " $msg: $outcome";
	}	

	function curl( $url, $post = NONE) {
		$ch = curl_init();
		
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, TRUE);
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		$head = curl_exec($ch);
		
		curl_close($ch);
		
		return $head;
	}	
}