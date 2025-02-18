#include "./Header/co2_captor.h"
#include "./Header/typeDef.h"

u16 CO2,TVOC = 0;
s16 err;

void setupCo2()
{ 
    u16 scaled_ethanol_signal, scaled_h2_signal;

    while (sgp_probe() != STATUS_OK) {
            Serial.println("Erreur : SGP30 non détecté !");
    }

    // Lire les signaux initiaux H2 et Ethanol
    err = sgp_measure_signals_blocking_read(&scaled_ethanol_signal, &scaled_h2_signal);
    if (err == STATUS_OK) {
        Serial.println("Signaux initiaux lus !");
    } else {
        Serial.println("Erreur : Lecture des signaux !");
    }

    // Initialiser les mesures de qualité de l'air
    err = sgp_iaq_init();
    if (err != STATUS_OK) {
        Serial.println("Erreur : Impossible d'initialiser IAQ !");
        while (1);
    }
}

void getCo2(void *pvParameters)
{
  for (;;) {
    //Reads data from CO2 sensor 
    err = sgp_measure_iaq_blocking_read(&TVOC, &CO2);
    if (err == STATUS_OK) {
        //Prints the CO2 if status of sensor is ok
        Serial.print("[CO2Task]: CO2:\t");
        Serial.print(CO2);
        Serial.println(" PPM");
    } else {
        //Prints error when CO2 sensor is not responding
        Serial.println("Erreur : Lecture des valeurs IAQ !");
    }

    //Update delay
    delay(10000);
  }
}