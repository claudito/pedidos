<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'sets_preparation_date',
        'sets_dispatch_date',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sets_preparation_date' => 'boolean',
            'sets_dispatch_date' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function trackings(): HasMany
    {
        return $this->hasMany(Tracking::class);
    }
}
