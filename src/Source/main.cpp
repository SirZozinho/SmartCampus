#include "./Header/typedef.h"
#include "./Header/screen_display.h"
#include "./Header/co2_captor.h"
#include "./Header/temp_hum_captor.h"
#include "./Header/detector_person.h"
#include "./Header/wifi_connection.h"
#include "./Header/time.h"
#include "./Header/send_data_api.h"


void setup() 
{
  setupWifi();
  setupCo2();
  setupScreen();
  setupTemp_Hum();
  voidSetupDetectorPerson();
  setupTime();

  /*
    RTOS setup
  */
  xTaskCreate(getTemp_Hum, "GetTempAndHumidity", 4096, NULL, 5, NULL);
  xTaskCreate(getCo2, "GetCO2",4096,NULL,4,NULL);
  xTaskCreate(displayScreen,"UpdateScreen",4096,NULL,3,NULL);
  xTaskCreate(isAnyoneHere,"DetectorPersonNearby",4096,NULL,1,NULL);
  xTaskCreate(getTime,"GetTime",4096,NULL,2,NULL);
  xTaskCreate(sendToAPI,"SendToAPI",8192,NULL,6,NULL);
}
void loop()
{
//Nothing there
}