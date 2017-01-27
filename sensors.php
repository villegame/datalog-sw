<?php
	require_once('../db.php');


	if(isset($_POST['btnAddDevice'])) {
		DbAddDevice($_POST['source'], $_POST['name'], $_POST['sensor'], $_POST['enabled'], $_POST['type']);
	}

	if(isset($_POST['btnRenameDevice'])) {
		DbRenameDevice($_POST['id'], $_POST['name']);
	}

	if(isset($_POST['btnRemoveDevice'])) {
		DbRemoveDevice($_POST['id']);
	}

	if(isset($_POST['btnEnableDevice'])) {
		DbEnableDevice($_POST['id']);

	}
	if(isset($_POST['btnDisableDevice'])) {
		DbDisableDevice($_POST['id']);
	}
?>

<html>
<head>
<title> Device management </title>
</head>
<body>
<script>

function updateDeviceData() {
	document.getElementById("devicedatastate").innerHTML="Reading...";
	var devicedata = loadXMLDoc("tester.php");
	document.getElementById("devicedata").innerHTML=devicedata; 
	document.getElementById("devicedatastate").innerHTML="Ready";
}

function loadXMLDoc(theURL) {
	var returnData = "";
	if (window.XMLHttpRequest)
        {// code for IE7+, Firefox, Chrome, Opera, Safari, SeaMonkey
            xmlhttp=new XMLHttpRequest();
        }
        else
        {// code for IE6, IE5
            xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
        }
        xmlhttp.onreadystatechange=function()
        {
            if (xmlhttp.readyState==4 && xmlhttp.status==200)
            {
               // alert(xmlhttp.responseText);
		returnData=xmlhttp.responseText;
            }
        }
        xmlhttp.open("GET", theURL, false);
        xmlhttp.send();

	return returnData;
	
}

</script>

<?php

// 1-Wire temperature devices

$local_temp_devices = array();
$db_temp_device_data = array();

$local_temp_devices_tmp = GetLocalTempDevices();
if($local_temp_devices_tmp) $local_temp_devices = $local_temp_devices_tmp;

$db_temp_device_data_tmp = DbGetTempDeviceData();
if($db_temp_device_data_tmp) $db_temp_device_data = $db_temp_device_data_tmp;

echo "<h1>1-wire devices</h1>";

if(count($local_temp_devices) > 0) {

	echo "<b>Following devices were found on system:</b>";
	echo "<table>";
	echo "<tr><td><b>Name</b></td><td><b>Status</b></td><td><b>State</b></td><td><b>Action</b></td></tr>";

	for($i = 0; $i < count($local_temp_devices); $i++) {

		$exists = false;
		$db_device_name = "";
		$db_device_id;
		$db_device_enabled;
		$device_state = "";
		$device_status = "Not in database";

		for($j = 0; $j < count($db_temp_device_data); $j++) {
			if($local_temp_devices[$i] == $db_temp_device_data[$j]['devices_source']) {
				$exists = true;
				$db_device_id = $db_temp_device_data[$j]['devices_id'];
				$db_device_enabled = $db_temp_device_data[$j]['devices_enabled'];
				$db_device_name = $db_temp_device_data[$j]['devices_name'];
				break;
			}
		}


		$buttons = "";

		if($exists) {

			$device_status = "In database as '".$db_device_name."'";
			//$buttons = "REMOVE, DISABLE";
			$buttons = "<form method='post'>";
		        $buttons .= "<input type='hidden' name='id' value='".$db_device_id."' />";
			$buttons .= "Rename to: <input type='text' name='name' />";
			$buttons .= "<input type='submit' name='btnRenameDevice' value='RENAME' />";
		        $buttons .= "<input type='submit' name='btnRemoveDevice' value='REMOVE' />";
			if($db_device_enabled) {
				$device_state = "Enabled";
			        $buttons .= "<input type='submit' name='btnDisableDevice' value='DISABLE' />";
			}
			else {
				$device_state = "Disabled";
				$buttons .= "<input type='submit' name='btnEnableDevice' value='ENABLE' />";
			}
		        $buttons .= "</form>";

		}
		else {
			$buttons = "<form method='post'>";
			$buttons .= "<input type='hidden' name='source' value='".$local_temp_devices[$i]."' />";
			$buttons .= "Add as: <input type='text' name='name' />";
			$buttons .= "<input type='hidden' name='sensor' value='1' />";
			$buttons .= "<input type='hidden' name='enabled' value='1' />";
			$buttons .= "<input type='hidden' name='type' value='1' />";
			$buttons .= "<input type='submit' name='btnAddDevice' value='ADD' />";
			$buttons .= "</form>";
		}

		echo "<tr>";
		echo "<td>".$local_temp_devices[$i]."</td>";
		echo "<td>".$device_status."</td>";
		echo "<td>";
		if($exists) echo $device_state;
		echo "</td>";
		echo "<td>".$buttons."</td>";
		echo "</tr>";
	}

	echo "</table>";

}

$devices_only_in_db = array();

for($i=0; $i<count($db_temp_device_data); $i++) {
	$exists = false;
	for($j = 0; $j < count($local_temp_devices); $j++) {
		if($db_temp_device_data[$i]['devices_source'] == $local_temp_devices[$j]) {
			$exists = true;
			break;
		}
	}
	if(!$exists) {
		array_push($devices_only_in_db, $db_temp_device_data[$i]);
	}
}

if(count($devices_only_in_db) > 0) {

	echo "<b>Following devices were found in database that are not connected to system physically:</b>";
	echo "<table>";
	echo "<tr><td><b>Name</b></td><td><b>Status</b></td><td><b>Action</b></td></tr>";


	for($i = 0; $i < count($devices_only_in_db); $i++) {

		$device_status = "In database as '".$devices_only_in_db[$i]['devices_name']."'";
		$buttons = "<form method='post'>";
		$buttons .= "<input type='hidden' name='id' value='".$devices_only_in_db[$i]['devices_id']."' />";
		$buttons .= "<input type='submit' name='btnRemoveDevice' value='REMOVE' />";
		$buttons .= "</form>";
		echo "<tr>";
		echo "<td>".$devices_only_in_db[$i]['devices_source']."</td>";
		echo "<td>".$device_status."</td>";
		echo "<td>".$buttons."</td>";
		echo "</tr>";
	}

	echo "</table>";

}

// DHT humidity/temperature devices

$local_hum_devices = GetLocalHumDevices();
$db_hum_device_data = array();
$db_hum_devices_tmp = DbGetHumDeviceData();
if($db_hum_devices_tmp) $db_hum_device_data = $db_hum_devices_tmp;

echo "<h1>DHT-22 devices</h1>";

echo "<table>";
echo "<tr><td><b>Pin</b></td><td><b>Type</b></td><td><b>Status</b></td><td><b>State</b></td><td><b>Action</b></td></tr>";

for($i=0; $i < count($local_hum_devices); $i++) {

	$exists = false;
	$db_device_name = "";
	$db_device_id;
	$db_device_enabled;
	$device_state = "";

	for($j=0; $j < count($db_hum_device_data); $j++) {
		if($db_hum_device_data[$j]['devices_source'] == $local_hum_devices[$i][0]
		&& $db_hum_device_data[$j]['devices_type'] == $local_hum_devices[$i][1]) {
			$exists = true;
			$db_device_name = $db_hum_device_data[$j]['devices_name'];
			$db_device_id  = $db_hum_device_data[$j]['devices_id'];
			$db_device_enabled = $db_hum_device_data[$j]['devices_enabled'];
			break;
		}
	}

	if($exists) {
		$buttons = "<form method='post'>";
		$buttons .= "<input type='hidden' name='id' value='".$db_device_id."' />";
		$buttons .= "Rename to: <input type='text' name='name' />";
		$buttons .= "<input type='submit' name='btnRenameDevice' value='RENAME' />";
		$buttons .= "<input type='submit' name='btnRemoveDevice' value='REMOVE' />";
		if($db_device_enabled) {
			$device_state = "Enabled";
		        $buttons .= "<input type='submit' name='btnDisableDevice' value='DISABLE' />";
		}
		else {
			$device_state = "Disabled";
			$buttons .= "<input type='submit' name='btnEnableDevice' value='ENABLE' />";
		}
		$buttons .= "</form>";
	}		
	else {
		$buttons = "<form method='post'>";
		$buttons .= "<input type='hidden' name='source' value='".$local_hum_devices[$i][0]."' />";
		$buttons .= "Add as: <input type='text' name='name' />";
		$buttons .= "<input type='hidden' name='sensor' value='2' />";
		$buttons .= "<input type='hidden' name='enabled' value='1' />";
		$buttons .= "<input type='hidden' name='type' value='".$local_hum_devices[$i][1]."' />";
		$buttons .= "<input type='submit' name='btnAddDevice' value='ADD' />";
		$buttons .= "</form>";
	}

	echo "<tr>";
	echo "<td>".$local_hum_devices[$i][0]."</td>";
	echo "<td>";
	if($local_hum_devices[$i][1] == 1) echo "Temperature";
	if($local_hum_devices[$i][1] == 2) echo "Humidity";
	echo "</td>";
	echo "<td>";
	if($exists) echo "Exists in database as '".$db_device_name."'";
	else echo "Not in database.";
	echo "</td>";
	echo "<td>";
	echo $device_state;
	echo "</td>";
	echo "<td>";
	echo $buttons;
	echo "</td>";
	echo "</tr>";
}

echo "</table>";

?>

<h1>Device value tester</h1>

<p>Read currently connected device values</p>
<button onclick="updateDeviceData()">READ</button>
<br /><br />
<div id="devicedatastate"></div>
<div id="devicedata"></div>

</body>
</html>

<?php

function GetLocalTempDevices () {

	// Read 1820 devices from system into array
	$tdevices = array();
	$dir = "/sys/devices/w1_bus_master1";
	// Open a directory, and read its contents
	if (is_dir($dir)){
	  if ($dh = opendir($dir)){
	    while (($subdir = readdir($dh)) !== false){
	        if(is_dir($dir."/".$subdir))
	            if(is_file($dir."/".$subdir."/w1_slave"))
	                array_push($tdevices, $subdir);
	    }
	    closedir($dh);
	  }
	}
	return $tdevices;
}

function GetLocalHumDevices () {
	$hdevices = array();
	array_push($hdevices, array(14, 1)); // temp
	array_push($hdevices, array(14, 2)); // hum
	array_push($hdevices, array(15, 1)); // temp
	array_push($hdevices, array(15, 2)); // hum
	return $hdevices;
}

?>
