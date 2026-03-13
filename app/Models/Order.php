<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'client_id',
        'order_date',
        'warehouse_preparation_date',
        'dispatch_date',
        'delivery_date',
        'status',
        'order_status_id',
        'total',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'warehouse_preparation_date' => 'date',
            'dispatch_date' => 'date',
            'delivery_date' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(Tracking::class);
    }
}
