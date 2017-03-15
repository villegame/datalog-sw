<?php

$temp_min = "null";
$temp_max = "null";
$hum_min = "null";
$hum_max = "null";

if(isset($_POST['btnReloadCharts'])) {

	if(!$_POST['tempMinAuto'] && is_numeric($_POST['tempMinVal'])) {
		$temp_min = $_POST['tempMinVal'];
	}

	if(!$_POST['tempMaxAuto'] && is_numeric($_POST['tempMaxVal'])) {
		$temp_max = $_POST['tempMaxVal'];
	}

	if(!$_POST['humMinAuto'] && is_numeric($_POST['humMinVal'])) {
                $hum_min = $_POST['humMinVal'];
        }

        if(!$_POST['humMaxAuto'] && is_numeric($_POST['humMaxVal'])) {
                $hum_max = $_POST['humMaxVal'];
        }

}

?>


<!DOCTYPE html>
<html>
<head>
<title> Charts </title>
<meta charset="utf-8">
<style> /* set the CSS */

body { font: 12px Arial;}

path {
    stroke: steelblue;
    stroke-width: 2;
    fill: none;
}

.axis path,
.axis line {
    fill: none;
    stroke: grey;
    stroke-width: 1;
    shape-rendering: crispEdges;
}

.legend {
    font-size: 16px;
    font-weight: bold;
    text-anchor: middle;
}

</style>


<!-- load the d3.js library -->
<script src="d3.v3.min.js"></script>
<script src="chart.js"></script>

</head>
<body>

<form method="post">

<table>
<tr>
<td>

<table>
<tr>
<td>

Min:
<input type="text" size="1" name="tempMinVal" value="
<?php
if("null" == $temp_min) echo "";
else echo $temp_min;
?>
" />
<br />
Auto: <input type="checkbox" name="tempMinAuto" value="auto" 
<?php
if("null" == $temp_min) echo 'checked="checked" ';
?>
/>
<br />
Max:
<input type="text" size="1" name="tempMaxVal" value="
<?php
if("null" == $temp_max) echo "";
else echo $temp_max;
?>
"
/>
<br />
Auto: <input type="checkbox" name="tempMaxAuto" value="auto" 
<?php
if("null" == $temp_max) echo 'checked="checked" ';
?>
/>


</td>
<td>
<div id="temperature"></div>
</td>
</tr>
</table>

</td>
</tr>
<tr>
<td>

<table>
<tr>
<td>

Min:
<input type="text" size="1" name="humMinVal" value="
<?php
if("null" == $hum_min) echo "";
else echo $hum_min;
?>
" />
<br />
Auto: <input type="checkbox" name="humMinAuto" value="auto" 
<?php
if("null" == $hum_min) echo 'checked="checked" ';
?>
/>
<br />
Max:
<input type="text" size="1" name="humMaxVal" value="
<?php
if("null" == $hum_max) echo "";
else echo $hum_max;
?>
" />
<br />
Auto: <input type="checkbox" name="humMaxAuto" value="auto" 
<?php
if("null" == $hum_max) echo 'checked="checked" ';
?>
/>

</td>
<td>
<div id="humidity"></div>
</td>
</tr>
</table>

</td>
</tr>
<tr>
<td>

<input type="submit" name="btnReloadCharts" value="RELOAD" />

</td>
<td></td>
</tr>
</table>

</form>

<script type="text/javascript">

LineChart("temperature", 
<?php echo $temp_min; ?>, 
<?php echo $temp_max; ?>);
LineChart("humidity", 
<?php echo $hum_min; ?>, 
<?php echo $hum_max; ?>);

</script>

</body>
</html>


