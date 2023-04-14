/*
┌────────────────────────────────────────────────────────┐
│  ███████╗    █████╗    ██████╗                         │
│  ██╔════╝   ██╔══██╗   ██╔══██╗                        │
│  █████╗     ███████║   ██████╔╝                        │
│  ██╔══╝     ██╔══██║   ██╔══██╗                        │
│  ███████╗██╗██║  ██║██╗██║  ██║                        │
│  ╚══════╝╚═╝╚═╝  ╚═╝╚═╝╚═╝  ╚═╝                        │
├───────────────────────┬────────────────────────────────┤
│Challenge 48h (ESP32)  │  EMILE BAILEY & ARTHUR PATUREL │
│       13/04/2023      │       www.github.com/lien_ici  │
└───────────────────────┴────────────────────────────────┘                              
*/

#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>
#include <ArduinoJson.h>
#define SS_PIN 22
#define RST_PIN 5

MFRC522 mfrc522(SS_PIN, RST_PIN);  // Create MFRC522 instance.

const char* ssid = "team4";
const char* password = "team4team4";

char id_boitier[4] = { '0', '0', '2', '\0' };  // L'ID du boitier correspond au numéro de salle. Taille d'exemple maximale "DIGITAL1\0"
char scanned_id[12] = { 0 };                   // Initialize a char array to store the scanned ID.
char response[40] = { 0 };                     // Initialize a char array to store the response.

void setup() {

  Serial.begin(115200);  // Initialize serial communications.
  // connection au lecteur de badge
  SPI.begin();         // Initialize SPI bus.
  mfrc522.PCD_Init();  // Initialize MFRC522.
  Serial.println("Ready to scan RFID tags.");

  // connection au wifi
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.println("Connecting to WiFi...");
  }
  Serial.println("Connected to WiFi");
}

void loop() {
  // Look for new RFID cards.
  if (mfrc522.PICC_IsNewCardPresent() && mfrc522.PICC_ReadCardSerial()) {
    // Convert the scanned ID to a string and print it to the serial monitor.
    for (byte i = 0; i < mfrc522.uid.size; i++) {
      char hex_str[3];
      sprintf(hex_str, "%02X", mfrc522.uid.uidByte[i]);  // Convert the byte to a two-digit hexadecimal string.
      strcat(scanned_id, hex_str);                       // Append the hexadecimal string to the scanned ID char array.
    }

    // Send the scanned ID and room ID as a JSON string via a GET request.
    createJson(id_boitier, scanned_id);

    // Reset the scanned ID array.
    memset(scanned_id, 0, sizeof(scanned_id));

    mfrc522.PICC_HaltA();       // Halt PICC.
    mfrc522.PCD_StopCrypto1();  // Stop encryption on PCD.
  }
}

void createJson(char* salle, char* badge_id) {
  // Create a JSON document.
  StaticJsonDocument<200> doc;

  // Add the room and value to the JSON document.
  doc["Salle"] = salle;
  doc["Badge_ID"] = badge_id;

  // Serialize the JSON document to a string.
  size_t len = measureJson(doc) + 1;
  char* jsonStr = (char*)malloc(len);
  serializeJson(doc, jsonStr, len);

  // Make the POST request using the JSON string.

  String url = "http://server.local/api/room/" + String(id_boitier) + "/add-user/" + String(badge_id);
  HTTPClient http;
  http.begin(url);
  http.addHeader("Content-Type", "application/json");
  int httpCode = http.POST(jsonStr);
  Serial.println(jsonStr);


  // Print the response to the serial monitor.
  String response = http.getString();
  Serial.println(response);

  // Free the memory used by the JSON string.
  free(jsonStr);
}
