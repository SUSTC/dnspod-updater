<?php

define('IN_SUSTC', true);
define('SC_ROOT', substr(__FILE__, 0, -10)); //strlen('domain.php')

require_once SC_ROOT.'config/config.php';
require_once SC_ROOT.'class/dnspod.php';

$dnspod = new dnspod();
$response = array();

if ($_GET['action'] == 'ip') {
  $response = $dnspod->call_api('Record.Modify',
    array('domain_id' => $_POST['domain_id'],
      'record_id' => $_POST['record_id'],
      'sub_domain' => $_POST['sub_domain'],
      'record_type' => 'A',
      'record_line' => '默认',
      'value' => $_POST['value'],
      //'mx' => $_POST['mx'],
      //'ttl' => $_POST['ttl'],
    )
  );
}

$text = json_encode($response, JSON_UNESCAPED_UNICODE);
exit($text);

?>