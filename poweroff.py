import RPi.GPIO as GPIO
import os
import time

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

# 27 for led
GPIO.setup(27,GPIO.OUT)

# 22 for button
GPIO.setup(22,GPIO.IN, pull_up_down=GPIO.PUD_UP)

# turn led on
GPIO.output(27,GPIO.HIGH)

# define function
def Shutdown(channel):
	os.system("sudo shutdown -h now")

# add event on button down
GPIO.add_event_detect(22, GPIO.FALLING, callback = Shutdown, bouncetime = 2000)

while 1:
	time.sleep(1)
