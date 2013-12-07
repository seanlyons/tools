<?PHP
bu
$test_file = $argv[1];
$test_class = $argv[2];

execute( $test_file, $test_class );

function execute( $test_file = NULL, $test_class = NULL) {

	//Ensure input exists.
	if ( ! empty($test_file)) {
		echo "No test file specified.\n";
		return;
	}
	if ( ! empty($test_class)) {
		echo "No test class specified.\n";
		return;
	}

	//If '.php' isn't included in the string, add it.
	if (strrpos($test_file, '.php', -4) === FALSE) {
		$test_file .= '.php';
	}

	if ( ! file_exists( $test_file )) {
		echo "No such file exists.\n";
		return;
	}
	if ( ! class_exists( $test_file )) {
		echo "No such file exists.\n";
		return;
	}

	$methods = get_class_methods( $test_file );

	if (empty( $methods)) {
		echo "No methods exist in '$test_file'.\n";
		return;
	}

	foreach ($methods as $temp => $expectations) {
		$description = $expectations[0];
		$input_params = $expectations[1];
		$input_params = $expectations[1];
	}
}