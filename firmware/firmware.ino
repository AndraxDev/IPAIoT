// Load Wi-Fi library
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <Arduino_JSON.h>
#include <Servo.h>
#include <Wire.h>
#include <APDS9930.h>

#define DUMP_REGS

const String hardware_id = "2aaba7ef58908c80f82a92a84f79bf98ce4db41cf3b994241bdf8bb0cbdeb5cd";

APDS9930 apds = APDS9930();
float ambient_light = 0; // can also be an unsigned long
uint16_t ch0 = 0;
uint16_t ch1 = 1;

// Replace with your network credentials
const char* ssid     = "AndraxDev";
const char* password = "0andraxdev0";
String serverPath = "http://137.184.46.160/get.php";
String sensorsPath = "http://137.184.46.160/sensors.php";

// Set web server port number to 80
WiFiServer server(80);

// Decode HTTP GET value
String redString = "0";
String greenString = "0";
String blueString = "0";
String rolletsString = "0";
String enabledString = "0";
int pos1 = 0;
int pos2 = 0;
int pos3 = 0;
int pos4 = 0;

// Variable to store the HTTP req  uest
String header;

// Red, green, and blue pins for PWM control
const int redPin = 13;     // 13 corresponds to GPIO13
const int greenPin = 12;   // 12 corresponds to GPIO12
const int bluePin = 14;    // 14 corresponds to GPIO14
const int motion = 0;
const int servo = 2;
const int temperature = A0;

// Setting PWM bit resolution
const int resolution = 256;

int rollets_state = 0;

// Current time
unsigned long currentTime = millis();
// Previous time
unsigned long previousTime = 0; 
// Define timeout time in milliseconds (example: 2000ms = 2s)
const long timeoutTime = 2000;

Servo s1;

void openRollets() {
  s1.write(0);
  delay(1000);
}

void closeRollets() {
  s1.write(180);
  delay(1000);
}

void switchRollets(int state) {
  if (state == 0) closeRollets();
  else openRollets();
}

void setup() {
  Serial.begin(115200);

  pinMode(redPin, OUTPUT);
  pinMode(greenPin, OUTPUT);
  pinMode(bluePin, OUTPUT);

  // Initializing motion sensor
  pinMode(motion, INPUT);
  
  analogWriteRange(resolution);
  analogWrite(redPin, 255);
  analogWrite(greenPin, 255);
  analogWrite(bluePin, 255);

  s1.attach(servo);

  // Initializing ambient light sensor
  if ( apds.init() ) {
    Serial.println(F("APDS-9930 initialization complete"));
  } else {
    Serial.println(F("Something went wrong during APDS-9930 init!"));
  }

  if ( apds.enableLightSensor(false) ) {
    Serial.println(F("Light sensor is now running"));
  } else {
    Serial.println(F("Something went wrong during light sensor init!"));
  }

#ifdef DUMP_REGS
  /* Register dump */
  uint8_t reg;
  uint8_t val;

  for(reg = 0x00; reg <= 0x19; reg++) {
    if( (reg != 0x10) && \
        (reg != 0x11) )
    {
      apds.wireReadDataByte(reg, val);
      Serial.print(reg, HEX);
      Serial.print(": 0x");
      Serial.println(val, HEX);
    }
  }
  apds.wireReadDataByte(0x1E, val);
  Serial.print(0x1E, HEX);
  Serial.print(": 0x");
  Serial.println(val, HEX);
#endif

  // Wait for initialization and calibration to finish
  delay(500);

  // Initializing temperature sensor
  pinMode(A0, INPUT);
  
  // Connect to Wi-Fi network with SSID and password
  Serial.print("Connecting to ");
  Serial.println(ssid);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(300);
    analogWrite(redPin, 0);
    analogWrite(greenPin, 0);
    analogWrite(bluePin, 0);
    delay(300);
    analogWrite(redPin, 0);
    analogWrite(greenPin, 255);
    analogWrite(bluePin, 255);
    Serial.print(".");
  }

  analogWrite(redPin, 0);
  analogWrite(greenPin, 0);
  analogWrite(bluePin, 0);
  
  // Print local IP address and start web server
  Serial.println("");
  Serial.println("WiFi connected.");
  Serial.println("IP address: ");
  Serial.println(WiFi.localIP());
  server.begin();
}

void loop() {
    WiFiClient client;
    HTTPClient http;

    http.begin(client, serverPath.c_str());

    int httpResponseCode = http.GET();
      
    if (httpResponseCode>0) {
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
      String payload = http.getString();
      Serial.println(payload);

      JSONVar svdata = JSON.parse(payload);

      if (JSON.typeof(svdata) == "undefined") {
        Serial.println("Failed to decode server response");
      } else {
        JSONVar keys = svdata.keys();
        Serial.println("Server response decoded");

        redString = JSON.stringify(svdata[keys[0]]);
        greenString = JSON.stringify(svdata[keys[1]]);
        blueString = JSON.stringify(svdata[keys[2]]);
        rolletsString = JSON.stringify(svdata[keys[3]]);
        enabledString = JSON.stringify(svdata[keys[4]]);

        redString.replace("\"", "");
        greenString.replace("\"", "");
        blueString.replace("\"", "");
        rolletsString.replace("\"", "");
        enabledString.replace("\"", "");

        int r = redString.toInt();
        int g = greenString.toInt();
        int b = blueString.toInt();
        int rl = rolletsString.toInt();
        int e = enabledString.toInt();

        if (e == 1) {
          analogWrite(redPin, r);
          analogWrite(greenPin, g);
          analogWrite(bluePin, b);
        } else {
          analogWrite(redPin, 0);
          analogWrite(greenPin, 0);
          analogWrite(bluePin, 0);
        }

        // Save some time
        if (rl != rollets_state) {
          switchRollets(rl);
        }

        rollets_state = rl;

        float sensor_light;
        int sensor_motion;
        float sensor_temp;

        if (!apds.readAmbientLightLux(ambient_light) ||
        !apds.readCh0Light(ch0) || 
        !apds.readCh1Light(ch1)) {
          Serial.println("Error reading light values");
          sensor_light = -1.0f;
        } else {
          Serial.print("Ambient: ");
          Serial.println(ambient_light);
          sensor_light = ambient_light;
        }
        
        Serial.print("Motion: ");
        Serial.println(digitalRead(motion));
        sensor_motion = digitalRead(motion);

        int analogValue = analogRead(temperature);
        float millivolts = (analogValue/1024.0) * 3300; //3300 is the voltage provided by NodeMCU
        float celsius = millivolts/10;
        Serial.print("Temperature: ");
        Serial.println(celsius);
        sensor_temp = celsius;

        // TODO: Send data to the server
        HTTPClient http2;

        String request = sensorsPath + "?light=" + sensor_light + "&motion=" + motion + "&temp=" + sensor_temp + "&hwid=" + hardware_id;
        Serial.print("Sending sensors information to ");
        Serial.println(request);
        http2.begin(client, request.c_str());
    
        int httpResponseCode2 = http2.GET();
          
        if (httpResponseCode2>0) {
          Serial.print("HTTP (sensors) Response code: ");
          Serial.println(httpResponseCode2);
          String payload2 = http2.getString();
          Serial.println(payload2);
        } else {
          Serial.print("Error code: ");
          Serial.println(httpResponseCode2);
        }
        http2.end();
      }
    }
    else {
      Serial.print("Error code: ");
      Serial.println(httpResponseCode);
    }
    // Free resources
    http.end();
}