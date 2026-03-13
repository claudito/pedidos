<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $clientA = Client::query()->where('code', 'CLI-001')->firstOrFail();
        $clientB = Client::query()->where('code', 'CLI-002')->firstOrFail();
        $registeredStatus = OrderStatus::query()->where('code', 'registrado')->firstOrFail();
        $deliveredStatus = OrderStatus::query()->where('code', 'entregado')->firstOrFail();
        $preparationStatus = OrderStatus::query()->where('code', 'en_preparacion')->firstOrFail();
        $productA = Product::query()->where('code', 'PROD-001')->firstOrFail();
        $productB = Product::query()->where('code', 'PROD-002')->firstOrFail();
        $productC = Product::query()->where('code', 'PROD-003')->firstOrFail();

        DB::transaction(function () use ($clientA, $clientB, $deliveredStatus, $preparationStatus, $productA, $productB, $productC) {
            if (! Order::query()->where('code', 'PED-001')->exists()) {
                $order = Order::query()->create([
                    'code' => 'PED-001',
                    'client_id' => $clientA->id,
                    'order_date' => now()->subDays(3)->toDateString(),
                    'warehouse_preparation_date' => now()->subDays(2)->toDateString(),
                    'dispatch_date' => now()->subDay()->toDateString(),
                    'delivery_date' => now()->toDateString(),
                    'status' => $deliveredStatus->name,
                    'order_status_id' => $deliveredStatus->id,
                    'notes' => 'Implementacion de cobertura WiFi para 2 pisos del hotel.',
                    'total' => 0,
                ]);

                $items = [
                    ['product' => $productA, 'quantity' => 2],
                    ['product' => $productB, 'quantity' => 6],
                ];

                $total = 0;
                foreach ($items as $item) {
                    $subtotal = $item['product']->price * $item['quantity'];
                    $total += $subtotal;
                    $order->items()->create([
                        'product_id' => $item['product']->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['product']->price,
                        'subtotal' => $subtotal,
                    ]);
                    $item['product']->decrement('quantity', $item['quantity']);
                }

                $order->update(['total' => $total]);
            }

            if (! Order::query()->where('code', 'PED-002')->exists()) {
                $order = Order::query()->create([
                    'code' => 'PED-002',
                    'client_id' => $clientB->id,
                    'order_date' => now()->subDays(1)->toDateString(),
                    'warehouse_preparation_date' => now()->toDateString(),
                    'dispatch_date' => null,
                    'delivery_date' => null,
                    'status' => $preparationStatus->name,
                    'order_status_id' => $preparationStatus->id,
                    'notes' => 'Ampliacion de cableado estructurado para nuevas aulas.',
                    'total' => 0,
                ]);

                $subtotal = $productC->price * 4;
                $order->items()->create([
                    'product_id' => $productC->id,
                    'quantity' => 4,
                    'unit_price' => $productC->price,
                    'subtotal' => $subtotal,
                ]);
                $productC->decrement('quantity', 4);
                $order->update(['total' => $subtotal]);
            }

            Order::query()->where('code', 'PED-001')->update([
                'status' => $deliveredStatus->name,
                'order_status_id' => $deliveredStatus->id,
            ]);

            Order::query()->where('code', 'PED-002')->update([
                'status' => $preparationStatus->name,
                'order_status_id' => $preparationStatus->id,
            ]);
        });
    }
}
