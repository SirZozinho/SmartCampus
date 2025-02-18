#include <Arduino.h>
#include <unity.h>
#include <DHTesp.h>
#include <Wire.h>
#include <SPI.h>

DHTesp dht;

void test_dht_sensor_responding() {
    float temperature = dht.getTemperature();
    float humidity = dht.getHumidity();

    TEST_ASSERT_FALSE(isnan(temperature));
    TEST_ASSERT_FALSE(isnan(humidity));

}

void setup() {
    UNITY_BEGIN(); // Commence le test

    // Initialisation du capteur
    dht.setup(21, DHTesp::DHT22); // Remplace par DHTesp::DHT11 si nécessaire
    delay(2000); // Laisse le capteur se stabiliser

    // Test
    RUN_TEST(test_dht_sensor_responding);

    UNITY_END(); // Termine le test
}

void loop() {
    // Vide, les tests s'exécutent dans `setup()`.
}