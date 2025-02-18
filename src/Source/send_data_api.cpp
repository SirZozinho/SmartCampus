#include "./Header/send_data_api.h"

// API configuration
const char* serverLink = "https://sae34.k8s.iut-larochelle.fr/api/captures";

// Database access information
const char* dbname = "sae34bdl2eq3";
const char* username = "l2eq3";
const char* userpass = "jobjih-Nizwe9-xusvyv";

// Variables for timing
unsigned long lastTime = 0;
unsigned long timerDelay = 60000 * 10; // Delay of 10 minute between sends

// Queue for captures std::queue
std::queue<Capture> captureQueue;

// Add a capture to the queue
void addToQueue(Capture& typeCapture) {
    captureQueue.push(typeCapture);
}

// Send a POST request and add to queue if it fails or WiFi is disconnected
void sendPostRequest(Capture& typeCapture) {
    bool requestFailed = false;

    // Check WiFi connection before sending the request
    if (isInternetAvailable()) {
        HTTPClient http;
        http.begin(serverLink);

        http.addHeader("Content-Type", "application/ld+json");
        http.addHeader("dbname", dbname);
        http.addHeader("username", username);
        http.addHeader("userpass", userpass);

        String httpRequestData = "{";
        httpRequestData += "\"nom\": \"" + typeCapture.name + "\",";
        httpRequestData += "\"valeur\": \"" + String(typeCapture.value) + "\",";
        httpRequestData += "\"dateCapture\": \"" + typeCapture.date + "\",";
        httpRequestData += "\"localisation\": \"D002\",";
        httpRequestData += "\"description\": \"" + typeCapture.desc + "\",";
        httpRequestData += "\"nomsa\": \"" + typeCapture.SAName + "\"";
        httpRequestData += "}";

        int httpResponseCode = http.POST(httpRequestData);

        Serial.print("\n HTTP Response code: ");
        Serial.println(httpResponseCode);

        if (httpResponseCode > 0) {
            String response = http.getString();
            Serial.println("Response from server: ");
            Serial.println(response);
            Serial.print("Data sent successfully");
        } else {
            // Mark as failed if the request fails
            Serial.print("Error on sending POST: ");
            Serial.println(http.errorToString(httpResponseCode).c_str());
            requestFailed = true;
        }

        http.end();
    } else {
        // If WiFi is disconnected
        Serial.println("Internet unavailable");
        requestFailed = true;
    }

    // Add to the queue if the request fails or the ESP is disconnected
    if (requestFailed) {
        addToQueue(typeCapture);
        Serial.print("Data added to queue");
    }
}

// Process the queue and send data if connected
void processQueue() {
    // If WiFi is connected, send the pending data from the queue
    if (isInternetAvailable()) {
        while (!captureQueue.empty()) {
            sendPostRequest(captureQueue.front());
            captureQueue.pop();
        }
    }
}

// Send data to the API at regular intervals
void sendToAPI(void *pvParameters) {
    for (;;) {
        if ((millis() - lastTime) > timerDelay) {
            Serial.print("Sending data...");

            Capture tempCapture = {"temp", temperature, "Ambient temperature capture", "ESP-028", currentTime};
            Capture humCapture = {"hum", humidity, "Ambient humidity capture", "ESP-028", currentTime};
            Capture co2Capture = {"co2", float(CO2), "Ambient CO2 capture", "ESP-028", currentTime};

            // Validate data before sending
            if (currentTime == "Error" || currentTime.isEmpty() || isnan(temperature) || isnan(humidity) || CO2 <= 0) {
                Serial.println("Send failed: Issue detected.");
                return; // Exit the function if any condition fails
            }

            // Attempt to send data and add to the queue if it fails
            sendPostRequest(tempCapture);
            sendPostRequest(humCapture);
            sendPostRequest(co2Capture);

            // Process the queue
            processQueue();

            lastTime = millis();
        }
        delay(1000);
    }
}

// Check if an internet connection is available
bool isInternetAvailable() {
    WiFiClient client;
    const char* host = "google.com";
    const uint16_t port = 80; // Standard HTTP port

    // Attempt to connect to google.com
    if (client.connect(host, port)) {
        client.stop(); // Close the connection if successful
        return true;   // Internet access is available
    } else {
        return false;  // Internet access is unavailable
    }
}
