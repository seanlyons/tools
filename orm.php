<?PHP
// echo __line__."\n";
// function __autoload( $classname) {
// echo $classname."\n";
	// if (class_exists('/home/sean/php/tests/' . strtolower($classname) . '.php')) {
		// require ('/home/sean/php/tests/' . strtolower($classname) . '.php');
	// }
	// if (class_exists('/home/sean/php/common/' . strtolower($classname) . '.php')) {
		// require ('/home/sean/php/common/' . strtolower($classname) . '.php');
	// }
// }

include 'tap.php';
include '../common/orm.php';

class Tests_Orm extends Tap {
	function test() {
		$db_params = array (
			'dbname' => 'seandb',
			'host' => 'localhost',
			'username' => 'seandb',
			'password' => '_MgpCev6jbNYNlYUldQO'
		);
		Tap::is_instance_of('db successfully initializes', 'Db', 'getDao', $db_params, 'mysqli');
		Tap::is_instance_of('db successfully initializes', 'Db', 'getDao', $db_params, 'mysqli');
		Tap::is_instance_of('db successfully initializes', 'Db', 'getDao', $db_params, 'pdo');
	}
	
    //Truncate all tables; Useful for testing.
	function reInit() {
		$dao = $this->getDao();
			
		if (mysqli_connect_errno( $dao )) {
			throw new Exception ( "Failed to make connection: ".mysqli_connect_error() );
		}	
	
		mysqli_query( $dao, 'truncate table Classes; truncate table Students; truncate table Student_classes; truncate table Professors;' );	
		return array('status' => 'all tables truncated!');
	}

    //Takes a query and executes it. Returns information on rows affected, and a data json blob.
	function performQuery( $query ) {
		$dao = $this->getDao();

		if (mysqli_connect_errno( $dao )) {
			throw new Exception ( "Failed to make connection: ".mysqli_connect_error() );
		}	
	
		$result = mysqli_query( $dao, $query );
        
		if ($result === false) {
			//throw new Exception ('Null result from query: '. $query);
		} elseif ($result === TRUE) {
            $r_array['status'] = 'success';
        } else {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $rows[] = $row;
            }
            if ($dao->affected_rows) {
                $r_array['status'] = 'success';
            } else {
                $r_array['status'] = 'no_rows_affected';
            }
        }
        if (isset($rows)) {
            $r_array['data'] = $rows;
		}
		$r_array['rows_affected'] = $dao->affected_rows;
		
        mysqli_close($dao);
        
		return $r_array;
	}	
    
    //Given the relevant query information, build the actual mysql statement.
	function generateQuery( $verb, $actor, $relevant, $id_key)	{
        $id = $relevant[$id_key];
        $actor = ucfirst($actor);
        $where = $this->generateWhereString( $id_key, $id );
		switch ($verb) {
			default:
				throw new Exception( 'Unrecognized verb present. ('.$verb.')>> '.__class__ .'::'.__function__);
			case 'insert':
				$query = $this->queryInsert($actor, $relevant);
				break;
			case 'update':
				$query = $this->queryUpdate($actor, $relevant, $id, $where);
				break;
			case 'delete':
				$query = $this->queryDelete($actor, $id, $where);
				break;
			case 'select':
				$query = $this->querySelect($actor, $id, $where);
				break;
		}
		return $query;
	}
	
    //Generate the where filter; possibly contains multiple clauses, for the Student_classes table.
    //Acceptable arguments are the relevant table's primary key or 'all'
    function generateWhereString( $id_key, $id ) {
        if (is_scalar($id_key)) {
			if ($id == 'all') {
				return '(1=1)';
			}
            $where = $id_key.'='.$id;
        } else {
            $count = 0;
            foreach($id_key as $ikk => $ikv) {
                if ($count > 0) {
                    $where .= ' AND ';
                }
				if ($ikv == 'all') {
					$where .= '(1=1)';
				}
                $where = $id_key.'='.$ikv;
            }
        }
		return $where;
    }
    
    //Generate Select syntax
	function querySelect($table, $id, $where ) {
		return 'SELECT * FROM '.$table.' WHERE '.$where.' LIMIT '.$this->select_limit;
	}
	
    //Generate Delete syntax
	function queryDelete($table, $id, $where ) {
		return 'DELETE FROM '.$table.' WHERE '.$where;
	}
	
    //Generate Update syntax; also properly formats column names/inputs
	function queryUpdate($table, $relevant, $id, $where ) {
		$relevant_string = $this->convertRelevantForUpdate($relevant);
		return 'UPDATE '.$table.' SET '.$relevant_string.' WHERE '.$where;
	}
	
    //Generate Insert syntax
	function queryInsert($table, $relevant) {
		$substring = $this->convertRelevantForInsert($relevant);
		return 'INSERT INTO '.$table.' '.$substring;	
	}

	//Convert an associated array to a format like `field1="new-value1", field2="new-value2"`
	function convertRelevantForUpdate($relevant) {
		$relevant_string = '';
		$i = 0;
		foreach ($relevant as $rk => $rv) {
			$i++;
			if ($i > 1) {
				$relevant_string .= ', ';
			}
			$relevant_string .= strtolower($rk).'="'.strtolower($rv).'"';
		}
		return $relevant_string;
	}
	
	//Convert an associated array to a format like `(student_id, first_name, last_name) VALUES (712, 'Gregory', 'Foon')`
	function convertRelevantForInsert($relevant) {
		$r_keys = array();
		$r_vals = array();
		foreach ($relevant as $rk => $rv) {
			$r_keys[] = $rk;
			//Wrap strings into single quotes; leave ints.
            //Due to \Init::processUGC(), only alphanum is valid; no need to further escape/sanitize.
			if (ctype_digit($rv)) {
				$r_vals[] = $rv;
			} else {
				$r_vals[] = "'" . $rv . "'";
			}
		}
		$keys = '(' . implode(', ', $r_keys) . ')';
		$vals = '(' . implode(', ', $r_vals) . ')';
		
		$substring = $keys . ' VALUES ' . $vals;
		
		return $substring;
	}
}


$orm = new Tests_Orm();
$orm->test();