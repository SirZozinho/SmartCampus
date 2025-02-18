#ifndef CO2_H
#define CO2_H

#include <sgp30.h>

//Main variables
extern u16 CO2,TVOC;
extern s16 err;

/**
 * @brief Setup the CO2 sensor (SGP30).
 * 
 * This function initializes the SGP30 CO2 sensor, ensuring it's ready for
 * use and configuring any necessary communication protocols.
 * 
 * @author Clément Muzelier
 * @return void
 */
void setupCo2();

/**
 * @brief Get the CO2 concentration value from the SGP30 sensor.
 * 
 * This function reads the current CO2 concentration from the SGP30 sensor and 
 * set the value along with any errors encountered during the reading in the main variables
 * 
 * @author Clément Muzelier
 * @return void
 */
void getCo2(void *pvParameters);

#endif // CO2_H