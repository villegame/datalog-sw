<?php

function DbConnect () {

	$db_conn = pg_connect("host=localhost port=5432 dbname=temp_mon user=temp_mon_user password=temp_mon_user");
	return $db_conn;
}

function DbGetTempDeviceData() {

	$db_conn = DbConnect();
	$result = pg_query($db_conn, "select devices_id, devices_type, devices_name, devices_source, devices_sensor, devices_enabled, devices_screen, devices_screen_order from temp_mon_schema.devices where devices_sensor=1;");
	$result_array = pg_fetch_all($result);
	pg_close($db_conn);

	return $result_array;
}

function DbGetHumDeviceData() {

	$db_conn = DbConnect();
	$result = pg_query($db_conn, "select devices_id, devices_type, devices_name, devices_source, devices_sensor, devices_enabled, devices_screen, devices_screen_order from temp_mon_schema.devices where devices_sensor=2;");
	$result_array = pg_fetch_all($result);
	pg_close($db_conn);

	return $result_array;
}

// source: gpio pin or 1w id, name: user chosen, $sensor: 1 = 1-wire, 2 = dht, enabled 1/0, typeid hum/temp
function DbAddDevice($source, $name, $sensor, $enabled, $type) {

	$db_conn = DbConnect();
	$result = pg_prepare($db_conn, "insert_query", 'insert into temp_mon_schema.devices (devices_source, devices_name, devices_sensor, devices_enabled, devices_type) values ($1, $2, $3, $4, $5);');
	$result = pg_execute($db_conn, "insert_query", array($source, $name, $sensor, $enabled, $type));
	pg_close($db_conn);
}

function DbRenameDevice($id, $name) {
	$db_conn = DbConnect();
	$result = pg_prepare($db_conn, "update_query", 'update temp_mon_schema.devices set devices_name=$1 where devices_id=$2;');
	$result = pg_execute($db_conn, "update_query", array($name, $id));
	pg_close($db_conn);
}

function DbRemoveDevice($id) {

	$db_conn = DbConnect();
	$result = pg_prepare($db_conn, "delete_query", 'delete from temp_mon_schema.devices where devices_id=$1;');
	$result = pg_execute($db_conn, "delete_query", array($id));
	pg_close($db_conn);
}

function DbEnableDevice($id) {

	$db_conn = DbConnect();
	$result = pg_prepare($db_conn, "update_query", 'update temp_mon_schema.devices set devices_enabled=1 where devices_id=$1;');
	$result = pg_execute($db_conn, "update_query", array($id));
	pg_close($db_conn);
}

function DbDisableDevice($id) {
        $db_conn = DbConnect();
        $result = pg_prepare($db_conn, "update_query", 'update temp_mon_schema.devices set devices_enabled=0 where devices_id=$1;');
        $result = pg_execute($db_conn, "update_query", array($id));
        pg_close($db_conn);
}

function DbSetDeviceScreenOrder($id, $screen, $order) {
	$db_conn = DbConnect();
	$result = pg_prepare($db_conn, "update_query", 'update temp_mon_schema.devices set devices_screen=$1, devices_screen_order=$2 where devices_id=$3;');
	$result = pg_execute($db_conn, "update_query", array($screen, $order, $id));
	pg_close($db_conn);
}

function DbGetHumCsvData() {
	$time = time() - (24*60*60);

	$db_conn = DbConnect();
        $result = pg_query($db_conn, "select devices.devices_name, values.values_value, values.values_time from temp_mon_schema.values, temp_mon_schema.devices where values.devices_id in (select devices_id from temp_mon_schema.devices where devices_type=2) and devices.devices_id = values.devices_id and values.values_time > ".$time." order by values.values_time;");
        $result_array = pg_fetch_all($result);
        pg_close($db_conn);

        return $result_array;
}

function DbGetTempCsvData() {
	$time = time() - (24*60*60);

	$db_conn = DbConnect();
	$result = pg_query($db_conn, "select devices.devices_name, values.values_value, values.values_time from temp_mon_schema.values, temp_mon_schema.devices where values.devices_id in (select devices_id from temp_mon_schema.devices where devices_type=1) and devices.devices_id = values.devices_id and values.values_time > ".$time." order by values.values_time;");
        $result_array = pg_fetch_all($result);
        pg_close($db_conn);

        return $result_array;
}

?>
