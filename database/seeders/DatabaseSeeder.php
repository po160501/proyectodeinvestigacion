<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Sensor;
use App\Models\MedicionRuido;
use App\Models\Alerta;
use App\Models\Trabajador;
use App\Models\ExposicionRuido;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Usuarios
        User::updateOrCreate(['email' => 'admin@soundguard.com'], [
            'name' => 'Administrador', 'password' => Hash::make('admin123'), 'rol' => 'admin',
        ]);
        User::updateOrCreate(['email' => 'operador@soundguard.com'], [
            'name' => 'Carlos Operador', 'password' => Hash::make('oper123'), 'rol' => 'operador',
        ]);

        // Sensores
        $sensores = [
            ['nombre' => 'Sensor-01', 'ubicacion' => 'Área de Producción A', 'estado' => 'activo',  'nivel_actual' => 78.5],
            ['nombre' => 'Sensor-02', 'ubicacion' => 'Área de Producción B', 'estado' => 'activo',  'nivel_actual' => 91.2],
            ['nombre' => 'Sensor-03', 'ubicacion' => 'Almacén Central',      'estado' => 'activo',  'nivel_actual' => 65.0],
            ['nombre' => 'Sensor-04', 'ubicacion' => 'Zona de Maquinaria',   'estado' => 'activo',  'nivel_actual' => 88.7],
        ];
        foreach ($sensores as $s) {
            Sensor::firstOrCreate(['nombre' => $s['nombre']], $s);
        }

        // Trabajadores
        $trabajadores = [
            ['nombre' => 'Juan Pérez',    'empresa' => 'Industrias Trujillo', 'area' => 'Producción A'],
            ['nombre' => 'María López',   'empresa' => 'Industrias Trujillo', 'area' => 'Producción B'],
            ['nombre' => 'Carlos Ramos',  'empresa' => 'Industrias Trujillo', 'area' => 'Maquinaria'],
            ['nombre' => 'Ana Torres',    'empresa' => 'Industrias Trujillo', 'area' => 'Almacén'],
            ['nombre' => 'Luis Mendoza',  'empresa' => 'Industrias Trujillo', 'area' => 'Producción A'],
        ];
        foreach ($trabajadores as $t) {
            Trabajador::firstOrCreate(['nombre' => $t['nombre']], $t);
        }

        $sensorIds    = Sensor::pluck('id')->toArray();
        $trabajadores = Trabajador::all();

        // Mediciones últimos 7 días
        for ($d = 6; $d >= 0; $d--) {
            $fecha = Carbon::today()->subDays($d);
            foreach (range(7, 17) as $hora) {
                foreach ($sensorIds as $sid) {
                    $db = round(rand(60, 105) + (rand(0, 10) / 10), 1);
                    MedicionRuido::create([
                        'sensor_id' => $sid,
                        'decibeles' => $db,
                        'fecha'     => $fecha->toDateString(),
                        'hora'      => sprintf('%02d:%02d:00', $hora, rand(0, 59)),
                    ]);
                    if ($db >= 85) {
                        Alerta::create([
                            'sensor_id'   => $sid,
                            'nivel_ruido' => $db,
                            'fecha'       => $fecha->toDateString(),
                            'hora'        => sprintf('%02d:%02d:00', $hora, rand(0, 59)),
                            'estado'      => $d > 0 ? 'resuelta' : 'activa',
                        ]);
                    }
                }
            }
        }

        // Exposiciones hoy
        foreach ($trabajadores as $t) {
            $minutos = rand(20, 70);
            $inicio  = Carbon::today()->setHour(8)->setMinute(0);
            ExposicionRuido::create([
                'trabajador_id'    => $t->id,
                'hora_inicio'      => $inicio->toTimeString(),
                'hora_fin'         => $inicio->copy()->addMinutes($minutos)->toTimeString(),
                'tiempo_exposicion'=> $minutos,
                'decibeles'        => round(rand(75, 100) + rand(0, 9) / 10, 1),
                'fecha'            => Carbon::today()->toDateString(),
            ]);
        }
    }
}
