<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class PersonalSeeder extends Seeder
{
    /**
     * Personal de prueba. Todos entran con la contraseña "Usuario1".
     * El primero de cada área tiene un correo corto para probar cada rol.
     */
    public function run(): void
    {
        $personal = [
            ['Fabián Rodríguez', 'admin@hotel.local', 'admin', 'Gerente general', '300 100 0001'],

            ['Laura Gómez', 'recepcion@hotel.local', 'recepcion', 'Recepcionista turno día', '300 100 0002'],
            ['Andrés Rojas', 'andres.rojas@hotel.local', 'recepcion', 'Recepcionista turno noche', '300 100 0003'],

            ['Rosa Pérez', 'aseo@hotel.local', 'aseo', 'Camarera de pisos', '300 100 0004'],
            ['Diana Vargas', 'diana.vargas@hotel.local', 'aseo', 'Camarera de pisos', '300 100 0005'],
            ['Luz Mery Castro', 'luzmery.castro@hotel.local', 'aseo', 'Camarera de pisos', '300 100 0006'],
            ['Marta Silva', 'marta.silva@hotel.local', 'aseo', 'Ama de llaves', '300 100 0007'],

            ['Jorge Díaz', 'restaurante@hotel.local', 'restaurante', 'Chef ejecutivo', '300 100 0008'],
            ['Camila Torres', 'camila.torres@hotel.local', 'restaurante', 'Auxiliar de cocina', '300 100 0009'],
            ['Pedro Castro', 'pedro.castro@hotel.local', 'restaurante', 'Mesero · room service', '300 100 0010'],
            ['Sofía Mora', 'sofia.mora@hotel.local', 'restaurante', 'Mesera', '300 100 0011'],

            ['Carlos Herrera', 'mantenimiento@hotel.local', 'mantenimiento', 'Jefe de mantenimiento', '300 100 0012'],
            ['Luis Ortega', 'luis.ortega@hotel.local', 'mantenimiento', 'Técnico de mantenimiento', '300 100 0013'],
        ];

        foreach ($personal as [$nombre, $correo, $rol, $cargo, $telefono]) {
            User::create([
                'nombre' => $nombre,
                'correo' => $correo,
                'password' => 'Usuario1',
                'rol' => $rol,
                'cargo' => $cargo,
                'telefono' => $telefono,
            ]);
        }

        // Un exempleado, para ver cómo queda el historial de alguien inactivo.
        User::create([
            'nombre' => 'Esteban Niño',
            'correo' => 'esteban.nino@hotel.local',
            'password' => 'Usuario1',
            'rol' => 'restaurante',
            'cargo' => 'Mesero (retirado)',
            'activo' => false,
        ]);
    }
}
