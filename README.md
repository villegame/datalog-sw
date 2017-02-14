# datalog-sw

Datalogger software related to a datalogger project [described here] (https://villegame.wordpress.com/projects/data-monitoring/portable-temperature-and-humidity-measuring-application-project/).

* /var/www/

  * db.php  

* /var/www/html/

  * chart.js  
  * charts.html  
  * gethumdata.php  
  * gettempdata.php  
  * index.html
  * sensors.php  
  * tester.php

* /home/pi/

  * dht.c  
  * reader.py  
  * poweroff.py

* Other

  * psql.sql

### db.php
Database-related PHP functions.

### chart.js
D3 chart based on example here: http://www.d3noob.org/2014/07/d3js-multi-line-graph-with-automatic.html.

### charts.html
Page containing charts.

### gethumdata.php
Returns humidity data from database in csv -format.

### gettempdata.php
Returns temperature data from database in csv -format.

### index.html
Frontpage.

### sensors.php
Sensor manager page.

### tester.php
Sensor tester. Reads all temperature sensors and DHT22-sensors from pins 14 and 15.

### dht.c
C-program to read data from DHT -sensors. Based on example here: http://www.uugear.com/portfolio/read-dht1122-temperature-humidity-sensor-from-raspberry-pi/

### reader.py
Python script to read sensor data and update it to database.

### poweroff.py
Simple script for lighting up a led to indicate system is online and to listen a button for shutdown command.

### psql.sql
Contains sql scripts for creting a schema and a few tables.
