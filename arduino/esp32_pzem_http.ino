/*
 * ESP32 + PZEM-004T → HTTP POST → MySQL (via PHP)
 * 
 * Library yang dibutuhkan:
 *  - PZEM004Tv30  (by Jakub Mandula)
 *  - WiFi         (built-in ESP32)
 *  - HTTPClient   (built-in ESP32)
 *
 * Ganti SERVER_HOST dengan IP / domain server PHP kamu.
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <PZEM004Tv30.h>

// ── WiFi ─────────────────────────────────────────────────────────────────────
const char* ssid = "Juanda Ibrahim";
const char* password = "12345678";

// ── Server PHP ────────────────────────────────────────────────────────────────
// Contoh lokal  : "http://192.168.1.100/pzem/insert.php"
// Contoh hosting: "http://namadomain.com/pzem/insert.php"
const char* serverURL = "https://simtad.my.id/insert.php";

// ── PZEM (RX=27, TX=26, via Serial2) ─────────────────────────────────────────
PZEM004Tv30 pzem(Serial2, 27, 26);

unsigned long lastSend = 0;
const unsigned long INTERVAL = 5000; // kirim tiap 5 detik

// ─────────────────────────────────────────────────────────────────────────────
void setup() {
  Serial.begin(115200);
  setup_wifi();
}

// ─────────────────────────────────────────────────────────────────────────────
void setup_wifi() {
  Serial.printf("\nMenghubungkan ke %s", ssid);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.printf("\nWiFi terhubung – IP: %s\n", WiFi.localIP().toString().c_str());
}

// ─────────────────────────────────────────────────────────────────────────────
void loop() {
  unsigned long now = millis();
  if (now - lastSend < INTERVAL) return;
  lastSend = now;

  // Baca sensor
  float voltage = pzem.voltage();
  float current = pzem.current();
  float power   = pzem.power();

  if (isnan(voltage) || isnan(current) || isnan(power)) {
    Serial.println("[PZEM] Gagal membaca sensor");
    return;
  }

  Serial.printf("[PZEM] V=%.2fV  I=%.3fA  P=%.2fW\n", voltage, current, power);

  // Pastikan WiFi masih terhubung
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("[WiFi] Terputus, reconnect...");
    setup_wifi();
    return;
  }

  // Kirim HTTP POST
  HTTPClient http;
  http.begin(serverURL);
  http.addHeader("Content-Type", "application/x-www-form-urlencoded");

  // Body: key=value&key=value
  String body = "tegangan=" + String(voltage, 2) +
                "&arus="     + String(current, 3) +
                "&daya="     + String(power,   2);

  int httpCode = http.POST(body);

  if (httpCode > 0) {
    Serial.printf("[HTTP] Respon %d: %s\n", httpCode, http.getString().c_str());
  } else {
    Serial.printf("[HTTP] Gagal: %s\n", http.errorToString(httpCode).c_str());
  }

  http.end();
}
