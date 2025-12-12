<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32 Control Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            color: #fff;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.5rem;
            background: linear-gradient(90deg, #00d9ff, #00ff88);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card h2 {
            margin-bottom: 20px;
            font-size: 1.3rem;
            color: #00d9ff;
        }

        .success-message {
            background: rgba(0, 255, 136, 0.2);
            border: 1px solid #00ff88;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }

        /* Sensores Grid */
        .sensors-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }

        .sensor-item {
            background: rgba(0, 217, 255, 0.1);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }

        .sensor-value {
            font-size: 2rem;
            font-weight: bold;
            color: #00ff88;
        }

        .sensor-label {
            font-size: 0.9rem;
            color: #aaa;
            margin-top: 5px;
        }

        /* Controls */
        .control-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .control-group:last-child {
            border-bottom: none;
        }

        .control-label {
            font-size: 1.1rem;
        }

        /* Toggle Switch */
        .toggle {
            position: relative;
            width: 60px;
            height: 32px;
        }

        .toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #333;
            transition: 0.3s;
            border-radius: 32px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 24px;
            width: 24px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }

        .toggle input:checked + .toggle-slider {
            background: linear-gradient(90deg, #00d9ff, #00ff88);
        }

        .toggle input:checked + .toggle-slider:before {
            transform: translateX(28px);
        }

        /* Message Input */
        .message-input {
            width: 100%;
            padding: 15px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            font-size: 1rem;
            margin-top: 10px;
            transition: border-color 0.3s;
        }

        .message-input:focus {
            outline: none;
            border-color: #00d9ff;
        }

        .message-input::placeholder {
            color: #888;
        }

        /* Submit Button */
        .btn-submit {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(90deg, #00d9ff, #00ff88);
            color: #1a1a2e;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0, 217, 255, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Status Indicator */
        .status-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 2s infinite;
        }

        .status-dot.on {
            background: #00ff88;
        }

        .status-dot.off {
            background: #ff4757;
            animation: none;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Responsive */
        @media (max-width: 600px) {
            .sensors-grid {
                grid-template-columns: 1fr;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }

        /* Tabla Historial */
        table {
            width: 100%;
            border-collapse: collapse;
            color: #eee;
        }
        th, td {
            text-align: left;
            padding: 12px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        th {
            color: #00d9ff;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎛️ ESP32 Control Panel</h1>

        @if(session('success'))
            <div class="success-message">
                ✅ {{ session('success') }}
            </div>
        @endif

        <!-- Sensores -->
        <div class="card">
            <h2>📊 Datos de Sensores</h2>
            <div class="sensors-grid">
                <div class="sensor-item">
                    <div class="sensor-value">{{ $sensores['temp'] }}°C</div>
                    <div class="sensor-label">Temperatura</div>
                </div>
                <div class="sensor-item">
                    <div class="sensor-value">{{ $sensores['hum'] }}%</div>
                    <div class="sensor-label">Humedad</div>
                </div>
                <div class="sensor-item">
                    <div class="sensor-value">{{ $sensores['ldr'] }}</div>
                    <div class="sensor-label">Luz (LDR)</div>
                </div>
            </div>
        </div>

        <!-- Estadísticas -->
        @if($estadisticas)
        <div class="card">
            <h2>📈 Estadísticas de Temperatura</h2>
            <div class="sensors-grid">
                <div class="sensor-item">
                    <div class="sensor-value">{{ number_format($estadisticas->avg_temp ?? 0, 1) }}°C</div>
                    <div class="sensor-label">Promedio</div>
                </div>
                <div class="sensor-item">
                    <div class="sensor-value">{{ number_format($estadisticas->max_temp ?? 0, 1) }}°C</div>
                    <div class="sensor-label">Máxima</div>
                </div>
                <div class="sensor-item">
                    <div class="sensor-value">{{ number_format($estadisticas->min_temp ?? 0, 1) }}°C</div>
                    <div class="sensor-label">Mínima</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Controles -->
        <div class="card">
            <h2>🕹️ Controles</h2>
            <form action="/" method="POST">
                @csrf
                
                <div class="control-group">
                    <span class="control-label">
                        <span class="status-dot {{ $estado['led'] ? 'on' : 'off' }}"></span>
                        💡 LED
                    </span>
                    <label class="toggle">
                        <input type="checkbox" name="led" {{ $estado['led'] ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="control-group">
                    <span class="control-label">
                        <span class="status-dot {{ $estado['buzzer'] ? 'on' : 'off' }}"></span>
                        🔔 Buzzer
                    </span>
                    <label class="toggle">
                        <input type="checkbox" name="buzzer" {{ $estado['buzzer'] ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="control-group" style="flex-direction: column; align-items: stretch;">
                    <span class="control-label">📝 Mensaje para pantalla OLED</span>
                    <input 
                        type="text" 
                        name="mensaje" 
                        class="message-input" 
                        placeholder="Escribe un mensaje..."
                        value="{{ $estado['mensaje'] }}"
                        maxlength="50"
                    >
                </div>

                <button type="submit" class="btn-submit">
                    🚀 Enviar al ESP32
                </button>
            </form>
        </div>

        <!-- Historial -->
        <div class="card">
            <h2>📜 Historial Reciente</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Hora</th>
                            <th>Temp</th>
                            <th>Hum</th>
                            <th>Luz</th>
                            <th>Mensaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($historial as $dato)
                        <tr>
                            <td>{{ $dato->created_at->diffForHumans() }}</td>
                            <td>{{ $dato->temperatura }}°C</td>
                            <td>{{ $dato->humedad }}%</td>
                            <td>{{ $dato->ldr }}</td>
                            <td>{{ $dato->mensaje }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <p style="text-align: center; color: #666; margin-top: 20px;">
            El ESP32 recibirá estos cambios en su próxima sincronización (cada 2 segundos)
        </p>
    </div>
</body>
</html>
