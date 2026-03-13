<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderSequence;
use Illuminate\Database\Seeder;

class OrderSequenceSeeder extends Seeder
{
    public function run(): void
    {
        $sequence = OrderSequence::query()->updateOrCreate(
            ['document_type' => 'orders'],
            [
                'prefix' => 'PED-',
                'padding' => 3,
            ],
        );

        $maxExisting = Order::query()
            ->where('code', 'like', $sequence->prefix.'%')
            ->get(['code'])
            ->map(function (Order $order) use ($sequence) {
                return (int) str_replace($sequence->prefix, '', $order->code);
            })
            ->max() ?? 0;

        if ($maxExisting > $sequence->current_number) {
            $sequence->update([
                'current_number' => $maxExisting,
            ]);
        }
    }
}
