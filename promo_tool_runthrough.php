<?PHP
include 'tap.php';
include '../common/orm.php';

class Promo_Tool_Runthrough extends Tap {
	function test_write_interaction() {
		Tap::set_base_url_path('10.10.10.111/sean/index.php/');
		
		$params = array(
			'uin' => 11235,
			'heroid' => 1212,
			'delegateid' => 7,
			'promoid' => 7,
			'useraction' => 1,
			'clientts' => time();
		}
		
		$expected = 'true';
		
		Tap::web_ok('crm/writeInteractionLog', $params, $expected);
		
		return;
	}
}


$orm = new Promo_Tool_Runthrough();
$orm->test();
		