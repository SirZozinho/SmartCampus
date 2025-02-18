#include "./Header/typedef.h"
#include "./Header/screen_display.h"
#include "./Header/co2_captor.h"
#include "./Header/temp_hum_captor.h"
#include "./Header/detector_person.h"

SSD1306Wire display(OLED_ADDR, SDA_PIN, SCL_PIN);

void setupScreen()
{
    /*
    Communication setup
    */
    Wire.begin((uint8_t)SDA_PIN, (uint8_t)SCL_PIN);
    Serial.begin(9600);
    Serial.println("Init...");

    /*
    OLED Screen Setup
    */
    display.init();
    display.flipScreenVertically();
    display.setFont(ArialMT_Plain_10);
    display.clear();
    display.drawString(0, 0, "Initialisation...");
    display.display();

}

void displayScreen(void *pvParameters)
{
    for(;;) {
      //Clear screen before any action
      display.clear();

      //check if there is someone nearby
      changeBrightness();
      
      //Check if sensor is responding
      if (dht.getStatus() == DHTesp::ERROR_NONE) {
        display.drawString(0,0, "T: " + String(temperature));
        display.drawString(0,20,"H: " + String(humidity));

      } else {
        //Prints errors in display if temp/humidity sensor is not responding
        display.drawString(0,0, "T: " + String(dht.getStatusString()));
        display.drawString(0,20,"H: " + String(dht.getStatusString()));
      }

      //Same for CO2 sensor
      if (err == STATUS_OK) {
        display.drawString(0,40,"CO2: " + String(CO2));
      } else {
        display.drawString(0,40,"CO2: ERROR");
      }

      display.display();//Refresh the screen
      delay(10000);
    }
}

void changeBrightness()
{
  if(isAnyoneNearby == true)
  {
    display.setBrightness(100);
  } else {
    display.setBrightness(0);
  } 
}