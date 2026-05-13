<?php

namespace Database\Seeders;

use App\Models\User;
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

        $trabajadores = Trabajador::all();

        // Alertas de ejemplo hoy
        foreach ($trabajadores as $t) {
            if (rand(0, 1)) {
                Alerta::create([
                    'trabajador_id' => $t->id,
                    'nivel_ruido'   => rand(86, 100),
                    'fecha'         => Carbon::today()->toDateString(),
                    'hora'          => Carbon::now()->subMinutes(rand(10, 120))->format('H:i:s'),
                    'estado'        => 'activa',
                ]);
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
