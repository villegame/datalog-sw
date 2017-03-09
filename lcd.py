import RPi.GPIO as GPIO
import time
import os

DB_OK = False

if True == os.path.isfile(os.path.dirname(os.path.abspath(__file__))+"/dblib.py"):
  from dblib import Database
  DB_OK = True

from lcdlib import LCD

LCD_LINE_1 = 0x80 # LCD RAM address for the 1st line
LCD_LINE_2 = 0xC0 # LCD RAM address for the 2nd line

BUTTON = 3

# Device name length limit on lcd line
DEVICE_NAME_MAX_LEN = 10
# Displayvalues triggering monitoring
lastpress = 0.1
# Triggering limit in seconds
triggerlimit = 16
# Screen cycle time in seconds
screen_cycle_time = 5
# Additional time after cycles to wait before next cycle
screen_cycle_extra_time = 3

database = None
if True == DB_OK:
  database = Database()
lcd = LCD()

def display_values(channel):

  global lastpress
  if(lastpress + triggerlimit > time.time()):
    return
  else:
    lastpress = time.time()
  if True == DB_OK:
    if True == database.test_conn():
      read_from_db()
    else:
      read_from_files()
  else:
    read_from_files()

def read_from_db():

  last_screen = database.get_max_screen()
  devices = database.get_latest_values()

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
  triggerlimit = len(screen_texts) * screen_cycle_time + screen_cycle_extra_time

  # Turn backlight on
  lcd.lcd_backlight(True)

  for line in screen_texts:
    lcd.lcd_string(line[0],LCD_LINE_1,1)
    lcd.lcd_string(line[1],LCD_LINE_2,1)
    time.sleep(screen_cycle_time)
  
  # Turn backlight off
  lcd.lcd_backlight(False)

  lcd.lcd_string("                ", LCD_LINE_1,1)
  lcd.lcd_string("                ", LCD_LINE_2,1)

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

def read_from_files():

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
  lcd.lcd_backlight(True)
 
  # Output prepared text
  lcd.lcd_string(ice_t_line1,LCD_LINE_1,1)
  lcd.lcd_string(ice_t_line2,LCD_LINE_2,1)
  time.sleep(screen_cycle_time)
  lcd.lcd_string(air_t_line1,LCD_LINE_1,1)
  lcd.lcd_string(air_t_line2,LCD_LINE_2,1)
  time.sleep(screen_cycle_time)
  lcd.lcd_string(air_h_line1,LCD_LINE_1,1)
  lcd.lcd_string(air_h_line2,LCD_LINE_2,1)
  time.sleep(screen_cycle_time)
  lcd.lcd_string("                ",LCD_LINE_1,1)
  lcd.lcd_string("                ",LCD_LINE_2,1)

  # Turn backlight off
  lcd.lcd_backlight(False)

def main():
  # Initialise lcd display
  lcd.initialise()

  # Setup button
  GPIO.setup(BUTTON,GPIO.IN, pull_up_down=GPIO.PUD_UP)
  
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
    lcd.lcd_gpio_cleanup()
