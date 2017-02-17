#!/usr/bin/python
#
# THIS IS MODIFIED VERSION OF THE ORIGINAL SCRIPT
#
# ORIGINAL CAN BE FOUND HERE: 
# http://www.raspberrypi-spy.co.uk/2012/08/16x2-lcd-module-control-with-backlight-switch/
#
#--------------------------------------
#    ___  ___  _ ____
#   / _ \/ _ \(_) __/__  __ __
#  / , _/ ___/ /\ \/ _ \/ // /
# /_/|_/_/  /_/___/ .__/\_, /
#                /_/   /___/
#
#  lcd_16x2.py
#  16x2 LCD Test Script with
#  backlight control and text justification
#
# Author : Matt Hawkins
# Date   : 06/04/2015
#
# http://www.raspberrypi-spy.co.uk/
#
# Copyright 2015 Matt Hawkins
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#--------------------------------------
#
# The wiring for the LCD is as follows:
# 1 : GND
# 2 : 5V
# 3 : Contrast (0-5V)*
# 4 : RS (Register Select)
# 5 : R/W (Read Write)       - GROUND THIS PIN
# 6 : Enable or Strobe
# 7 : Data Bit 0             - NOT USED
# 8 : Data Bit 1             - NOT USED
# 9 : Data Bit 2             - NOT USED
# 10: Data Bit 3             - NOT USED
# 11: Data Bit 4
# 12: Data Bit 5
# 13: Data Bit 6
# 14: Data Bit 7
# 15: LCD Backlight +5V**
# 16: LCD Backlight GND

import imp
import RPi.GPIO as GPIO
import time
import os
#import sys # For debugging

# Import postgres library if such is installed
DB_LIB_FOUND = False
try:
  imp.find_module('psycopg2')
  DB_LIB_FOUND = True
except ImportError:
  pass
if DB_LIB_FOUND == True:	
  import psycopg2

# Define GPIO to LCD mapping
LCD_RS = 10
LCD_E  = 9
LCD_D4 = 25
LCD_D5 = 11
LCD_D6 = 8
LCD_D7 = 7
LED_ON = 2

BUTTON = 3

# Define some device constants
LCD_WIDTH = 16    # Maximum characters per line
LCD_CHR = True
LCD_CMD = False

LCD_LINE_1 = 0x80 # LCD RAM address for the 1st line
LCD_LINE_2 = 0xC0 # LCD RAM address for the 2nd line

# Timing constants
E_PULSE = 0.0005
E_DELAY = 0.0005

# Device name length limit on lcd line
DEVICE_NAME_MAX_LEN = 10

# Displayvalues triggering monitoring
lastpress = 0.1
# Triggering limit in seconds
triggerlimit = 16

def display_values(channel):

  # This is to get rid of multiple callbacks from one button press
  # Limit is set to 16 seconds before function can be processed again
  global lastpress
  if(lastpress + triggerlimit > time.time()):
    return
  else:
    lastpress = time.time()

  # To determine if we read values from /tmp/files
  use_files = False

  if DB_LIB_FOUND == True:
    try:
      #Try DB connection
      read_from_db()
    except:
      #No DB available, or error in data handling, try reading from files
      use_files = True
      # Debug: needs import sys
      #for info in sys.exc_info():
      #	print info
  else:
    use_files = True

  if use_files == True:
    read_from_files()

def read_from_db():

#  print "FROM DB"
  
  # Read measure data from database
  conn = psycopg2.connect("dbname='temp_mon' user='temp_mon_user' host='localhost' password='temp_mon_user'")
  cur = conn.cursor()

  # Get latest time data was logged in
  maxtime = 0

  cur.execute("SELECT MAX(values_time) FROM temp_mon_schema.values;")
  for value in cur:
    maxtime = value[0]

  # Get devices to display by set screen and screen order
  # Array of devices, each element will be an array [id,name,screen,order,value]
  devices = []  
  cur.execute("SELECT devices_id, devices_name, devices_screen, devices_screen_order FROM temp_mon_schema.devices WHERE devices_screen > 0 AND devices_screen_order > 0 AND devices_screen_order < 3 ORDER BY devices_screen, devices_screen_order;")
  for data in cur:
    device = [data[0], data[1], data[2], data[3], 0.0]
    devices.append(device)

  # The max value of screen numbers used
  last_screen = 0
  cur.execute("SELECT MAX(devices_screen) FROM temp_mon_schema.devices where devices_screen > 0 AND devices_screen_order > 0 AND devices_screen_order < 3;");
  for data in cur:
    last_screen = int(data[0])

  i = 0
  for device in devices:
    query = "SELECT values_value FROM temp_mon_schema.values WHERE devices_id = {0} AND values_time = {1};" . format(device[0], maxtime)
    cur.execute(query)
    for data in cur:
      devices[i][4] = data[0]
    i = i+1


  # Generate screen texts per screen [line1, line2]
  screen_texts = []

  screen = 1
  while screen <= last_screen:
    line_1 = ""
    line_2 = ""
    for device in devices:
      if device[2] == screen:
        line = "{0}:{1}" . format(device_name_lcd_format(device[1]), device[4])
        if device[3] == 1:
          line_1 = line
        if device[3] == 2:
          line_2 = line
    if line_1 != "" or line_2 != "":
      if line_1 == '':
        line_1 = "                "
      if line_2 == "":
        line_2 = "                "
      screen_texts.append([line_1, line_2])
    screen = screen + 1

  # Set triggerlimit to last screen cycle time plus one second
  global triggerlimit
  triggerlimit = len(screen_texts) * 5 + 1

  # Turn backlight on
  lcd_backlight(True)

  for line in screen_texts:
    lcd_string(line[0],LCD_LINE_1,1)
    lcd_string(line[1],LCD_LINE_2,1)
    time.sleep(5)
  
  # Turn backlight off
  lcd_backlight(False)

  lcd_string("                ", LCD_LINE_1,1)
  lcd_string("                ", LCD_LINE_2,1)


def device_name_lcd_format(name):

  if len(name) == DEVICE_NAME_MAX_LEN:
    return name
  if len(name) > DEVICE_NAME_MAX_LEN:
    return name[:DEVICE_NAME_MAX_LEN]
  else:
    fillings = DEVICE_NAME_MAX_LEN - len(name)
    while (fillings > 0):
      name += " "
      fillings = fillings - 1
    return name

def read_from_files():

#  print "FROM FILES"

  # Read measure data from files
  t1 = read_from_path("/tmp/t1.txt")
  t2 = read_from_path("/tmp/t2.txt")
  ht1 = read_from_path("/tmp/ht1.txt")
  ht2 = read_from_path("/tmp/ht2.txt")
  h1 = read_from_path("/tmp/h1.txt")
  h2 = read_from_path("/tmp/h2.txt")

  # Prepare measure data in text lines
  ice_t_line1 = "ICE  HI:" + t1
  ice_t_line2 = "TEMP LO:" + t2
  air_t_line1 = "AIR  HI:" + ht1
  air_t_line2 = "TEMP LO:" + ht2
  air_h_line1 = "AIR  HI:" + h1
  air_h_line2 = "HUM  LO:" + h2

  # Turn backlight on
  lcd_backlight(True)
 
  # Output prepared text
  lcd_string(ice_t_line1,LCD_LINE_1,1)
  lcd_string(ice_t_line2,LCD_LINE_2,1)
  time.sleep(5)
  lcd_string(air_t_line1,LCD_LINE_1,1)
  lcd_string(air_t_line2,LCD_LINE_2,1)
  time.sleep(5)
  lcd_string(air_h_line1,LCD_LINE_1,1)
  lcd_string(air_h_line2,LCD_LINE_2,1)
  time.sleep(5)
  lcd_string("                ",LCD_LINE_1,1)
  lcd_string("                ",LCD_LINE_2,1)

  # Turn backlight off
  lcd_backlight(False)

def initialise():

#  GPIO.setwarnings(False)
  GPIO.setmode(GPIO.BCM)       # Use BCM GPIO numbers
  GPIO.setup(LCD_E, GPIO.OUT)  # E
  GPIO.setup(LCD_RS, GPIO.OUT) # RS
  GPIO.setup(LCD_D4, GPIO.OUT) # DB4
  GPIO.setup(LCD_D5, GPIO.OUT) # DB5
  GPIO.setup(LCD_D6, GPIO.OUT) # DB6
  GPIO.setup(LCD_D7, GPIO.OUT) # DB7
  GPIO.setup(LED_ON, GPIO.OUT) # Backlight enable
  
  # Setup button for gpio 24
  GPIO.setup(BUTTON,GPIO.IN, pull_up_down=GPIO.PUD_UP)

  # Initialise display
  lcd_byte(0x33,LCD_CMD) # 110011 Initialise
  lcd_byte(0x32,LCD_CMD) # 110010 Initialise
  lcd_byte(0x06,LCD_CMD) # 000110 Cursor move direction
  lcd_byte(0x0C,LCD_CMD) # 001100 Display On,Cursor Off, Blink Off
  lcd_byte(0x28,LCD_CMD) # 101000 Data length, number of lines, font size
  lcd_byte(0x01,LCD_CMD) # 000001 Clear display
  time.sleep(E_DELAY)

def lcd_byte(bits, mode):
  # Send byte to data pins
  # bits = data
  # mode = True  for character
  #        False for command

  GPIO.output(LCD_RS, mode) # RS

  # High bits
  GPIO.output(LCD_D4, False)
  GPIO.output(LCD_D5, False)
  GPIO.output(LCD_D6, False)
  GPIO.output(LCD_D7, False)
  if bits&0x10==0x10:
    GPIO.output(LCD_D4, True)
  if bits&0x20==0x20:
    GPIO.output(LCD_D5, True)
  if bits&0x40==0x40:
    GPIO.output(LCD_D6, True)
  if bits&0x80==0x80:
    GPIO.output(LCD_D7, True)

  # Toggle 'Enable' pin
  lcd_toggle_enable()

  # Low bits
  GPIO.output(LCD_D4, False)
  GPIO.output(LCD_D5, False)
  GPIO.output(LCD_D6, False)
  GPIO.output(LCD_D7, False)
  if bits&0x01==0x01:
    GPIO.output(LCD_D4, True)
  if bits&0x02==0x02:
    GPIO.output(LCD_D5, True)
  if bits&0x04==0x04:
    GPIO.output(LCD_D6, True)
  if bits&0x08==0x08:
    GPIO.output(LCD_D7, True)

  # Toggle 'Enable' pin
  lcd_toggle_enable()

def lcd_toggle_enable():
  # Toggle enable
  time.sleep(E_DELAY)
  GPIO.output(LCD_E, True)
  time.sleep(E_PULSE)
  GPIO.output(LCD_E, False)
  time.sleep(E_DELAY)

def lcd_string(message,line,style):
  # Send string to display
  # style=1 Left justified
  # style=2 Centred
  # style=3 Right justified

  if style==1:
    message = message.ljust(LCD_WIDTH," ")
  elif style==2:
    message = message.center(LCD_WIDTH," ")
  elif style==3:
    message = message.rjust(LCD_WIDTH," ")

  lcd_byte(line, LCD_CMD)

  for i in range(LCD_WIDTH):
    lcd_byte(ord(message[i]),LCD_CHR)

def lcd_backlight(flag):
  # Toggle backlight on-off-on
  GPIO.output(LED_ON, flag)

def read_from_path(path):
  value = "--"
  if os.path.exists(path):
    file = open(path, 'r')
    line = file.readline().strip()
    try:
      line = float(line)
      if isinstance(line, float):
        value = line
    except ValueError:
      #not a float
      pass
    file.close()
  return str(value)

def main():
  # Initialise display
  initialise()
  
  # Turn backlight off
  lcd_backlight(False)
  
  # Add event on button down
  GPIO.add_event_detect(BUTTON, GPIO.FALLING, callback = display_values, bouncetime = 500)

  while 1:
    time.sleep(1)

if __name__ == '__main__':

  try:
    main()
  except KeyboardInterrupt:
    pass
  finally:
    GPIO.cleanup()
