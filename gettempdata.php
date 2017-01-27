<?php

require_once('../db.php');

$result_array = DbGetTempCsvData();

print "time,device,value\n";
for($i = 0; $i < count($result_array); $i++) {
	print date("YmdHis",$result_array[$i]['values_time']) . "," . $result_array[$i]['devices_name'] . "," . $result_array[$i]['values_value'] . "\n";
}

?>
