#include <Arduino.h>
#include <Wire.h>
#include <SSD1306Wire.h> // Bibliothèque pour l'écran OLED SSD1306

#include "sensirion_common.h"
#include "sgp30.h"
#define SDA_PIN  8
#define SCL_PIN 9

// Définir les broches I2C pour ESP32


// Adresse de l'écran OLED
#define OLED_ADDR 0x3C
SSD1306Wire display(OLED_ADDR, SDA_PIN, SCL_PIN); // Initialiser l'écran OLED

void setup() {
    s16 err;
    u16 scaled_ethanol_signal, scaled_h2_signal;

    // Initialisation série pour le débogage
    Serial.begin(115200);
    Serial.println("Démarrage série...");

    // Initialiser l'I2C avec les broches personnalisées
    Wire.begin(SDA_PIN, SCL_PIN);

    // Initialiser l'écran OLED
    display.init();
    display.flipScreenVertically();
    display.setFont(ArialMT_Plain_10);
    display.clear();
    display.drawString(0, 0, "Initialisation...");
    display.display();

    // Initialiser le capteur SGP30
    while (sgp_probe() != STATUS_OK) {
        Serial.println("Erreur : SGP30 non détecté !");
        display.clear();
        display.drawString(0, 20, "Erreur : Capteur !");
        display.display();
        delay(1000);
    }
    Serial.println("Capteur SGP30 détecté !");
    display.clear();
    display.drawString(0, 0, "Capteur SGP30 OK");
    display.display();

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
        display.clear();
        display.drawString(0, 0, "Erreur : Init IAQ !");
        display.display();
        while (1);
    }

    // Message d'initialisation réussie
    display.clear();
    display.drawString(0, 0, "Initialisation terminee");
    display.display();
    delay(2000);
}

void loop() {
    s16 err = 0;
    u16 tvoc_ppb, co2_eq_ppm;

    // Lire les données de CO₂ et TVOC
    err = sgp_measure_iaq_blocking_read(&tvoc_ppb, &co2_eq_ppm);
    if (err == STATUS_OK) {
        // Afficher dans le terminal série
        Serial.print("tVOC Concentration : ");
        Serial.print(tvoc_ppb);
        Serial.println(" ppb");

        Serial.print("CO2eq Concentration : ");
        Serial.print(co2_eq_ppm);
        Serial.println(" ppm");

        // Afficher sur l'écran OLED
        display.clear();
        display.setTextAlignment(TEXT_ALIGN_LEFT);
        display.setFont(ArialMT_Plain_16);

        // Ligne 1 : Titre
        display.drawString(0, 0, "Qualite de l'air");

        // Ligne 2 : CO2
        //display.setFont(ArialMT_Plain_24);
        display.drawString(0, 20, "CO2: " + String(co2_eq_ppm) + " ppm");

        // Ligne 3 : TVOC
        //display.setFont(ArialMT_Plain_16);
        //display.drawString(0, 50, "TVOC: " + String(tvoc_ppb) + " ppb");

        // Actualiser l'écran
        display.display();
    } else {
        Serial.println("Erreur : Lecture des valeurs IAQ !");
        display.clear();
        display.drawString(0, 20, "Erreur : Lecture !");
        display.display();
    }

    // Pause de 1 seconde
    delay(1000);
}
