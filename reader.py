#!/usr/bin/python

import psycopg2
import time
import os

# Translate BCM.Gpio nubmer to wiringPi pin
# Pin translation: http://wiringpi.com/pins/
# Board version:   http://elinux.org/RPi_HardwareHistory
def translatepin(pin):

	# Get revision of board
	pcbRev = 2
	revNum = 0
	f = open('/proc/cpuinfo', 'r')
	for line in f:
	        if line.split(" ")[0].startswith('Revision'):
	                revNum = line.split(":")[1].strip()[-4:]

	if revNum == "0002" or revNum == "0003" or revNum == "0010" or revNum == "0011" or revNum == "0014" or revNum == "1040" or revNum == "20a0":
		pcbRev = 1

	if revNum == 0:
		print "Could not get revision number of board..."
		quit()

	# Get translated pin numbers according to board pcb revision
	translated=[]
	if pcbRev == 2:
	        translated = [-1,-1,8,9,7,-1,-1,11,10,13,12,14,-1,-1,15,16,-1,0,1,-1,-1,-1,3,4,5,6,-1,2,17,18,19,20]
	if pcbRev == 1:
	        translated = [8,9,-1,-1,7,-1,-1,11,10,13,12,14,-1,-1,15,16,-1,0,1,-1,-1,2,3,4,5,6,-1,-1,17,18,19,20]

        return translated[pin]

# Arrays for devices to read
owDevices=[]
dhtDevices=[]

# Get device data from database
try:
	# Connection
	conn = psycopg2.connect("dbname='temp_mon' user='temp_mon_user' host='localhost' password='password'")
	# Cursor for performing operations
	cur = conn.cursor()

	# Run query for 1-w devices
	cur.execute("SELECT devices_id, devices_source, devices_type, devices_sensor FROM temp_mon_schema.devices where devices_enabled=1 and devices_sensor=1;");
	for value in cur:
		owDevices.append(value)

	# Run query for dht-devices
	cur.execute("SELECT devices_id, devices_source, devices_type, devices_sensor FROM temp_mon_schema.devices where devices_enabled=1 and devices_sensor=2 order by devices_source;");
	for value in cur:
		dhtDevices.append(value)

	cur.close()
	conn.close()
except:
	print "Cannot get device data from database"

# Get values from devices

# 1-W:

# List device id and command to read data from it
# list [devices_id, command, value]
owCommands=[]
for device in owDevices:
	command = "timeout 6 cat /sys/devices/w1_bus_master1/{0}/w1_slave".format(device[1])
	path = "/sys/devices/w1_bus_master1/{0}/w1_slave".format(device[1])
	if os.path.exists(path):
		value = float(os.popen(command).read().strip().split("=")[2])/1000
		if isinstance(value, float):
			if value != 85.00: 
				# Do not add 85.00, it is a reset value
				owCommands.append([device[0],command,value])

# DHT:

# Determine which pins to read dht-device data from
# list [devices_source, command, read temp, read hum, temp val, hum val, temp devices_id, hum devices_id]
dhtCommands=[]
for device in dhtDevices:
	exists = False
	for edevice in dhtCommands:
		if edevice[0] == device[1]:
			if device[2] == 1:
				edevice[2] = True
				edevice[6] = device[0]
			if device[2] == 2:
				edevice[3] = True
				edevice[7] = device[0]
			exists = True
	if exists == False:
		temp = False
		tid = 0
		hum = False
		hid = 0
		command = "timeout 6 /home/pi/dht {0}".format(translatepin(int(device[1])))
		if device[2] == 1:
			temp = True
			tid = device[0]
		if device[2] == 2:
			hum = True
			hid = device[0]
		dhtCommands.append([device[1], command, temp, hum, 0.0, 0.0, tid, hid])

for command in dhtCommands:
	dhtResult = os.popen(command[1]).read().strip().split(",")
	if len(dhtResult) == 2:
		# Program returned 2 values separated by comma: humidity and temperature
		command[4] = float(dhtResult[1])
		command[5] = float(dhtResult[0])
	else:
		# Did not get proper output, do not insert into database this time
		command[2] = False
		command[3] = False

# Write results into database
try:
	# Timestamp
	timestamp = int(time.time())

        # Connection
        conn = psycopg2.connect("dbname='temp_mon' user='temp_mon_user' host='localhost' password='password'")
        # Cursor for performing operations
        cur = conn.cursor()

        for command in dhtCommands:
		if command[2] == True:
			query = "insert into temp_mon_schema.values (devices_id, values_value, values_time) values({0}, {1}, {2});".format(command[6], command[4], timestamp)
			cur.execute(query)
		if command[3] == True:
			query = "insert into temp_mon_schema.values (devices_id, values_value, values_time) values({0}, {1}, {2});".format(command[7], command[5], timestamp)
			cur.execute(query)

	for command in owCommands:
		query = "insert into temp_mon_schema.values (devices_id, values_value, values_time) values({0}, {1}, {2});".format(command[0], command[2], timestamp)
		cur.execute(query)

	# Commit changes
	conn.commit()

	# Close cursor and connection
        cur.close()
        conn.close()
except:
        print "Cannot insert data to database"

