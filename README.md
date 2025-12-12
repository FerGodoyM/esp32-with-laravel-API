# ESP32 con Laravel API

Sistema IoT completo que integra un ESP32 con sensores (DHT11, LDR) y una API Laravel para control y monitoreo en tiempo real.

## 📋 Descripción

Este proyecto permite:
- **ESP32**: Lee temperatura, humedad y luz, y se sincroniza con Laravel cada 2 segundos
- **Laravel API**: Recibe datos de sensores, almacena historial en base de datos, y envía comandos al ESP32
- **Panel Web**: Interfaz para visualizar datos en tiempo real y controlar LED/buzzer del ESP32

## 🛠️ Requisitos Previos

### Para el ESP32
- Arduino IDE (versión 1.8 o superior)
- Placa ESP32 (ESP32-WROOM-32 o similar)
- Bibliotecas Arduino:
  - `WiFi.h`
  - `HTTPClient.h`
  - `ArduinoJson` (versión 6.x)
  - `Adafruit_GFX`
  - `Adafruit_SSD1306`
  - `DHT sensor library`

### Para Laravel
- PHP 8.1 o superior
- Composer
- XAMPP (o cualquier servidor con Apache/MySQL)
- SQLite o MySQL

## 📁 Estructura del Proyecto

```
esp32-with-laravel-API/
├── programacion_esp32/          # Código del ESP32
│   └── programacion_esp32.ino   # Sketch principal
├── laravel-api/                 # API Laravel
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── Esp32Controller.php    # Endpoint de sincronización
│   │   │   └── PanelController.php    # Panel web
│   │   └── Models/
│   │       └── SensorData.php         # Modelo de datos
│   ├── database/
│   │   └── migrations/                # Migraciones de BD
│   ├── routes/
│   │   ├── api.php                    # Rutas API
│   │   └── web.php                    # Rutas web
│   └── resources/views/
│       └── panel.blade.php            # Vista del panel
└── README.md
```

## 🚀 Instalación

### 1. Configurar Laravel

```bash
# Navegar a la carpeta del proyecto Laravel
cd laravel-api

# Instalar dependencias
composer install

# Copiar archivo de configuración
cp .env.example .env

# Generar clave de aplicación
php artisan key:generate

# Ejecutar migraciones
php artisan migrate

# Iniciar servidor
php artisan serve
```

El servidor estará disponible en `http://localhost:8000`

### 2. Configurar ESP32

1. Abre `programacion_esp32/programacion_esp32.ino` en Arduino IDE

2. **Configura tus credenciales WiFi** (líneas 10-11):
   ```cpp
   const char* ssid = "TU_WIFI";
   const char* password = "TU_PASSWORD";
   ```

3. **Configura la URL del servidor** (línea 14):
   - Encuentra la IP local de tu PC:
     - Windows: `ipconfig` (busca "Dirección IPv4")
     - Mac/Linux: `ifconfig` o `ip addr`
   - Actualiza la URL:
   ```cpp
   const char* serverUrl = "http://TU_IP_LOCAL:8000/api/esp32/sync";
   // Ejemplo: "http://192.168.1.101:8000/api/esp32/sync"
   ```

4. **Instala las bibliotecas necesarias** en Arduino IDE:
   - Ve a `Sketch > Include Library > Manage Libraries`
   - Busca e instala:
     - ArduinoJson (by Benoit Blanchon)
     - Adafruit GFX Library
     - Adafruit SSD1306
     - DHT sensor library (by Adafruit)

5. **Sube el código al ESP32**:
   - Conecta el ESP32 por USB
   - Selecciona la placa: `Tools > Board > ESP32 Dev Module`
   - Selecciona el puerto: `Tools > Port > COMx` (Windows) o `/dev/ttyUSBx` (Linux)
   - Haz clic en "Upload"

### 3. Conexiones del Hardware

```
ESP32 Pinout:
├── DHT11 Sensor
│   ├── VCC → 3.3V
│   ├── DATA → GPIO 4
│   └── GND → GND
├── OLED Display (I2C)
│   ├── VCC → 3.3V
│   ├── GND → GND
│   ├── SDA → GPIO 22
│   └── SCL → GPIO 21
├── LDR (Fotoresistor)
│   ├── Un extremo → 3.3V
│   ├── Otro extremo → GPIO 34 + Resistencia 10kΩ a GND
├── LED
│   ├── Ánodo (+) → GPIO 2 + Resistencia 220Ω
│   └── Cátodo (-) → GND
└── Buzzer
    ├── Positivo → GPIO 25
    └── Negativo → GND
```

## 🌐 Endpoints de la API

### POST `/api/esp32/sync`
Sincroniza datos entre ESP32 y Laravel.

**Request (desde ESP32):**
```json
{
  "temp": 25.5,
  "hum": 60.0,
  "ldr": 1024
}
```

**Response (a ESP32):**
```json
{
  "led": true,
  "buzzer": false,
  "mensaje": "Hola desde Laravel!"
}
```

### GET `/` (Panel Web)
Muestra el panel de control con:
- Datos en tiempo real de sensores
- Controles para LED y buzzer
- Campo de mensaje personalizado
- Estadísticas (promedio, máximo, mínimo de temperatura)

### POST `/panel/actualizar`
Actualiza el estado del ESP32 desde el panel web.

**Request:**
```
led: on/off
buzzer: on/off
mensaje: "Tu mensaje"
```

## 💡 Uso

1. **Inicia el servidor Laravel**:
   ```bash
   cd laravel-api
   php artisan serve
   ```

2. **Enciende el ESP32** (debe estar programado y conectado al WiFi)

3. **Abre el panel web** en tu navegador:
   ```
   http://localhost:8000
   ```

4. **Interactúa con el sistema**:
   - El ESP32 enviará datos cada 2 segundos
   - Usa el panel web para controlar el LED y buzzer
   - Los datos se guardan automáticamente en la base de datos

## 📊 Base de Datos

La tabla `sensor_data` almacena:
- `id`: ID único
- `temperatura`: Temperatura en °C
- `humedad`: Humedad relativa en %
- `ldr`: Valor del sensor de luz (0-4095)
- `mensaje`: Mensaje enviado al ESP32
- `created_at`: Timestamp de creación

## 🔧 Troubleshooting

### ESP32 no se conecta al WiFi
- Verifica las credenciales WiFi en el código
- Asegúrate de que el ESP32 esté en rango del router
- Revisa el monitor serial (115200 baud) para ver mensajes de error

### Error de conexión HTTP
- Verifica que la IP en `serverUrl` sea correcta
- Asegúrate de que el servidor Laravel esté corriendo
- Verifica que el firewall no bloquee el puerto 8000
- El ESP32 y la PC deben estar en la misma red

### Panel web no muestra datos
- Verifica que el ESP32 esté enviando datos (revisa el monitor serial)
- Refresca la página del panel
- Revisa los logs de Laravel: `laravel-api/storage/logs/laravel.log`

### Errores de permisos en Laravel
```bash
# En Windows (desde la carpeta laravel-api)
icacls storage /grant Everyone:F /t
icacls bootstrap/cache /grant Everyone:F /t
```

## 📝 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 👨‍💻 Autor

Desarrollado como proyecto IoT con ESP32 y Laravel.

---

**¿Necesitas ayuda?** Revisa la sección de Troubleshooting o abre un issue en GitHub.
