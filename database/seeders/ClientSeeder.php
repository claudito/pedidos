<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'code' => 'CLI-001',
                'name' => 'Hotel Costa Norte SAC',
                'document_number' => '20100011122',
                'email' => 'compras@hotelcostanorte.com.pe',
                'phone' => '999111222',
                'address' => 'Av. La Marina 123, Trujillo',
            ],
            [
                'code' => 'CLI-002',
                'name' => 'Colegio San Gabriel EIRL',
                'document_number' => '20555544433',
                'email' => 'operaciones@colegiosangabriel.edu.pe',
                'phone' => '988444333',
                'address' => 'Jr. Los Educadores 456, Chiclayo',
            ],
            [
                'code' => 'CLI-003',
                'name' => 'Condominio Vista Fibra SA',
                'document_number' => '20444888776',
                'email' => 'administracion@vistafibra.pe',
                'phone' => '977000555',
                'address' => 'Av. Los Ingenieros 890, Piura',
            ],
        ];

        foreach ($clients as $client) {
            Client::query()->updateOrCreate(['code' => $client['code']], $client);
        }
    }
}
