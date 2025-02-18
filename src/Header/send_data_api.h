#ifndef SEND_DATA_API_H
#define SEND_DATA_API_H

#include "typeDef.h"

#include "./Header/temp_hum_captor.h"
#include "./Header/co2_captor.h"
#include "./Header/time.h"

#include <HTTPClient.h>
#include <queue>

// Structure for a capture
typedef struct {
    const String name;       // Name of the measurement type (e.g., "temperature", "humidity")
    float value;             // Value of the measurement (e.g., 22.5 for temperature)
    const String desc;       // Description of the measurement (e.g., "Ambient temperature measurement")
    const String SAName;     // Name of the acquisition system/ESP32 (e.g., "ESP-006")
    const String date;       // Date of the measurement (e.g., "2025-01-13 14:30:15")
} Capture;

/**
 * @author Léonard Lardeux
 * @brief Sends the sensor data to the API.
 *
 * @param force Specifies whether to force the data transmission.
 * This function sends temperature, humidity, and CO2 data through separate POST requests.
 */
void sendToAPI(void *pvParameters);

/**
 * @author Léonard Lardeux
 * @brief Sends a POST request to the API with the specified data.
 *
 * @param capture The capture data to send, including name, value, description, system name, and date.
 */
void sendPostRequest(Capture& capture);

/**
 * @author Léonard Lardeux
 * @brief Processes the queue of pending captures and sends them to the API.
 *
 * This function ensures all pending measurements are sent to the API if the internet is available.
 */
void processQueue();

/**
 * @author Léonard Lardeux
 * @brief Adds a capture to the queue for later processing.
 *
 * @param capture The capture data to add to the queue.
 */
void addToQueue(Capture& capture);

/**
 * @author Léonard Lardeux
 * @brief Checks if an internet connection is available.
 *
 * @return true if an internet connection is available, otherwise false.
 */
bool isInternetAvailable();

#endif // SEND_DATA_API_H
