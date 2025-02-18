#include "./Header/detector_person.h"

bool isAnyoneNearby = false;

void voidSetupDetectorPerson()
{
  pinMode(SENSOR_PIN, INPUT);
}

void isAnyoneHere(void *pvParameters) {
  for (;;) {
    int sensorState = digitalRead(SENSOR_PIN);
    
    if (sensorState == HIGH) {
      isAnyoneNearby = true;
      Serial.println("Someone");
      delay(60000);
    } else {
      Serial.println("Nobody");
      isAnyoneNearby = false;
    }

    delay(500);  // Attendre avant la prochaine lecture
  }
}