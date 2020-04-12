import RPi.GPIO as GPIO
import microgear.client as microgear
import time
import logging
from firebase import firebase
import re

firebase = firebase.FirebaseApplication('https://seniorproject-muic.firebaseio.com/', None)

fan = 19
pump = 26

GPIO.setwarnings(False)
GPIO.setmode(GPIO.BCM)
GPIO.setup(fan,GPIO.OUT)
GPIO.setup(pump,GPIO.OUT)

appid = "SmartGardenProject"
gearkey = "0PU47Vd8dzaRIuT"
gearsecret =  "A3QLd61QNZJTXOwWsHOuXtpls"

microgear.create(gearkey,gearsecret,appid,{'debugmode': True})

mostrecentKeyID = 0
mostrecentTimestamp = 0
new_threshold = [28,80]

def connection():
    logging.info("Now I am connected with netpie")

def subscription(topic,message):
    global new_threshold
    logging.info(topic+" "+message)
    temp = re.findall(r'[\d]+', message)
    new_threshold[0] = temp[0]
    new_threshold[1] = temp[1]

def disconnect():
    logging.debug("disconnect is work")

microgear.setalias("pi")
microgear.on_connect = connection
microgear.on_message = subscription
microgear.on_disconnect = disconnect
microgear.subscribe("/mail")
microgear.connect(False)
    
while True:
    if(microgear.connected):
        myGetResults = firebase.get('/sensor/am2320(new)/average', None)
        for keyID in myGetResults:
            if int(myGetResults[keyID]['Index'] > mostrecentTimestamp):
                mostrecentTimestamp = int(myGetResults[keyID]['Index'])
                mostrecentKeyID = myGetResults[keyID]
        temp = int(myGetResults[keyID]['Temperature'])
        humid = int(myGetResults[keyID]['Humidity'])
        print("%d - %d | %d - %d" % (temp, int(new_threshold[0]), humid, int(new_threshold[1]),))
        if(temp < int(new_threshold[0]) and humid < int(new_threshold[1])):
            print("pump on")
            #LOW on, HIGH off
            GPIO.output(fan,GPIO.HIGH)
            GPIO.output(pump,GPIO.LOW)
            time.sleep(60)
            GPIO.output(pump,GPIO.HIGH)
        elif(temp < int(new_threshold[0]) and humid > int(new_threshold[1])):
            print("fan on")
            GPIO.output(pump,GPIO.HIGH)
            GPIO.output(fan,GPIO.LOW)
            time.sleep(60)
            GPIO.output(fan,GPIO.HIGH)
        elif(temp > int(new_threshold[0]) and humid < int(new_threshold[1])):
            print("fan and pump on")
            GPIO.output(fan,GPIO.LOW)
            GPIO.output(pump,GPIO.LOW)
            time.sleep(60)
            GPIO.output(fan,GPIO.HIGH)
            GPIO.output(pump,GPIO.HIGH)
        elif(temp > int(new_threshold[0]) and humid > int(new_threshold[1])):
            print("fan on")
            GPIO.output(pump,GPIO.HIGH) 
            GPIO.output(fan,GPIO.LOW)
            time.sleep(60)
            GPIO.output(fan,GPIO.HIGH)
        time.sleep(10)
            
