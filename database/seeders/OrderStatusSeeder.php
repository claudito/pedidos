<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Tracking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            [
                'code' => 'registrado',
                'name' => 'Registrado',
                'description' => 'Pedido registrado en el sistema.',
                'sets_preparation_date' => false,
                'sets_dispatch_date' => false,
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'code' => 'en_preparacion',
                'name' => 'En preparacion',
                'description' => 'Pedido en preparacion de almacen.',
                'sets_preparation_date' => true,
                'sets_dispatch_date' => false,
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'code' => 'despachado',
                'name' => 'Despachado',
                'description' => 'Pedido despachado al cliente.',
                'sets_preparation_date' => false,
                'sets_dispatch_date' => true,
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'code' => 'entregado',
                'name' => 'Entregado',
                'description' => 'Pedido entregado e instalado.',
                'sets_preparation_date' => false,
                'sets_dispatch_date' => false,
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'code' => 'cancelado',
                'name' => 'Cancelado',
                'description' => 'Pedido cancelado.',
                'sets_preparation_date' => false,
                'sets_dispatch_date' => false,
                'sort_order' => 5,
                'is_active' => true,
            ],
        ];

        foreach ($statuses as $status) {
            OrderStatus::query()->updateOrCreate(['code' => $status['code']], $status);
        }

        $this->syncExistingTrackings();
        $this->syncExistingOrders();
    }

    private function syncExistingTrackings(): void
    {
        Tracking::query()
            ->where(function ($query) {
                $query->whereNull('order_status_id')
                    ->orWhereDoesntHave('orderStatus');
            })
            ->get()
            ->each(function (Tracking $tracking) {
                $status = $this->resolveStatus($tracking->status);

                if (! $status) {
                    return;
                }

                $tracking->update([
                    'order_status_id' => $status->id,
                    'status' => $status->name,
                ]);
            });
    }

    private function syncExistingOrders(): void
    {
        Order::query()
            ->where(function ($query) {
                $query->whereNull('order_status_id')
                    ->orWhereDoesntHave('orderStatus');
            })
            ->get()
            ->each(function (Order $order) {
                $status = $this->resolveStatus($order->status);

                if (! $status) {
                    return;
                }

                $order->update([
                    'order_status_id' => $status->id,
                    'status' => $status->name,
                ]);
            });
    }

    private function resolveStatus(?string $value): ?OrderStatus
    {
        $normalized = Str::of((string) $value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->value();

        if ($normalized === '') {
            return OrderStatus::query()->where('code', 'registrado')->first();
        }

        return OrderStatus::query()->where('code', $normalized)->first()
            ?? OrderStatus::query()->where('name', $value)->first()
            ?? OrderStatus::query()->where('code', 'registrado')->first();
    }
}
