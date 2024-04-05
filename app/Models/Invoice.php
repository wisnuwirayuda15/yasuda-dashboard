<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Invoice extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'order_id',
        'main_costs',
        'bills',
        'shirts',
        'seats_charge',
        'card_details',
        'special_notes',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'main_costs' => 'array',
        'bills' => 'array',
        'shirts' => 'array',
        'seats_charge' => 'array',
        'card_details' => 'array',
    ];

    public function profitLoss(): HasOne
    {
        return $this->hasOne(ProfitLoss::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
