from firebase import firebase
import time
import datetime
import board
import busio
import adafruit_am2320
import adafruit_tca9548a
from w1thermsensor import W1ThermSensor

firebase = firebase.FirebaseApplication('https://seniorproject-muic.firebaseio.com/', None)

sensor = W1ThermSensor()

i2c = busio.I2C(board.SCL, board.SDA)
tca = adafruit_tca9548a.TCA9548A(i2c)

am0 = adafruit_am2320.AM2320(tca[6]) #r b
am1 = adafruit_am2320.AM2320(tca[1]) #r f
am2 = adafruit_am2320.AM2320(tca[2]) #l f
am3 = adafruit_am2320.AM2320(tca[5]) #l b

mostrecentKeyID = 0
mostrecentTimestamp = 0
temp = []
humid = []

try:
    myGetResults = firebase.get('/sensor/am2320(new)/average', None)
    
    for keyID in myGetResults:
        if int(myGetResults[keyID]['Index'] > mostrecentTimestamp):
            mostrecentTimestamp = int(myGetResults[keyID]['Index'])
            mostrecentKeyID = myGetResults[keyID]
            
    index = int(myGetResults[keyID]['Index'])
except:
    index = 0
    
def update_firebase(temperature, humidity, bus):
    global index
    if temperature is not None and humidity is not None:
        #time.sleep(0.5)
        str_temperature = ' {0:0.2f} *C '.format(temperature)  
        str_humidity  = ' {0:0.2f} %'.format(humidity)
        #print("Temperature: {0:0.2f} *C Humidity: {1:0.2f} %\n". format(temperature, humidity,))  
    else:
        print('Failed!!')  
        #time.sleep(0.5)
    index = index + 1
    now = datetime.datetime.now()
    time = str(now.strftime("%Y/%m/%d %H:%M:%S"))
    data = {"Temperature": temperature, "Humidity": humidity}
    avg_data = {"Index": index, "Time": time, "Temperature": temperature, "Humidity": humidity}
    if(bus == 1):
        firebase.post('/sensor/am2320(new)/bus1', data)
    elif(bus == 3):
        firebase.post('/sensor/am2320(new)/bus3', data)
    elif(bus == 4):
        firebase.post('/sensor/am2320(new)/bus4', data)
    elif(bus == 5):
        firebase.post('/sensor/am2320(new)/bus5', data)
    elif(bus == 6):
        firebase.post('/sensor/am2320(new)/average', avg_data)
        print("Average\nTemperature: {0:0.2f} *C Humidity: {1:0.2f} %\n". format(temperature, humidity,))

while True:
    temperature = float("{0:.2f}".format(sensor.get_temperature()))
    try:
        temp0, humid0 = temperature, am0.relative_humidity
        #print(am0.temperature, am0.relative_humidity)
        update_firebase(temp0, humid0, 1)
        temp.append(temp0)
        humid.append(humid0)
        print(temp0,humid0)
    except:
        print("error0")
        
    try:
        temp1, humid1 = temperature, am1.relative_humidity
        #print(am1.temperature, am1.relative_humidity)
        update_firebase(temp1, humid1, 3)
        temp.append(temp1)
        humid.append(humid1)
        print(temp1,humid1)
    except:
        print("error1")

    try:
        temp2, humid2 = temperature, am2.relative_humidity
        #print(am2.temperature, am2.relative_humidity)
        update_firebase(temp2, humid2, 4)
        temp.append(temp2)
        humid.append(humid2)
        print(temp2,humid2)
    except:
        print("error2")

    try:
        temp3, humid3 = temperature, am3.relative_humidity
        #print(am3.temperature, am3.relative_humidity)
        update_firebase(temp3, humid3, 5)
        temp.append(temp3)
        humid.append(humid3)
        print(temp3,humid3)
    except:
        print("error5")
        
    total_temp = sum(temp)
    avg_temp = total_temp/len(temp)
    total_humid = sum(humid)
    avg_humid = total_humid/len(humid)
    #print("Temperature: {0:0.2f} *C Humidity: {1:0.2f} %\n". format(avg_temp, avg_humid,))
    update_firebase(int(avg_temp), int(avg_humid), 6)
    temp.clear()
    humid.clear()
    time.sleep(5)