#include "./Header/time.h"

const char* ntpServer = "fr.pool.ntp.org"; // Serveur NTP externe
const long  gmtOffset_sec = 3600;          // Décalage horaire en secondes (GMT+1)
const int   daylightOffset_sec = 3600;     // Décalage d'heure d'été (si applicable)

String currentTime;

void setupTime()
{
  Serial.begin(9600);

  // Synchronisation de l'heure via le serveur NTP
  configTime(gmtOffset_sec, daylightOffset_sec, ntpServer);

  // Test de récupération de l'heure locale
  struct tm timeinfo;
  if (!getLocalTime(&timeinfo)) {
    Serial.println("Échec de l'obtention de l'heure");
    return;
  }

  // Affichage de l'heure obtenue
  Serial.println("Heure obtenue via NTP:");
  Serial.println(&timeinfo, "%A, %B %d %Y %H:%M:%S");
}

void getTime(void *pvParameters)
{
  for (;;) {
    struct tm timeinfo;
    if (!getLocalTime(&timeinfo)) {
      Serial.println("Échec de l'obtention de l'heure");
      currentTime = "Erreur";
      return;
    }
    
    char buffer[20];
    snprintf(buffer, sizeof(buffer), "%04d-%02d-%02d %02d:%02d:%02d", 
             timeinfo.tm_year + 1900, timeinfo.tm_mon + 1, timeinfo.tm_mday, 
             timeinfo.tm_hour, timeinfo.tm_min, timeinfo.tm_sec);
    currentTime = String(buffer);

    //Affichage de l'heure sur le moniteur série
    Serial.print("[TimeTask]: Hour : ");
    Serial.println(currentTime);

    delay(10000); // Délai de 5 secondes
  }
}
