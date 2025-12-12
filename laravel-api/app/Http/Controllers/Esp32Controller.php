<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\SensorData;

class Esp32Controller extends Controller
{
    /**
     * Sincroniza datos con el ESP32.
     * Recibe: temp, hum, ldr
     * Responde: led, buzzer, mensaje (desde el panel web)
     */
    public function sync(Request $request)
    {
        // 1. Recibir datos del ESP32
        $temperatura = $request->input('temp');
        $humedad = $request->input('hum');
        $luz = $request->input('ldr');

        // 2. Guardar datos de sensores en cache (para mostrar en el panel)
        Cache::put('esp32_sensores', [
            'temp' => $temperatura,
            'hum' => $humedad,
            'ldr' => $luz
        ], 3600);

        // Mostrar en consola de Laravel (para debug)
        logger("ESP32 -> Temp: $temperatura, Hum: $humedad, Luz: $luz");

        // 3. Obtener estado del panel (LED, buzzer, mensaje)
        $estado = Cache::get('esp32_estado', [
            'led' => false,
            'buzzer' => false,
            'mensaje' => 'Hola desde Laravel!'
        ]);

        // --- Guardar HISTORIAL en Base de Datos ---
        SensorData::create([
            'temperatura' => $temperatura ?? 0,
            'humedad' => $humedad ?? 0,
            'ldr' => $luz ?? 0,
            'mensaje' => $estado['mensaje']
        ]);

        // 4. Retornar respuesta JSON al ESP32
        return response()->json([
            'led' => $estado['led'],
            'buzzer' => $estado['buzzer'],
            'mensaje' => $estado['mensaje']
        ]);
    }
}
