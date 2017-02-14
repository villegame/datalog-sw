<?php
	require_once('../db.php');

	$info_msg = "";

	if(isset($_POST['btnAddDevice'])) {
		DbAddDevice($_POST['source'], $_POST['name'], $_POST['sensor'], $_POST['enabled'], $_POST['type']);
		$info_msg .= "Added device from ".$_POST['source']." with name:".$_POST['name'].".<br />";
	}
	if(isset($_POST['btnRenameDevice'])) {
		DbRenameDevice($_POST['id'], $_POST['name']);
		$info_msg .= "Device ".$_POST['id']." renamed to ".$_POST['name'].".<br />";
	}
	if(isset($_POST['btnRemoveDevice'])) {
		DbRemoveDevice($_POST['id']);
		$info_msg .= "Device ".$_POST['id']." removed.<br />";
	}
	if(isset($_POST['btnEnableDevice'])) {
		DbEnableDevice($_POST['id']);
		$info_msg .= "Device ".$_POST['id']." enabled.<br />";
	}
	if(isset($_POST['btnDisableDevice'])) {
		DbDisableDevice($_POST['id']);
		$info_msg .= "Device ".$_POST['id']." disabled.<br />";
	}
	if(isset($_POST['btnSetDeviceScreenOrder'])) {
		if(isset($_POST['screen']) && isset($_POST['order'])) {
			if(is_numeric($_POST['screen']) && is_numeric($_POST['order'])) {
				DbSetDeviceScreenOrder($_POST['id'], $_POST['screen'], $_POST['order']);
				$info_msg .= "Updated device screen order (id:".$_POST['id'].", screen:".$_POST['screen'].", order:".$_POST['order'].").<br />";
			}
			else {
				$info_msg .= "Screen and order values need to be numeric.<br />";
			}
		}
		else {
			$info_msg .= "Screen and order values are both needed.<br />";
		}
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

// Check errors

if($info_msg != "") {
	echo "<center><b>".$info_msg."</b></center>";
}

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
	echo "<tr><td><b>Name</b></td><td><b>Status</b></td><td><b>State</b></td><td><b>On LCD display</b></td><td><b>Action</b></td></tr>";

	for($i = 0; $i < count($local_temp_devices); $i++) {

		$exists = false;
		$db_device_name = "";
		$db_device_id;
		$db_device_enabled;
		$db_device_screen;
		$db_device_screen_order;
		$device_state = "";
		$device_status = "Not in database";

		for($j = 0; $j < count($db_temp_device_data); $j++) {
			if($local_temp_devices[$i] == $db_temp_device_data[$j]['devices_source']) {
				$exists = true;
				$db_device_id 		= 	$db_temp_device_data[$j]['devices_id'];
				$db_device_enabled 	= 	$db_temp_device_data[$j]['devices_enabled'];
				$db_device_name 	= 	$db_temp_device_data[$j]['devices_name'];
				$db_device_screen 	=	$db_temp_device_data[$j]['devices_screen'];
				$db_device_screen_order	=	$db_temp_device_data[$j]['devices_screen_order'];
				break;
			}
		}


		$buttons = "";
		$info_screen = "";

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

			$info_screen = "<form method='post'>";
			$info_screen .= "<input type='hidden' name='id' value='".$db_device_id."' />";
			$info_screen .= "Screen:<input type='text' name='screen' value='".$db_device_screen."' />";
			$info_screen .= "Line:<input type='text' name='order' value='".$db_device_screen_order."' />";
			$info_screen .= "<input type='submit' name='btnSetDeviceScreenOrder' value='SET' />";
			$info_screen .= "</form>";

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
		echo "<td>".$info_screen."</td>";
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
echo "<tr><td><b>Pin</b></td><td><b>Type</b></td><td><b>Status</b></td><td><b>State</b></td><td><b>On LCD display</b></td><td><b>Action</b></td></tr>";

for($i=0; $i < count($local_hum_devices); $i++) {

	$exists = false;
	$db_device_name = "";
	$db_device_id;
	$db_device_enabled;
	$db_device_screen;
	$db_device_Screen_order;
	$device_state = "";

	for($j=0; $j < count($db_hum_device_data); $j++) {
		if($db_hum_device_data[$j]['devices_source'] == $local_hum_devices[$i][0]
		&& $db_hum_device_data[$j]['devices_type'] == $local_hum_devices[$i][1]) {
			$exists = true;
			$db_device_name 	= $db_hum_device_data[$j]['devices_name'];
			$db_device_id  		= $db_hum_device_data[$j]['devices_id'];
			$db_device_enabled 	= $db_hum_device_data[$j]['devices_enabled'];
			$db_device_screen 	= $db_hum_device_data[$j]['devices_screen'];
			$db_device_screen_order	= $db_hum_device_data[$j]['devices_screen_order'];
			break;
		}
	}

	$buttons = "";
	$info_screen = "";

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

		$info_screen = "<form method='post'>";
                $info_screen .= "<input type='hidden' name='id' value='".$db_device_id."' />";
                $info_screen .= "Screen:<input type='text' name='screen' value='".$db_device_screen."' />";
                $info_screen .= "Line:<input type='text' name='order' value='".$db_device_screen_order."' />";
                $info_screen .= "<input type='submit' name='btnSetDeviceScreenOrder' value='SET' />";
                $info_screen .= "</form>";
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
	echo "<td>".$device_state."</td>";
	echo "<td>".$info_screen."</td>";
	echo "<td>".$buttons."</td>";
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
