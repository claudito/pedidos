<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Tracking;
use Illuminate\Database\Seeder;

class TrackingSeeder extends Seeder
{
    public function run(): void
    {
        $trackings = [
            ['order_code' => 'PED-001', 'status_code' => 'registrado', 'description' => 'Proyecto WiFi registrado para hotel.', 'tracked_at' => now()->subDays(3)],
            ['order_code' => 'PED-001', 'status_code' => 'despachado', 'description' => 'Equipos de red enviados al sitio de instalacion.', 'tracked_at' => now()->subDay()],
            ['order_code' => 'PED-001', 'status_code' => 'entregado', 'description' => 'Router y access points entregados e instalados.', 'tracked_at' => now()],
            ['order_code' => 'PED-002', 'status_code' => 'en_preparacion', 'description' => 'Cableado Cat6 en alistamiento para despacho.', 'tracked_at' => now()],
        ];

        foreach ($trackings as $tracking) {
            $order = Order::query()->where('code', $tracking['order_code'])->firstOrFail();
            $status = OrderStatus::query()->where('code', $tracking['status_code'])->firstOrFail();

            Tracking::query()->updateOrCreate(
                [
                    'order_id' => $order->id,
                    'order_status_id' => $status->id,
                    'tracked_at' => $tracking['tracked_at'],
                ],
                [
                    'status' => $status->name,
                    'description' => $tracking['description'],
                ]
            );
        }
    }
}
