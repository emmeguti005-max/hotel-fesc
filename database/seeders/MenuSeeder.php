<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /** Carta del restaurante. Precios en COP antes de IVA. */
    public function run(): void
    {
        $menu = [
            ['desayuno', 'Desayuno santandereano', 'Pisillo, arepa, huevos al gusto, chocolate', 28000],
            ['desayuno', 'Desayuno continental', 'Frutas, pan, mantequilla, mermelada, café o jugo', 22000],
            ['desayuno', 'Calentado con huevo', 'Fríjoles, arroz, chorizo, arepa y huevo', 24000],
            ['desayuno', 'Changua', 'Con almojábana', 16000],
            ['entrada', 'Empanadas cucuteñas (x4)', 'De pipitoria, con ají', 14000],
            ['entrada', 'Patacones con hogao', 'Crocantes, para compartir', 15000],
            ['entrada', 'Crema de ahuyama', 'Con crutones', 13000],
            ['plato_fuerte', 'Mute santandereano', 'Plato típico, con arepa', 38000],
            ['plato_fuerte', 'Carne oreada con yuca', 'Al carbón, con ensalada', 42000],
            ['plato_fuerte', 'Bandeja paisa', 'Completa', 45000],
            ['plato_fuerte', 'Pechuga a la plancha', 'Con papas a la francesa y ensalada', 34000],
            ['plato_fuerte', 'Club sándwich', 'Pollo, tocineta, queso, papas a la francesa', 29000],
            ['plato_fuerte', 'Hamburguesa de la casa', '200 g de res, queso, papas', 32000],
            ['plato_fuerte', 'Pasta Alfredo con pollo', null, 33000],
            ['bebida', 'Jugo natural en agua', 'Mora, maracuyá, guayaba o mango', 7000],
            ['bebida', 'Jugo natural en leche', null, 8500],
            ['bebida', 'Limonada de coco', null, 11000],
            ['bebida', 'Gaseosa', '400 ml', 5500],
            ['bebida', 'Agua sin gas', '600 ml', 4500],
            ['bebida', 'Café americano', null, 5000],
            ['bebida', 'Cerveza nacional', 'Botella 330 ml', 8000],
            ['postre', 'Cortado de leche', 'Postre típico cucuteño', 9000],
            ['postre', 'Torta de chocolate', 'Con helado de vainilla', 12000],
            ['postre', 'Ensalada de frutas', 'Con queso y helado', 14000],
            ['snack', 'Papas a la francesa', null, 9000],
            ['snack', 'Alitas BBQ (x8)', 'Con papas', 26000],
        ];

        foreach ($menu as [$categoria, $nombre, $descripcion, $precio]) {
            Producto::create(compact('categoria', 'nombre', 'descripcion', 'precio'));
        }

        Producto::create([
            'categoria' => 'plato_fuerte',
            'nombre' => 'Mojarra frita',
            'descripcion' => 'Sólo fines de semana',
            'precio' => 40000,
            'disponible' => false,
        ]);
    }
}
