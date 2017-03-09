#!/bin/bash
 
cat /sys/bus/w1/devices/28-8000002894f1/w1_slave | grep t= | cut -f2 -d= | awk '{print $1/1000}' | awk '{printf("%.2f\n", $1)}' > /tmp/t1.txt
cat /sys/bus/w1/devices/28-80000028a939/w1_slave | grep t= | cut -f2 -d= | awk '{print $1/1000}' | awk '{printf("%.2f\n", $1)}' > /tmp/t2.txt
 
DHT1=$(/home/pi/Adafruit_Python_DHT/examples/AdafruitDHT.py 22 15)
DHT2=$(/home/pi/Adafruit_Python_DHT/examples/AdafruitDHT.py 22 14)
 
H1=$(echo $DHT1 | cut -f3 -d= | cut -f1 -d%)
HT1=$(echo $DHT1 | cut -f2 -d= | cut -f1 -d*)
 
H2=$(echo $DHT2 | cut -f3 -d= | cut -f1 -d%)
HT2=$(echo $DHT2 | cut -f2 -d= | cut -f1 -d*)
 
echo $H1 > /tmp/h1.txt
echo $HT1 > /tmp/ht1.txt
echo $H2 > /tmp/h2.txt
echo $HT2 > /tmp/ht2.txt
