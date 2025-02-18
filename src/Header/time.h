#ifndef TIME_SC_H
#define TIME_SC_H

#include <SPI.h>
#include "./Header/wifi_connection.h"

//Main variable
extern String currentTime;

/**
 * @brief Setup the time on the DS1307.
 * 
 * This function initializes date and hour of DS1307 with NTP server.
 * 
 * @author Léonard Lardeux
 * @return void
 */
void setupTime();

/**
 * @brief Get and display current time.
 * 
 * This function reads the current time value on DS1307, get and display current time. 
 * 
 * @author Léonard Lardeux
 * @return void
 */
void getTime(void *pvParameters);

#endif // TIME_SC_H