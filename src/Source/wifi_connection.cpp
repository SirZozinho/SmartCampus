#include "./Header/wifi_connection.h"

const char* ssid = "eduroam";

void setupWifi()
{ 
    Serial.begin(9600); 
    delay(10); 
    Serial.println(); 
    Serial.print("Connecting to network: "); 
    Serial.println(ssid); 
    WiFi.disconnect(true); // disconnect form wifi to set new wifi connection 
    WiFi.mode(WIFI_STA); // init wifi mode 
   
    // A cert-file-free Eduroam connection with PEAP (or TTLS) 
    WiFi.begin(ssid, WPA2_AUTH_PEAP, EAP_IDENTITY, EAP_USERNAME, EAP_PASSWORD);

    // Print dot during the connecting 
    while (WiFi.status() != WL_CONNECTED) { 
        delay(500); 
        Serial.print(".");
    }

    // Display informations
    Serial.println(""); 
    Serial.println("WiFi connected."); 
    Serial.println("IP address set: ");  
    Serial.println(WiFi.localIP()); // Print LAN IP
 
    // Disconnect WiFi as it's no longer needed 
    //WiFi.disconnect(true); 
    //WiFi.mode(WIFI_OFF);
}


