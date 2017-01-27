# datalog-sw
Datalogger software

/var/www/
	db.php  

/var/www/html/
	chart.js  
	charts.html  
	gethumdata.php  
	gettempdata.php  
	index.php  
	sensors.php  
	tester.php

/home/pi/
	dht.c  
	reader.py  

db.php
	Database-related PHP functions.

chart.js
	D3 chart based on example here: http://www.d3noob.org/2014/07/d3js-multi-line-graph-with-automatic.html.

charts.html
	Page containing charts.

gethumdata.php
	Returns humidity data from database in csv -format.

gettempdata.php
	Returns temperature data from database in csv -format.

index.php
	Frontpage.

sensors.php
	Sensor manager page.

tester.php
	Sensor tester. Reads all temperature sensors and DHT22-sensors from pins 14 and 15.
