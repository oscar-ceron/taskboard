<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $ana = User::create([ 
        'name' => 'Ana López', 
        'email' => 'ana@taskboard.test', 
        'password' => 'password123',   // el cast 'hashed' la cifra 
    ]); 
    $carlos = User::create([ 
        'name' => 'Carlos Pérez', 
        'email' => 'carlos@taskboard.test', 
        'password' => 'password123', 
    ]); 

    $ana->comercios()->create(['nombre_comercio' => 'Café Amanecer', 'rubro' => 'Cafetería']); 
    $carlos->comercios()->create(['nombre_comercio' => 'Ferretería El Tornillo', 'rubro' => 
    'Ferretería']); 

        /*$this->call([
            ComercioSeeder::class,
        ]);*/
    }

    
}
