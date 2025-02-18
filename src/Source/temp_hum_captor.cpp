#include "./Header/temp_hum_captor.h"
#include "./Header/typeDef.h"

DHTesp dht;

float temperature = 0;
float humidity = 0;

void setupTemp_Hum()
{
      dht.setup(DHT22_PIN,DHTesp::DHT22);
}

void getTemp_Hum(void *pvParameters)
{
    for (;;) {
    if(dht.getStatus() == DHTesp::ERROR_NONE) {

      //Update main variables
      temperature =  dht.getTemperature();
      humidity = dht.getHumidity();

      //Prints data into the Serial console
      Serial.print("[TempHumidityTask]: Temp:\t");
      Serial.print(temperature);
      Serial.print(" °C\t");
      Serial.print("Humidity:\t");
      Serial.print(humidity);
      Serial.println(" %");
    } else {
      //Prints error into console when temp/humidity sensor is not responding
      Serial.print("[TempHumidityTask][Error]:\t");
      Serial.println(dht.getStatusString());
    }
    
    //Update delay
    delay(10000);
  }
}