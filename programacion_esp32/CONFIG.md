# Configuración del ESP32

## Credenciales WiFi

Antes de subir el código al ESP32, debes configurar tus credenciales WiFi en el archivo `programacion_esp32.ino`:

```cpp
// Líneas 10-11
const char* ssid = "TU_NOMBRE_WIFI";          // Reemplaza con el nombre de tu red WiFi
const char* password = "TU_CONTRASEÑA_WIFI";  // Reemplaza con tu contraseña WiFi
```

## URL del Servidor Laravel

También debes configurar la IP de tu PC donde corre el servidor Laravel:

```cpp
// Línea 14
const char* serverUrl = "http://192.168.1.XXX:8000/api/esp32/sync";
```

### ¿Cómo encontrar tu IP local?

**Windows:**
```bash
ipconfig
```
Busca "Dirección IPv4" en la sección de tu adaptador WiFi/Ethernet.

**Mac/Linux:**
```bash
ifconfig
# o
ip addr
```

### Ejemplo de configuración:

Si tu IP es `192.168.1.105`, la URL sería:
```cpp
const char* serverUrl = "http://192.168.1.105:8000/api/esp32/sync";
```

## ⚠️ Importante

**NO subas tus credenciales reales al repositorio Git.** El archivo `.ino` en el repositorio debe mantener los valores de ejemplo (`TU_NOMBRE_WIFI`, `TU_CONTRASEÑA_WIFI`, etc.).

Mantén tus credenciales reales solo en tu copia local del archivo.
