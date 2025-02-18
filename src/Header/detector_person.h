#ifndef DETECTOR_PERSON_H
#define DETECTOR_PERSON_H

#include "typeDef.h"

// Variable externe pour la détection de la présence
extern bool isAnyoneNearby;

/**
 * @brief Setup the captor(BS312).
 * 
 * @author Enzo Biguet
 * @return void
 */
void voidSetupDetectorPerson();

/**
 * @brief Setup the Screen brightness (BS312).
 * 
 * This function change the brightness's screen.
 * 
 * @author Enzo Biguet
 * @return void
 */
void isAnyoneHere(void *pvParameters);

#endif // DETECTOR_PERSON_H