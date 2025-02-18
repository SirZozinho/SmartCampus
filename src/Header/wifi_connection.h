#ifndef WIFI_SC_H
#define WIFI_SC_H

#include <WiFi.h>
#include "esp_wpa2.h" //wpa2 library for connections to Enterprise networks

// login details of Clement for Eduroam
#define EAP_IDENTITY "cmuzelie"
#define EAP_USERNAME "cmuzelie"
#define EAP_PASSWORD "-6D4k8r!NsO6+42."

extern bool isWifiConnected;
 
/**
 * @brief Setup the Wifi connection.
 * 
 * This function initializes the Wifi connection, after that ESP-32 is connected to Internet.
 * 
 * @author Clément Muzelier
 * @return void
 */
void setupWifi();

#endif // WIFI_SC_H