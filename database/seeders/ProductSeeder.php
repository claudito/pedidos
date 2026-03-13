<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['code' => 'PROD-001', 'description' => 'Router MikroTik hEX RB750Gr3', 'quantity' => 35, 'price' => 265.00],
            ['code' => 'PROD-002', 'description' => 'Access Point Ubiquiti UniFi U6 Lite', 'quantity' => 28, 'price' => 589.00],
            ['code' => 'PROD-003', 'description' => 'Cable UTP Cat6 caja x305m', 'quantity' => 18, 'price' => 420.00],
            ['code' => 'PROD-004', 'description' => 'Conector RJ45 Cat6 bolsa x100', 'quantity' => 60, 'price' => 48.50],
            ['code' => 'PROD-005', 'description' => 'Switch Gigabit TP-Link 8 puertos', 'quantity' => 22, 'price' => 179.90],
            ['code' => 'PROD-006', 'description' => 'Bobina de fibra optica drop 1 hilo 500m', 'quantity' => 12, 'price' => 690.00],
        ];

        foreach ($products as $product) {
            Product::query()->updateOrCreate(['code' => $product['code']], $product);
        }
    }
}
