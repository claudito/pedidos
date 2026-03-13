<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderSequence;
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Orders/Index', [
            'orders' => Order::query()
                ->with(['client:id,name', 'orderStatus:id,name,code', 'items.product:id,description'])
                ->latest('order_date')
                ->get()
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'code' => $order->code,
                    'client_id' => $order->client_id,
                    'client_name' => $order->client?->name,
                    'order_date' => optional($order->order_date)->format('Y-m-d'),
                    'warehouse_preparation_date' => optional($order->warehouse_preparation_date)->format('Y-m-d'),
                    'dispatch_date' => optional($order->dispatch_date)->format('Y-m-d'),
                    'delivery_date' => optional($order->delivery_date)->format('Y-m-d'),
                    'status' => $order->orderStatus?->name ?? $order->status,
                    'order_status_id' => $order->order_status_id,
                    'notes' => $order->notes,
                    'total' => $order->total,
                    'items' => $order->items->map(fn ($item) => [
                        'product_id' => $item->product_id,
                        'product_name' => $item->product?->description,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'subtotal' => $item->subtotal,
                    ])->values(),
                ]),
            'clients' => Client::query()->orderBy('name')->get(['id', 'name']),
            'products' => Product::query()->orderBy('description')->get(['id', 'description', 'price', 'quantity']),
            'next_order_code' => $this->previewNextOrderCode(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateOrder($request);
        $registeredStatus = OrderStatus::query()->where('code', 'registrado')->firstOrFail();

        DB::transaction(function () use ($validated, $registeredStatus) {
            $items = $this->prepareItems($validated['items']);
            $total = collect($items)->sum('subtotal');
            $generatedCode = $this->generateOrderCode();

            $order = Order::create([
                'code' => $generatedCode,
                'client_id' => $validated['client_id'],
                'order_date' => $validated['order_date'],
                'warehouse_preparation_date' => null,
                'dispatch_date' => null,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'status' => $registeredStatus->name,
                'order_status_id' => $registeredStatus->id,
                'notes' => $validated['notes'] ?? null,
                'total' => $total,
            ]);

            $order->items()->createMany($items);
            $this->applyStockDelta($items, -1);

            $order->trackings()->create([
                'order_status_id' => $registeredStatus->id,
                'status' => $registeredStatus->name,
                'description' => 'Pedido registrado',
                'tracked_at' => now(),
            ]);
        });

        return back()->with('success', 'Pedido creado correctamente.');
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $this->validateOrder($request, $order);

        DB::transaction(function () use ($validated, $order) {
            $originalItems = $order->items()->get(['product_id', 'quantity'])->toArray();
            $this->applyStockDelta($originalItems, 1);

            $items = $this->prepareItems($validated['items']);
            $total = collect($items)->sum('subtotal');

            $order->update([
                'client_id' => $validated['client_id'],
                'order_date' => $validated['order_date'],
                'delivery_date' => $validated['delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'total' => $total,
            ]);

            $order->items()->delete();
            $order->items()->createMany($items);
            $this->applyStockDelta($items, -1);
        });

        return back()->with('success', 'Pedido actualizado correctamente.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order) {
            $items = $order->items()->get(['product_id', 'quantity'])->toArray();
            $this->applyStockDelta($items, 1);
            $order->delete();
        });

        return back()->with('success', 'Pedido eliminado correctamente.');
    }

    private function validateOrder(Request $request, ?Order $order = null): array
    {
        return $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'order_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);
    }

    private function prepareItems(array $items): array
    {
        return collect($items)
            ->map(function (array $item) {
                $product = Product::query()->findOrFail($item['product_id']);

                if ($product->quantity < $item['quantity']) {
                    abort(422, "Stock insuficiente para el producto {$product->description}.");
                }

                $subtotal = $product->price * $item['quantity'];

                return [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                    'subtotal' => $subtotal,
                ];
            })
            ->values()
            ->all();
    }

    private function applyStockDelta(array $items, int $direction): void
    {
        foreach ($items as $item) {
            $product = Product::query()->findOrFail($item['product_id']);
            $newQuantity = $product->quantity + ($item['quantity'] * $direction);

            if ($newQuantity < 0) {
                abort(422, "Stock insuficiente para el producto {$product->description}.");
            }

            $product->update([
                'quantity' => $newQuantity,
            ]);
        }
    }

    private function previewNextOrderCode(): string
    {
        $sequence = OrderSequence::query()->firstOrCreate(
            ['document_type' => 'orders'],
            ['prefix' => 'PED-', 'current_number' => 0, 'padding' => 3],
        );

        return $sequence->prefix.str_pad((string) ($sequence->current_number + 1), $sequence->padding, '0', STR_PAD_LEFT);
    }

    private function generateOrderCode(): string
    {
        $sequence = OrderSequence::query()
            ->where('document_type', 'orders')
            ->lockForUpdate()
            ->first();

        if (! $sequence) {
            $sequence = OrderSequence::query()->create([
                'document_type' => 'orders',
                'prefix' => 'PED-',
                'current_number' => 0,
                'padding' => 3,
            ]);
            $sequence->refresh();
        }

        $nextNumber = $sequence->current_number + 1;
        $sequence->update([
            'current_number' => $nextNumber,
        ]);

        return $sequence->prefix.str_pad((string) $nextNumber, $sequence->padding, '0', STR_PAD_LEFT);
    }
}
