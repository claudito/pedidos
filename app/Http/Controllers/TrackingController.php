<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Tracking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class TrackingController extends Controller
{
    public function index(): Response
    {
        $orders = Order::query()
            ->with([
                'client:id,name',
                'trackings' => fn ($query) => $query
                    ->with('orderStatus:id,name,code')
                    ->orderByDesc('tracked_at')
                    ->orderByDesc('id'),
            ])
            ->orderByDesc('order_date')
            ->get();

        return Inertia::render('Trackings/Index', [
            'tracking_orders' => $orders->map(function (Order $order) {
                $latestTracking = $order->trackings->first();
                $latestStatus = $latestTracking?->orderStatus?->name ?? $latestTracking?->status ?? 'Sin seguimiento';

                return [
                    'order_id' => $order->id,
                    'order_code' => $order->code,
                    'client_name' => $order->client?->name,
                    'current_status' => $latestStatus,
                    'current_description' => $latestTracking?->description,
                    'last_tracked_at' => optional($latestTracking?->tracked_at)->format('d/m/Y H:i'),
                    'tracking_count' => $order->trackings->count(),
                    'history' => $order->trackings->map(fn (Tracking $tracking) => [
                        'id' => $tracking->id,
                        'order_id' => $tracking->order_id,
                        'order_code' => $order->code,
                        'order_status_id' => $tracking->order_status_id,
                        'status' => $tracking->orderStatus?->name ?? $tracking->status,
                        'description' => $tracking->description,
                        'tracked_at' => optional($tracking->tracked_at)->format('Y-m-d\TH:i'),
                        'tracked_at_label' => optional($tracking->tracked_at)->format('d/m/Y H:i'),
                    ])->values(),
                ];
            })->values(),
            'orders' => Order::query()->orderBy('code')->get(['id', 'code']),
            'statuses' => OrderStatus::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get(['id', 'code', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'order_status_id' => ['required', 'exists:order_statuses,id'],
            'description' => ['nullable', 'string'],
            'tracked_at' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($validated) {
            $status = OrderStatus::query()->findOrFail($validated['order_status_id']);

            Tracking::create([
                'order_id' => $validated['order_id'],
                'order_status_id' => $status->id,
                'status' => $status->name,
                'description' => $validated['description'] ?? null,
                'tracked_at' => $validated['tracked_at'],
            ]);

            $this->syncOrderFromTrackings((int) $validated['order_id']);
        });

        return back()->with('success', 'Seguimiento creado correctamente.');
    }

    public function update(Request $request, Tracking $tracking): RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'order_status_id' => ['required', 'exists:order_statuses,id'],
            'description' => ['nullable', 'string'],
            'tracked_at' => ['required', 'date'],
        ]);

        DB::transaction(function () use ($validated, $tracking) {
            $status = OrderStatus::query()->findOrFail($validated['order_status_id']);
            $originalOrderId = $tracking->order_id;

            $tracking->update([
                'order_id' => $validated['order_id'],
                'order_status_id' => $status->id,
                'status' => $status->name,
                'description' => $validated['description'] ?? null,
                'tracked_at' => $validated['tracked_at'],
            ]);

            $this->syncOrderFromTrackings((int) $validated['order_id']);

            if ($originalOrderId !== (int) $validated['order_id']) {
                $this->syncOrderFromTrackings($originalOrderId);
            }
        });

        return back()->with('success', 'Seguimiento actualizado correctamente.');
    }

    public function destroy(Tracking $tracking): RedirectResponse
    {
        DB::transaction(function () use ($tracking) {
            $orderId = $tracking->order_id;
            $tracking->delete();
            $this->syncOrderFromTrackings($orderId);
        });

        return back()->with('success', 'Seguimiento eliminado correctamente.');
    }

    private function syncOrderFromTrackings(int $orderId): void
    {
        $order = Order::query()->with(['trackings.orderStatus'])->findOrFail($orderId);
        $trackings = $order->trackings->sortBy('tracked_at')->values();
        $latestTracking = $trackings->last();

        $preparationTracking = $trackings->first(function (Tracking $tracking) {
            return $tracking->orderStatus?->sets_preparation_date === true;
        });

        $dispatchTracking = $trackings->first(function (Tracking $tracking) {
            return $tracking->orderStatus?->sets_dispatch_date === true;
        });

        $order->update([
            'status' => $latestTracking?->orderStatus?->name ?? 'Registrado',
            'order_status_id' => $latestTracking?->order_status_id,
            'warehouse_preparation_date' => $preparationTracking?->tracked_at?->toDateString(),
            'dispatch_date' => $dispatchTracking?->tracked_at?->toDateString(),
        ]);
    }
}
