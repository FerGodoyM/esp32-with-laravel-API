<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\SensorData;

class PanelController extends Controller
{
    /**
     * Muestra el panel de control
     */
    public function index()
    {
        // Obtener estado actual del cache
        $estado = Cache::get('esp32_estado', [
            'led' => false,
            'buzzer' => false,
            'mensaje' => 'Hola desde Laravel!'
        ]);

        // Obtener últimos datos de sensores
        $sensores = Cache::get('esp32_sensores', [
            'temp' => 0,
            'hum' => 0,
            'ldr' => 0
        ]);

        // Obtener historial de la BD (últimos 10)
        $historial = SensorData::latest()->take(10)->get();

        // Obtener estadísticas
        $estadisticas = $this->calcularEstadisticas();

        return view('panel', compact('estado', 'sensores', 'historial', 'estadisticas'));
    }

    /**
     * Actualiza el estado (LED, buzzer, mensaje)
     */
    public function update(Request $request)
    {
        $estado = [
            'led' => $request->has('led'),
            'buzzer' => $request->has('buzzer'),
            'mensaje' => $request->input('mensaje', 'Hola desde Laravel!')
        ];

        // Guardar en cache por 1 hora
        Cache::put('esp32_estado', $estado, 3600);

        return redirect('/')->with('success', '¡Estado actualizado!');
    }


    public function calcularEstadisticas()
    {
        $estadisticas = SensorData::select(
            DB::raw('AVG(temperatura) as avg_temp'),
            DB::raw('MAX(temperatura) as max_temp'),
            DB::raw('MIN(temperatura) as min_temp')
        )->first();

        return $estadisticas;
    }
}
