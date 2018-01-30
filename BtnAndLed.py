#!/usr/bin/env python
import socket
import RPi.GPIO as GPIO
from threading import Timer
import os
import time
import smtplib
import subprocess
import _mysql
import MySQLdb
GPIO.setwarnings(False)

db=MySQLdb.connect(host="145.89.160.172",port=3306,user="SjoerdZ",
                      passwd="raspberry",db="IDP")
db.autocommit(True)
k=[]
##dequery = db.query("""SELECT * FROM huizen
##         """)


##r=db.store_result()
##r=db.use_result()
##r.fetch_row(10,1)
##print(r)


LedPin = 11    # pin11 --- led
BtnPin = 12    # pin12 --- button
ApplampPin = 40
AlarmPin = 15
AlarmBtn = 16
##LampPin = 37
##LampBtn = 40

Led_status = 1
Lamp_status = 1
Camera_status = 0
Alarm_status = 1


Alarmled = 0
CameraLed = 0
socket = ""               # Hier komt de socket
print'welkom bij de client, druk op de groene knop om de camera te starten of stoppen.'
print'Druk op de Rode knop om het alarm aan te zetten, doe dit alleen in nood situaties.'

##def lampje():
##    GPIO.setup(40, GPIO.OUT)
##    while (True):
##        GPIO.output(40, True)
##        time.sleep(0.5)
##        GPIO.output(40, False)
##        time.sleep(0.5)

TO= "john.vanmeerten@student.hu.nl" #all of the credentials
GMAIL_USER="csnproject2017@gmail.com"
PASS= "project123"
SUBJECT = 'Alert!'
TEXT = 'HET ALARM IS GEACTIVEERD !!!! CHECK DE CAMERA FEED http://145.89.157.164:8081/'

def send_mail(): #the texting portion
    print ("Sending text")
    server = smtplib.SMTP('smtp.gmail.com:587')
    server.starttls()
    server.login(GMAIL_USER,PASS)
    header = 'To: ' + TO + '\n' + 'From: ' + GMAIL_USER
    header = header + '\n' + 'Subject: ' + SUBJECT + '\n'
    print (header)
    msg = header + '\n' + TEXT + '\n\n'
    server.sendmail(GMAIL_USER,TO,msg)
    server.quit()
    time.sleep(1)
    print ("Text sent")

##def connectionFcn():
##    global Alarm_status# Connectie maken met de server
##    global socket
##    global connection
##    connection = False    # Er is geen conecctie
##    while connection == False :     # Als er geen connectie is probeert hij hem te maken
##        try:
##            socket = socket.socket()      # s word de socket
##            host = socket.gethostbyname('145.89.160.172')   #IP van server
##            port = 12221     # Portnummer
##            s.connect((host, port))        # Connect
##            print('Connected to', host)   # Bevestiging dat hij verbonden is
##            connection = True          # Connectie waarde word veranderd
##        except:    # Als er geen verbinding gemaakt kan worden of hij valt weg
##            print('Connectie verloren')  #ALARM 
##            global Alarm_status # Rode lamp gaat aan
##            Alarm_status = 0
##            GPIO.output(AlarmPin,Alarm_status)
####            time.sleep(1)    #hij hoeft maar 1 keer per seconde te kijken of hij kan verbinden
####            GPIO.output(BuzzerPin, GPIO.LOW)
####            time.sleep(0.1)
####            GPIO.output(BuzzerPin, GPIO.HIGH)
####            time.sleep(0.1)

def setup():
	GPIO.setmode(GPIO.BOARD)       # Numbers GPIOs by physical location
	
	GPIO.setup(LedPin, GPIO.OUT)   # Set LedPin's mode is output
	GPIO.setup(BtnPin, GPIO.IN, pull_up_down=GPIO.PUD_UP)    # Set BtnPin's mode is input, and pull up to high level(3.3V)
	GPIO.output(LedPin, GPIO.HIGH)

        GPIO.setup(AlarmPin, GPIO.OUT)   # Set LedPin's mode is output
	GPIO.setup(AlarmBtn, GPIO.IN, pull_up_down=GPIO.PUD_UP)    # Set BtnPin's mode is input, and pull up to high level(3.3V)
	GPIO.output(AlarmPin, GPIO.HIGH)
	
##	GPIO.setup(LampPin, GPIO.OUT)   # Set LedPin's mode is output
##	GPIO.setup(LampBtn, GPIO.IN, pull_up_down=GPIO.PUD_UP)    # Set BtnPin's mode is input, and pull up to high level(3.3V)
##	GPIO.output(LampPin, GPIO.HIGH)
	

def swLed(ev=None):
	global Led_status
	global Camera_status
	global Alarm_status
	global CameraLed
	Led_status = not Led_status
	GPIO.output(LedPin, Led_status)  # switch led status(on-->off; off-->on)
	if Led_status == 1 and Alarmled ==0:
		print 'led off...'
##		lampje()
                CameraUit()
                CameraLed = 0
	else:
		print '...led on'
		CameraLed = 1
		print(CameraLed)
                CameraAan()
                
                


def swAlarm(ev=None):
        global Alarm_status
	global Camera_status
	global Alarmled
	s=db.cursor()
	Alarm_status = not Alarm_status
	print(Alarm_status)
	GPIO.output(AlarmPin, Alarm_status)
        if Alarm_status == 1:
            print 'Alarm uit'
            Alarmled = 0
            print(Alarmled)
            s.execute("""UPDATE huizen SET huizen.Alarm=3 WHERE huis =1""")
            s.close
        else:
            print 'ALARM'
            Alarmled = 1
            print(Alarmled)
            CameraAan()
            send_mail()
            s.execute("""UPDATE huizen SET huizen.Alarm=2 WHERE huis =1""")
            s.close
            
##            sendPacket('3')
                      
        
def CameraAan():
    global Camera_status
    global CameraLed
    s=db.cursor()
    if Camera_status == 0 and Alarmled == 1 or CameraLed == 1 :
        print 'Camera server gestart'
        subprocess.call(['./startcamera.sh'])
        Camera_status = 1
        s.execute("""UPDATE huizen SET huizen.camera=3 WHERE huis =1""")
        s.close
        
##        sendPacket('1')
def GetCameraData():
    global Alarm_status
    global Camera_status
    global Alarmled
# print header
    global db
    global k
    k= None
##    Camera_status = 1
    c=db.cursor()
    s=db.cursor()
    c.execute("""SELECT * FROM huizen""")
##    k = ()
    k = c.fetchall()
    c.close()
##k.split('"')
##    print(k)
    print(k[0][2],'kijk hier naar')
##    print(k[0][1][2])
    if k[0][2] == '0' :
        print"KOM IK HIER"
        subprocess.call(['./stopcamera.sh'])
        Camera_status = 0
        s.execute("""UPDATE huizen SET huizen.camera=2 WHERE huis =1""")
        s.close
    elif k[0][2] == '1':
        print'hello'
        subprocess.call(['./startcamera.sh'])
        s.execute("""UPDATE huizen SET huizen.camera=3 WHERE huis =1""")
        s.close
        Camera_status = 1

def GetAlarmData():
    global Alarm_status
    global Camera_status
    global Alarmled
    global db
    global k
    s=db.cursor()
##    print(k[0][3],'de alarmDB data')
    if k[0][3] == '1':
        swAlarm()
        s.execute("""UPDATE huizen SET huizen.Alarm=2 WHERE huis =1""")
        s.close
    elif k[0][3] =='0':
        print('alarm staat uit')
        s.execute("""UPDATE huizen SET huizen.Alarm=3 Where huis =1""")
        
def Applamp(ev=None):
    global ApplampPin
    global k
    GPIO.setup(40,GPIO.OUT)
    s=db.cursor()
    print(k)
    if k[0][1][6] == "1":
        GPIO.output(40,GPIO.HIGH)
        print('APP lamp aan')
    else:
        GPIO.output(40,GPIO.LOW)
        print('APP lamp uit')
    
        
##    while k[0][1][2] == 1:
##        time.sleep(1)
##        swLed()


##def swLamp(ev=None):
##    global Lamp_status
##    Lamp_status = not Lamp_status
##    GPIO.output(LampPin, Led_status)  # switch led status(on-->off; off-->on)
##    if Lamp_status == 1:
##        print 'led off...'
####	   lampje()
##    else:
##	print '...led on'
	
	
def xConnect():
    cnx = mysql.connector.connect(user='root', password='raspberry',
                                host='145.89.160.172',
                                database='idp')
    cnx.close()
##xConnect()

def CameraUit():
    global Camera_status
    global Alarmled
    s=db.cursor()
    if Camera_status == 1 and Alarmled == 0 :
        subprocess.call(['./stopcamera.sh'])
        print 'Camera server gestopt'
        s.execute("""UPDATE huizen SET huizen.camera=2 Where huis =1""")
        s.close
        Camera_status = 0
        print(Alarmled)
        #sendPacket('')
    else:
        print(Alarmled)
        print 'geprobeerd camera uit te zetten echter staat het alarm aan, schakel het alarm eerst uit'
##        sendPacket('0')

def loop():
	GPIO.add_event_detect(BtnPin, GPIO.FALLING, callback=swLed, bouncetime=200) # wait for falling and set bouncetime to prevent the callback function from being called multiple times when the button is pressed
	GPIO.add_event_detect(AlarmBtn, GPIO.FALLING, callback=swAlarm, bouncetime=200)
##	GPIO.add_event_detect(LampBtn, GPIO.FALLING, callback=swLamp, bouncetime=200)

	#GPIO.add_event_detect(GrnBtnPin, GPIO.FALLING, callback=GrnswLed, bouncetime=200) # Als de groene knop in word ingedrukt etc.
        #PIO.add_event_detect(RedBtnPin, GPIO.FALLING, callback=RedswLed, bouncetime=200)
	while True:
		time.sleep(1)   # Don't do anything
		GetCameraData()
		GetAlarmData()
		Applamp()
		


def destroy():
        global db
	GPIO.output(LedPin, GPIO.HIGH)     # led off
	GPIO.output(AlarmPin, GPIO.HIGH)
	GPIO.output(40,GPIO.LOW)
	subprocess.call(['./stopcamera.sh'])
        print 'Camera server gestopt'
        db.close()
	GPIO.cleanup()                     # Release resource

if __name__ == '__main__':     # Program start from here
	setup()
##	connectionFcn()
	try:
		loop()
	except KeyboardInterrupt:  # When 'Ctrl+C' is pressed, the child program destroy() will be  executed.
		destroy()