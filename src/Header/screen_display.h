#ifndef SCREEN_H
#define SCREEN_H

#include "typeDef.h"
#include <SSD1306Wire.h>


/**
 * @brief Setup the Screen Component (SSD1306Wire).
 * 
 * This function initializes screen, ensuring it's ready for
 * use and configuring any necessary communication protocols.
 * 
 * @author Enzo Biguet
 * @return void
 */
void setupScreen();

/**
 * @brief display the screen configuration from to the SSD1306Wire screen.
 * 
 * This function reads  and display the current screen configuration to the SSD1306Wire screen.
 * 
 * @author Enzo Biguet
 * @return void
 */
void displayScreen(void *pvParameters);

/**
 * @brief Change the brighness of the screen.
 * 
 * This function reads the variable of the presence and configure the brightness's screen.
 * 
 * @author Enzo Biguet
 * @return void
 */
void changeBrightness();

#endif // TEMP_HUM