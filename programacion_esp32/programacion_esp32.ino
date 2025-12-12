#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <DHT.h>

// ----- CREDENCIALES Y SERVIDOR -----
const char* ssid = "TU_NOMBRE_WIFI";
const char* password = "TU_CONTRASEÑA_WIFI";
// ¡CAMBIA ESTO POR LA IP DE TU PC!
// USAR IP LOCAL DETECTADA (No usar localhost/127.0.0.1 en el ESP32)
const char* serverUrl = "http://192.168.1.XXX:8000/api/esp32/sync"; 
 

// ----- PINES CONFIGURADOS -----
#define OLED_SDA 22 
#define OLED_SCL 21
#define DHTPIN 4
#define DHTTYPE DHT11
const int ledPin = 2;
const int buzzerPin = 25;
const int ldrPin = 34;

// ----- OBJETOS -----
DHT dht(DHTPIN, DHTTYPE);
Adafruit_SSD1306 display(128, 64, &Wire, -1);

void setup() {
  Serial.begin(115200);
  pinMode(ledPin, OUTPUT);
  pinMode(buzzerPin, OUTPUT);
  dht.begin();

  // Iniciar OLED
  Wire.begin(OLED_SDA, OLED_SCL);
  if(!display.begin(SSD1306_SWITCHCAPVCC, 0x3C)) {
     if(!display.begin(SSD1306_SWITCHCAPVCC, 0x3D)) {
        Serial.println("Fallo OLED");
        while(1);
     }
  }

  // Conectar WiFi
  display.clearDisplay();
  display.setTextColor(WHITE);
  display.setCursor(0,0);
  display.println("Conectando WiFi...");
  display.display();

  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Conectado!");
}

void loop() {
  // 1. LEER DATOS LOCALES
  float t = dht.readTemperature();
  float h = dht.readHumidity();
  int ldr = analogRead(ldrPin);

  // Evitar errores de lectura NaN
  if (isnan(t)) t = 0;
  if (isnan(h)) h = 0;

  // 2. ENVIAR A LARAVEL Y RECIBIR ORDENES
  if(WiFi.status() == WL_CONNECTED){
    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/json");

    // Crear JSON para enviar
    StaticJsonDocument<200> docEnvio;
    docEnvio["temp"] = t;
    docEnvio["hum"] = h;
    docEnvio["ldr"] = ldr;
    
    String jsonString;
    serializeJson(docEnvio, jsonString);

    // Enviar POST
    int httpResponseCode = http.POST(jsonString);

    if (httpResponseCode > 0) {
      String response = http.getString();
      Serial.println("Laravel dice: " + response);

      // Procesar respuesta JSON de Laravel
      StaticJsonDocument<512> docResp;
      DeserializationError error = deserializeJson(docResp, response);

      if (!error) {
        // --- APLICAR ORDENES ---
        bool ordenLed = docResp["led"];
        bool ordenBuzzer = docResp["buzzer"];
        const char* mensaje = docResp["mensaje"];

        digitalWrite(ledPin, ordenLed ? HIGH : LOW);
        
        if(ordenBuzzer) tone(buzzerPin, 1000);
        else noTone(buzzerPin);

        // --- ACTUALIZAR PANTALLA ---
        display.clearDisplay();
        display.setCursor(0,0);
        display.print("T:"); display.print(t, 1);
        display.print(" L:"); display.println(ldr);
        
        display.drawLine(0, 15, 128, 15, WHITE);
        
        display.setCursor(0, 25);
        display.println("Mensaje API:");
        display.println(mensaje);
        
        display.setCursor(0, 55);
        display.print("LED:"); display.print(ordenLed ? "ON" : "OFF");
        display.display();
      }
    } else {
      Serial.print("Error HTTP: ");
      Serial.println(httpResponseCode);
      display.clearDisplay();
      display.setCursor(0,0);
      display.println("Error conexion API");
      display.println(httpResponseCode);
      display.display();
    }
    http.end();
  }

  delay(2000); // Sincronizar cada 2 segundos
}