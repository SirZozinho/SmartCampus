#ifndef TEMP_HUM_H
#define TEMP_HUM_H

#include <DHTesp.h>

//Main variables
extern float temperature;
extern float humidity;

//Components classes
extern DHTesp dht;

/**
 * @brief Setup the temp and hum sensor (DHT22).
 * 
 * This function initializes the DHT22 temperature and humidity sensor, ensuring it's ready for
 * use and configuring any necessary communication protocols.
 * 
 * @author Louis Paquereau
 * @return void
 */
void setupTemp_Hum();

/**
 * @brief Get the temp and hum concentration value from the DHT22 sensor.
 * 
 * This function reads the current temperature and humidity concentration from the DHT22 sensor and 
 * set the value along with any errors encountered during the reading in the main variables
 * 
 * @author Louis Paquereau
 * @return void
 */
void getTemp_Hum(void *pvParameters);

#endif // TEMP_HUM