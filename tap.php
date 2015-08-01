<?PHP
class Tap {
	protected static $counter = 0;
	protected static $passed = 0;
	protected static $failed = 0;
    protected static $debug = false;
	protected static $base_url_path = '';

	function destruct() {
		list($counter, $passed, $failed) = array(Tap::$counter, Tap::$passed, Tap::$failed);
		echo "\n$counter tests run: $passed passed, $failed failed.\n\n";
	}
	
	//Pass in the name of a static var (counter, passed, failed) as a string, and iterate it.
	function iter($var) {
		static::${$var} += 1;
		return static::${$var};
	
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
        if (static::debug === TRUE) {
			print_r(array($msg, $class_name, $function_name, $expected_output, $input));
		}
		
		if (self::checkClassAndMethod($class_name, $function_name) === FALSE) {
			return;
		}
		$class = self::retClass( $class_name );
		$instance = new $class;
		$returned = $instance->$function_name( $input );

        if ( ! is_scalar($expected_output)) {
            $expected_output = json_encode($expected_output);
        }
        if ( ! is_scalar($returned)) {
            $returned = json_encode($returned);
        }
		if ($returned == $expected_output) {
			$outcome = GREEN . "matched on `$returned`." . REGULAR ;
			static::iter('passed');
		} else {
			$outcome = RED . "Unmatched comparison: Expected `$expected_output`; received `$returned`." . REGULAR;
			static::iter('failed');
		}
		echo '#' . self::iter('counter') . " $msg: $outcome";
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
			$outcome = GREEN . "instanceof matched on `$expected_output." . REGULAR;
			$this->passed++;
		} else {
			$returned = get_class( $returned );
			$outcome = RED . "Unmatched instance type: Expected `$expected_output`; received `$returned`." . REGULAR;
			$this->failed++;
		}
		echo '#' . self::iter('counter') . " $msg: $outcome";
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
			$outcome = RED . "Error: returned value not a resource." . REGULAR;
			$this->failed++;
		} elseif ($returned == $expected_resource_type) {
			$outcome = GREEN . "matched resource type on`$returned`." . REGULAR;
			$this->passed++;
		} else {
			$outcome = RED . "Unmatched resource type: Expected `$expected_resource_type`; received `$returned`." . REGULAR;
			$this->failed++;
		}
		echo '#' . self::iter('counter') . " $msg: $outcome\n";
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

	function set_base_url_path( $base_url_path ) {
		static::$base_url_path = $base_url_path;
		return 'base_url_path set to: '.static::$base_url_path;
	}

	function web_require( $msg, $url, $params, $expected_output ) {
		$outcome = Tap::web_ok( $url, $params, $expected_output );
		if ($outcome === FALSE) {
			throw new Exception('Required check failed.');
		}
	}
	
	function web_ok( $msg, $url, $params, $expected_output ) {
		if (static::$debug === TRUE) {
			print_r(array($msg, $class_name, $function_name, $expected_output, $input));
		}

		$params = http_build_query($params);

		$returned = self::curl_text( $url . '?' . $params);

        if ( ! is_scalar($expected_output)) {
            $expected_output = json_encode($expected_output);
        }
        if ( ! is_scalar($returned)) {
            $returned = json_encode($returned);
        }
		if ($returned == $expected_output) {
			$outcome = GREEN . "matched on `$returned`." . REGULAR ;
			static::$passed++;
			$return = TRUE;
		} else {
			$outcome = RED . "Unmatched comparison: Expected `$expected_output`; received `$returned`." . REGULAR;
			static::$failed++;
			$return = FALSE;
		}
		echo '#' . self::iter('counter') . " $msg: $outcome";
		return $return;
	}

	function curl( $url, $post) {
		$ch = curl_init();
		$url = static::$base_url_path . $url;
		
		// curl_setopt($ch, CURLOPT_URL, $url);
		// curl_setopt($ch, CURLOPT_HEADER, TRUE);
		// curl_setopt($ch, CURLOPT_POST, 1);
		// curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

		// $head = curl_exec($ch);

		// curl_close($ch);


		
		return $head;
	}
	
	function curl_text( $url, $post = NULL) {
		$url = static::$base_url_path . $url;
		$head = file_get_contents($url);
		
		return $head;
	}
		
################################## TEST HARNESS #########################

	//Get all .tap files in the provided directory.
	function include_all_tap_tests_in_dir( $dir = NULL ) {
		if ($dir == NULL || empty($dir)) {
			throw new Exception('No directory specified for'.__function__." >> ".__line__);
		}
		if (!file_exists( $dir )) {
			throw new Exception("Specified directory '".$dir."' does not exist. >> ".__line__);
		}
		if (!is_dir( $dir )) {
			throw new Exception("Specified directory '".$dir."' is not directory. >> ".__line__);
		}
		$contents = scandir( $dir );
		foreach ($contents as $k => $v) {
			if (is_dir($v)
			&& ($v !== '.' && $v !== '..')) {
				self::include_all_tap_tests_in_dir( $dir .'/'. $v);
			} else {
				//If it's a .tap file, include it.
				if (strpos($v, '.tap') === strlen($v) - 4) {
					include_once $v;
					echo "\nFile: ".BLUE.$v.REGULAR;
					$class_name = substr($v, 0, -4);
					if (!class_exists( $class_name )) {
						throw new Exception('.tap file missing class by the same name: '.$v);
					}
					
					//Every .tap test contains one class, with its own file name as the class name, which contains all of the functions to test in it.
					$tests[$class_name] = get_class_methods( $class_name );
				}
			}
		}
		if (empty($tests)) {
			throw new Exception('No .tap tests contained in specified directory: '.$dir);
		}
		return $tests;
	}
	
	function execute( $dir = NULL) {
		//CLI

		
		try {
			$tests = self::include_all_tap_tests_in_dir( $dir );
			foreach ($tests as $class => $method_list) {
				foreach ($method_list as $k => $method) {
					if (strpos($method, 'test') !== 0) {
						continue;
					}
					$class::$method();
				}
			}
		} catch (Exception $e) {
			echo "\nException: ".$e->getMessage()."\n\n";
			exit;
		}
		self::destruct();
	}
}

//CLI
clearstatcache();
if (!empty($argv)) {
	new Helper('cli');
	if ($argc <= 1) {
		echo "\nSyntax: php ".__file__." <absolute path to directory containing TAP tests>\n\n";
		return;
	}
	$dir = $argv[1];
	Tap::execute( $dir );
} else { //Web
	$helper = new Helper('web');
	$dir = $_REQUEST['dir'];
}

class Helper {
	function __construct($src) {
		switch ($src) {
			case 'web':
				define ('REGULAR', "</span>");
				define ('GREEN', "<span style='color:green'>");
				define ('RED', "<span style='color:red'>");
				define ('BLUE', "<span style='color:teal'>");
				break;
			case 'cli':
				define ('REGULAR', "\n\e[39m");
				define ('GREEN', "\e[32m");
				define ('RED', "\e[31m");
				define ('BLUE', "\e[94m");
				break;
		}
	}
}