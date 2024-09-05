#include <ESP8266WiFi.h>
#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN D2  // SDA pin
#define RST_PIN D1 // Reset pin
MFRC522 mfrc522(SS_PIN, RST_PIN);  // Create MFRC522 instance

const char* ssid = "Guest";        // Replace with your Wi-Fi SSID
const char* password = "thePortal"; // Replace with your Wi-Fi password

const char* host = "esp-test.atwebpages.com";   // Replace with your server address

void setup() {
  Serial.begin(115200);
  delay(10);

  // Connect to Wi-Fi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to Wi-Fi");
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) { // Timeout after 20 attempts
    delay(500);
    Serial.print(".");
    attempts++;
  }
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\nFailed to connect to Wi-Fi");
    return;
  }
  Serial.println("\nConnected to Wi-Fi");

  
  SPI.begin();           // Initialize SPI bus
  mfrc522.PCD_Init();    // Initialize MFRC522
  Serial.println("Place your card to the reader...");
}

void loop() {
  // Look for new cards
  if (!mfrc522.PICC_IsNewCardPresent()) {
    return;
  }

  // Select one of the cards
  if (!mfrc522.PICC_ReadCardSerial()) {
    return;
  }

  // Store the UID in a string in the format FF FF FF FF
  String uidString = "";
  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) {
      uidString += "0";  // Add leading zero for values less than 0x10
    }
    uidString += String(mfrc522.uid.uidByte[i], HEX);  // Convert byte to hex string
    if (i < mfrc522.uid.size - 1) {
      uidString += "%20";  // Add space between bytes, but not after the last byte
    }
  }

  // Convert to uppercase for standard UID format
  uidString.toUpperCase();

  // Print the UID string to Serial
  Serial.print("UID tag: ");
  Serial.println(uidString);
  MarkPresent(uidString);
  
  // Halt PICC to stop reading
  mfrc522.PICC_HaltA();
}

void MarkPresent(String Card) {
  // Connect to the server
  WiFiClient client;
  Serial.print("Connecting to ");
  Serial.print(host);
  if (!client.connect(host, 80)) { // Connect to server on port 80
    Serial.println(" - Connection to host failed");
    return;
  }
  Serial.println(" - Connected to host");

  String url = "/write.php?mode=mark-attendance&card=" + Card;
  Serial.println(url);

  // Send GET request
  client.print(String("GET ") + url + " HTTP/1.1\r\n" +
              "Host: " + host + "\r\n" +
              "Connection: close\r\n\r\n");

  // Wait for response
  delay(1000); // Adjust the delay as needed

  String response;
  // Read and print the response (optional)
  while (client.available()) {
    response = client.readStringUntil('\r');
  }
  Serial.println(response);

  // Close the connection
  client.stop();
  Serial.println("Connection closed");
}
