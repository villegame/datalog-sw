<html>
<head>
<title>DHT22 tester</title>
</head>

<body>

<?php


echo "<table border=1>";
echo "<tr><td><b>Sensor</b></td><td><b>Value</b></td></tr>";

$dir = "/sys/devices/w1_bus_master1";
// Open a directory, and read its contents
if (is_dir($dir)){
	if ($dh = opendir($dir)){
		while (($subdir = readdir($dh)) !== false){
			if(is_dir($dir."/".$subdir))
				if(is_file($dir."/".$subdir."/w1_slave"))
					echo "<tr><td>" . $subdir . "</td><td>" . shell_exec("cat ".$dir."/".$subdir."/w1_slave | grep t= | cut -f2 -d= | awk '{print $1/1000}'") . "</td></tr>";// | awk '{printf(\"%.2f\n\", $1}'") . "<br>";
		}
	closedir($dh);
  	}
}


$DHT14 =  shell_exec("sudo /usr/local/bin/sudo_dht_wrapper.sh 15");
$DHT15 =  shell_exec("sudo /usr/local/bin/sudo_dht_wrapper.sh 16");

$values = explode(",",$DHT14);
$hum = "";
$temp = "";
if(count($values) != 2) {
	$hum = "No data";
	$temp = "No data";
}
else {
	$hum = $values[0];
	$temp = $values[1];
}
echo "<tr><td>DHT on pin 14 Hum</td><td>".$hum."</td></tr>";
echo "<tr><td>DHT on pin 14 Tmp</td><td>".$temp."</td></tr>";


$values = explode(",",$DHT15);
$hum = "";
$temp = "";
if(count($values) != 2) {
        $hum = "No data";
        $temp = "No data";
}
else {
        $hum = $values[0];
        $temp = $values[1];
}
echo "<tr><td>DHT on pin 15 Hum</td><td>".$hum."</td></tr>";
echo "<tr><td>DHT on pin 15 Tmp</td><td>".$temp."</td></tr>";

echo "</table>";

?>

</body>
</html>
