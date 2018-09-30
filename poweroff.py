import RPi.GPIO as GPIO
import os
import time

GPIO.setmode(GPIO.BCM)
GPIO.setwarnings(False)

# 27 for led
led = 27
# 22 for button
button = 22

GPIO.setup(button, GPIO.IN, pull_up_down=GPIO.PUD_UP)
GPIO.setup(led, GPIO.OUT)
# turn led on
GPIO.output(led, GPIO.HIGH)

# define function
def Shutdown(channel):
	os.system("sudo shutdown -h now")

# add event on button down
GPIO.add_event_detect(button, GPIO.FALLING, callback = Shutdown, bouncetime = 2000)

while 1:
	time.sleep(1)
